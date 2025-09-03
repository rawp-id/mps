<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PlanGeneratorController extends Controller
{
    public function generate(Request $req)
    {
        // 0) Parameter
        $planId = $req->integer('plan_id') ?? 1;
        $startDate = $req->date('start_date') ? Carbon::parse($req->date('start_date')) : Carbon::today();
        $endDate = $req->date('end_date') ? Carbon::parse($req->date('end_date')) : Carbon::today()->addDays(14);
        $saveResults = filter_var($req->input('save', true), FILTER_VALIDATE_BOOLEAN); // default true
        $timeLimit = (int) ($req->input('time_limit_sec', 15));

        // 1) Horizon (ambil dari calender_days yang berada dalam rentang)
        $days = DB::table('calender_days')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('is_workday', false)
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->toArray();

            // dd($days);

        $days_start_end = CarbonPeriod::create($startDate, $endDate)->toArray();

        $days = array_values(array_diff($days_start_end, $days));

        $days = array_map(function ($d) {
            return Carbon::parse($d)->toDateString();
        }, $days);

        // dd($days);

        if (empty($days)) {
            return response()->json(['error' => 'Horizon kosong. Isi tabel calender_days untuk rentang dimaksud.'], 422);
        }

        // 2) Machines & Shifts
        $machines = DB::table('machines')->select('id', 'name')->get();
        $shifts = DB::table('shifts')->where('is_active', 1)->get()
            ->groupBy('machine_id'); // shift statis per hari kerja

        // 3) Base capacity per machine-day: workday? sum(shift minutes)
        $isWorkday = DB::table('calender_days')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('is_workday', 'date'); // ['Y-m-d' => 0/1]

        $baseCap = []; // [machine_id][date] => minutes
        foreach ($machines as $m) {
            foreach ($days as $d) {
                $mins = 0;
                if (($isWorkday[$d] ?? 1) == 1) {
                    foreach ($shifts->get($m->id, []) as $s) {
                        $mins += $this->diffMinutes($s->start_time, $s->end_time);
                    }
                }
                $baseCap[$m->id][$d] = max(0, (int) $mins);
            }
        }

        // dd($baseCap);

        // 4) Downtime (slice by day)
        $downRows = DB::table('downtimes')
            ->where('end_datetime', '>=', $startDate->toDateString() . ' 00:00:00')
            ->where('start_datetime', '<=', $endDate->toDateString() . ' 23:59:59')
            ->get();

        $downCap = []; // [machine_id][date] => minutes
        foreach ($downRows as $dwn) {
            foreach ($this->sliceByDayMinutes($dwn->start_datetime, $dwn->end_datetime, $startDate, $endDate) as $day => $mins) {
                $downCap[$dwn->machine_id][$day] = ($downCap[$dwn->machine_id][$day] ?? 0) + $mins;
            }
        }

        // dd($downCap);

        // 5) Overtime (per date)
        $otRows = DB::table('overtimes')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $otCap = []; // [machine_id][date] => minutes
        foreach ($otRows as $ot) {
            $otCap[$ot->machine_id][$ot->date] = ($otCap[$ot->machine_id][$ot->date] ?? 0) + $this->diffMinutes($ot->start_time, $ot->end_time);
        }

        // 6) Build capacities array (base only: shift - downtime + overtime)
        $capacities = [];
        foreach ($machines as $m) {
            foreach ($days as $d) {
                $cap = max(0, ($baseCap[$m->id][$d] ?? 0) - ($downCap[$m->id][$d] ?? 0)) + ($otCap[$m->id][$d] ?? 0);
                $capacities[] = [
                    'machine_id' => (int) $m->id,
                    'date' => $d,
                    'minutes' => (int) $cap,
                ];
            }
        }

        // 7) Locks (capacity & pins)
        // 7a) SCHEDULES REAL terpakai => capacity locks
        $schedRows = DB::table('schedules')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereNotNull('start_time')
                    ->whereNotNull('end_time')
                    ->where('end_time', '>=', $startDate->toDateString() . ' 00:00:00')
                    ->where('start_time', '<=', $endDate->toDateString() . ' 23:59:59');
            })
            ->orWhere(function ($q) use ($startDate, $endDate) {
                // fallback menit jika hanya plan_duration/duration + start_time tersedia
                $q->whereNotNull('start_time')
                    ->whereNull('end_time')
                    ->whereBetween(DB::raw("date(start_time)"), [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->get();

        $lockCapacityMap = []; // [machine_id][date] => minutes
        foreach ($schedRows as $s) {
            if ($s->start_time && $s->end_time) {
                foreach ($this->sliceByDayMinutes($s->start_time, $s->end_time, $startDate, $endDate) as $day => $mins) {
                    $lockCapacityMap[$s->machine_id][$day] = ($lockCapacityMap[$s->machine_id][$day] ?? 0) + $mins;
                }
            } else {
                $mins = (int) ($s->plan_duration ?? $s->duration ?? 0);
                if ($mins > 0) {
                    $day = substr($s->start_time, 0, 10);
                    if ($day >= $startDate->toDateString() && $day <= $endDate->toDateString()) {
                        $lockCapacityMap[$s->machine_id][$day] = ($lockCapacityMap[$s->machine_id][$day] ?? 0) + $mins;
                    }
                }
            }
        }

        // dd($lockCapacityMap);

        // 7b) SIMULATE locked => capacity locks + pins
        $simLockedRows = DB::table('simulate_schedules')
            ->where('is_locked', 1)
            ->whereNotNull('start_time')
            ->whereNotNull('machine_id')
            ->whereBetween(DB::raw("date(start_time)"), [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        // dd($simLockedRows);

        $pins = [];
        foreach ($simLockedRows as $s) {
            // capacity lock
            if ($s->start_time && $s->end_time) {
                foreach ($this->sliceByDayMinutes($s->start_time, $s->end_time, $startDate, $endDate) as $day => $mins) {
                    $lockCapacityMap[$s->machine_id][$day] = ($lockCapacityMap[$s->machine_id][$day] ?? 0) + $mins;
                }
            } else {
                $mins = (int) ($s->plan_duration ?? $s->duration ?? 0);
                $day = substr($s->start_time, 0, 10);
                if ($mins > 0 && $day >= $startDate->toDateString() && $day <= $endDate->toDateString()) {
                    $lockCapacityMap[$s->machine_id][$day] = ($lockCapacityMap[$s->machine_id][$day] ?? 0) + $mins;
                }
            }

            // pin (ikat op pada hari tersebut) jika operation_id ada
            if ($s->operation_id) {
                $pins[] = [
                    'co_product_id' => (int) ($s->co_product_id ?? 0),
                    'operation_id' => (int) $s->operation_id,
                    'machine_id' => (int) $s->machine_id,
                    'date' => substr($s->start_time, 0, 10),
                    'minutes' => (int) ($s->plan_duration ?? $s->duration ?? 0),
                ];
            }
        }

        // Flatten lock capacity
        $lockCapacity = [];
        foreach ($lockCapacityMap as $mid => $byDay) {
            foreach ($byDay as $d => $mins) {
                $lockCapacity[] = [
                    'machine_id' => (int) $mid,
                    'date' => $d,
                    'minutes' => (int) $mins,
                ];
            }
        }

        // 8) Jobs (co_products) & exclude yang sudah ada di schedules
        $coList = DB::table('co_products as cp')
            ->join('products as p', 'p.id', '=', 'cp.product_id')
            ->leftJoin('cos as c', 'c.id', '=', 'cp.co_id')
            ->select('cp.id as co_product_id', 'cp.product_id', 'p.code', 'cp.shipment_date', 'p.shipping_date as product_ship')
            ->orderBy('cp.shipment_date', 'asc')
            ->get();

        // dd($coList);

        $jobs = [];
        foreach ($coList as $co) {
            // dd($co);
            $ops = DB::select("
                SELECT pp.id AS pp_id, o.id AS operation_id, o.machine_id, o.duration AS minutes, o.is_setting AS is_setup
                FROM process_products pp
                JOIN operations o ON o.id = pp.operation_id
                WHERE pp.product_id = ?
                  AND NOT EXISTS (
                    SELECT 1 FROM schedules s
                    WHERE s.co_product_id = ? AND s.operation_id = o.id
                  )
                ORDER BY pp.id ASC
            ", [$co->product_id, $co->co_product_id]);

            // dd($ops);

            if (empty($ops))
                continue;

            // tentukan due_date: pakai co_products.shipment_date, fallback products.shipping_date
            $due = $co->shipment_date ?? $co->product_ship;
            if (!$due)
                continue;
            $dueDate = substr($due, 0, 10);

            // hanya ambil job jika due ada di horizon (atau minimal <= end)
            if ($dueDate > $endDate->toDateString()) {
                // tetap boleh, solver akan menganggap due di luar horizon (ditrim jadi last day)
            }

            $jobs[] = [
                'co_product_id' => (int) $co->co_product_id,
                'product_id' => (int) $co->product_id,
                'code' => $co->code,
                'due_date' => $dueDate,
                'ops' => array_map(function ($o) {
                    return [
                        'pp_id' => (int) $o->pp_id,
                        'operation_id' => (int) $o->operation_id,
                        'machine' => (int) $o->machine_id,
                        'minutes' => max(1, (int) $o->minutes),
                        'is_setup' => (int) $o->is_setup,
                    ];
                }, $ops),
            ];
            // dd($jobs);
        }
        // dd($jobs);

        // 9) Payload untuk Python
        $payload = [
            'horizon' => array_values($days),
            'capacities' => $capacities,
            'jobs' => $jobs,
            'locks' => [
                'capacity' => $lockCapacity,
                'pins' => $pins,
            ],
            'params' => [
                'time_limit_sec' => $timeLimit,
                'late_penalty' => 10,
                'setup_penalty' => 1,
                'split_chunk_max' => 240,
            ],
        ];

        // dd($payload);

        // 10) Jalankan Python
        // Simpan payload ke file sementara
        $payloadFile = storage_path('app/payload.json');
        file_put_contents($payloadFile, json_encode($payload, JSON_PRETTY_PRINT));
        // dd($payloadFile);

        // Jalankan python dengan argumen file payload
        $python = env('PY_BIN', 'D:/Tech/work/erp/mps-rest/.venv/Scripts/python.exe');
        $script = env('MPS_SOLVER', 'D:/Tech/work/erp/mps-rest/mps_fix_shift.py');

        $proc = new Process([$python, $script, $payloadFile]);
        $proc->setTimeout(180);
        $proc->run();

        // dd($proc->getOutput());

        if (!$proc->isSuccessful()) {
            throw new ProcessFailedException($proc);
        }

        $result = json_decode($proc->getOutput(), true);
        if (!is_array($result)) {
            return response()->json(['error' => 'Solver tidak mengembalikan JSON valid', 'raw' => $proc->getOutput()], 500);
        }

        // 11) Simpan ke simulate_schedules (idempotent)
        if ($saveResults && in_array($result['status'] ?? 'UNKNOWN', ['OPTIMAL', 'FEASIBLE'])) {
            DB::transaction(function () use ($planId, $result, $shifts) {
                // Hapus hasil lama yang tidak locked
                DB::table('simulate_schedules')
                    ->where('plan_id', $planId)
                    ->where('is_locked', 0)
                    ->delete();

                // Insert baris baru
                $now = Carbon::now();
                $rows = [];
                foreach ($result['assignments'] as $a) {
                    $machineId = (int) $a['machine_id'];
                    $date = $a['date'];
                    $minutes = (int) $a['minutes'];

                    // Tentukan jam mulai default = earliest shift machine (kalau ada) else 08:00
                    $shiftList = $shifts->get($machineId, collect());
                    $startTime = '08:00:00';
                    if ($shiftList && $shiftList->count() > 0) {
                        $earliest = $shiftList->sortBy('start_time')->first();
                        $startTime = $earliest->start_time;
                    }
                    $startDT = Carbon::parse($date . ' ' . $startTime);
                    $endDT = (clone $startDT)->addMinutes($minutes);

                    $rows[] = [
                        'plan_id' => $planId,
                        'co_product_id' => (int) ($a['co_product_id'] ?? null),
                        'process_id' => null, // opsional: isi via join operations.process_id jika perlu
                        'machine_id' => $machineId,
                        'operation_id' => (int) ($a['operation_id'] ?? null),
                        'previous_schedule_id' => null,
                        'process_dependency_id' => null,
                        'is_start_process' => ($a['op_index'] ?? 0) === 0 ? 1 : 0,
                        'is_final_process' => 0,
                        'quantity' => 0,
                        'plan_speed' => 0,
                        'conversion_value' => null,
                        'plan_duration' => $minutes,
                        'duration' => $minutes,
                        'start_time' => $startDT->toDateTimeString(),
                        'end_time' => $endDT->toDateTimeString(),
                        'shift_id' => null,
                        'is_overtime' => 0,
                        'adjusted_start' => null,
                        'adjusted_end' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($rows)) {
                    // Bulk insert batched
                    foreach (array_chunk($rows, 500) as $chunk) {
                        DB::table('simulate_schedules')->insert($chunk);
                    }
                }
            });
        }

        // 12) Balikkan hasil + payload (opsional untuk debug)
        return response()->json([
            'status' => $result['status'] ?? 'UNKNOWN',
            'kpi' => $result['job_kpi'] ?? [],
            'machine_load' => $result['machine_load'] ?? [],
            'assignments' => $result['assignments'] ?? [],
            'plan_id' => $planId,
            'payload_sample' => $req->boolean('with_payload', false) ? $payload : null,
        ]);
    }

    /* ===================== Helpers ===================== */

    // beda menit antar "HH:MM:SS", handle overnight sederhana jika end < start (anggap lewat tengah malam)
    private function diffMinutes(string $start, string $end): int
    {
        try {
            $s = Carbon::createFromFormat('H:i:s', $start);
            $e = Carbon::createFromFormat('H:i:s', $end);
        } catch (\Exception $ex) {
            // fallback HH:MM
            $s = Carbon::createFromFormat('H:i', substr($start, 0, 5));
            $e = Carbon::createFromFormat('H:i', substr($end, 0, 5));
        }
        if ($e->lessThanOrEqualTo($s)) {
            $e->addDay(); // overnight shift
        }
        return max(0, $s->diffInMinutes($e));
    }

    // potong interval [startDT, endDT] menjadi menit per tanggal dalam horizon
    private function sliceByDayMinutes(string $startDT, string $endDT, Carbon $hStart, Carbon $hEnd): array
    {
        $out = [];
        $start = Carbon::parse($startDT);
        $end = Carbon::parse($endDT);

        if ($end->lt($hStart) || $start->gt($hEnd->copy()->endOfDay())) {
            return $out; // di luar horizon
        }

        // clamp ke horizon
        if ($start->lt($hStart))
            $start = $hStart->copy()->startOfDay();
        if ($end->gt($hEnd->copy()->endOfDay()))
            $end = $hEnd->copy()->endOfDay();

        $cursor = $start->copy()->startOfDay();
        $last = $end->copy()->startOfDay();

        while ($cursor->lte($last)) {
            $day = $cursor->toDateString();
            $dayStart = $cursor->copy()->startOfDay();
            $dayEnd = $cursor->copy()->endOfDay();

            $from = $start->greaterThan($dayStart) ? $start : $dayStart;
            $till = $end->lessThan($dayEnd) ? $end : $dayEnd;

            if ($till->gte($from)) {
                $mins = max(0, $from->diffInMinutes($till));
                if ($mins > 0) {
                    $out[$day] = ($out[$day] ?? 0) + $mins;
                }
            }
            $cursor->addDay();
        }
        return $out;
    }
}
