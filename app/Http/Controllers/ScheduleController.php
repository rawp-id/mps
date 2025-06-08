<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Machine;
use App\Models\Process;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['product', 'process', 'machine'])->latest()->paginate(10);
        return view('schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('schedules.create', [
            'products' => Product::all(),
            'machines' => Machine::all(),
            'processes' => Process::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'process_id' => 'required|exists:processes,id',
            'machine_id' => 'required|exists:machines,id',
            'previous_schedule_id' => 'nullable|exists:schedules,id',
            'quantity' => 'required|numeric|min:1',
            'plan_speed' => 'required|numeric|min:1',
            'conversion_value' => 'required|numeric|min:0',
            'plan_duration' => 'required|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        Schedule::create($validated);

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function show(Schedule $schedule)
    {
        return view('schedules.show', compact('schedule'));
    }

    public function edit(Schedule $schedule)
    {
        return view('schedules.edit', [
            'schedule' => $schedule,
            'products' => Product::all(),
            'machines' => Machine::all(),
            'processes' => Process::all(),
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'process_id' => 'required|exists:processes,id',
            'machine_id' => 'required|exists:machines,id',
            'previous_schedule_id' => 'nullable|exists:schedules,id',
            'quantity' => 'required|numeric|min:1',
            'plan_speed' => 'required|numeric|min:1',
            'conversion_value' => 'required|numeric|min:0',
            'plan_duration' => 'required|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        $schedule->update($validated);

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
    }

    public function updateScheduleWithDelay(Schedule $schedule, int $delayMinutes)
    {
        $product = $schedule->product;
        $shipmentDeadline = Carbon::parse($product->shipping_date);

        // 1. Mundurkan waktu schedule yang ditarget
        $schedule->start_time = Carbon::parse($schedule->start_time)->addMinutes($delayMinutes);
        $schedule->end_time = Carbon::parse($schedule->end_time)->addMinutes($delayMinutes);

        if ($schedule->end_time->greaterThan($shipmentDeadline)) {
            throw new \Exception("Proses awal melewati batas pengiriman.");
        }

        $schedule->save();

        $lastSchedule = $schedule;

        // 2. Update semua proses setelahnya (berdasarkan previous_schedule_id)
        while (true) {
            $next = Schedule::where('previous_schedule_id', $lastSchedule->id)->first();
            if (!$next)
                break;

            $duration = Carbon::parse($next->end_time)->diffInMinutes(Carbon::parse($next->start_time));
            $startTime = Carbon::parse($lastSchedule->end_time);
            $endTime = $startTime->copy()->addMinutes($duration);

            // 3. Cek bentrok di mesin
            if ($this->hasMachineConflict($next->machine_id, $startTime, $endTime, $next->id)) {
                $startTime = $this->getNextAvailableSmartTime(
                    $next->machine_id,
                    $startTime,
                    $duration,
                    $shipmentDeadline
                );
                $endTime = $startTime->copy()->addMinutes($duration);
            }

            // 4. Pastikan end_time tidak melewati shipment
            if ($endTime->greaterThan($shipmentDeadline)) {
                throw new \Exception("Proses ID {$next->id} tidak bisa dijadwalkan sebelum batas pengiriman.");
            }

            $next->start_time = $startTime;
            $next->end_time = $endTime;
            $next->save();

            $lastSchedule = $next;
        }
    }

    public function applyDelayToSchedule(Request $request, Schedule $schedule)
    {
        $delayMinutes = $request->input('delay_minutes', 0);

        if ($delayMinutes <= 0) {
            return redirect()->back()->withErrors(['delay_minutes' => 'Delay must be greater than 0']);
        }

        try {
            $this->updateScheduleWithDelay($schedule, $delayMinutes);
            return redirect()->route('schedules.index')->with('success', 'Schedule updated with delay successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function applyDelayToScheduleSmart($scheduleId, $delayMinutes)
    {
        DB::transaction(function () use ($scheduleId, $delayMinutes) {

            $schedule = Schedule::findOrFail($scheduleId);

            $product = $schedule->product;
            $shippingDate = $product->shipping_date;

            echo "▶️ Applying delay of {$delayMinutes} minutes to Product {$product->code}, Process {$schedule->process_id}\n";

            // Step 1: Update current schedule with delay
            $schedule->start_time = $schedule->start_time->addMinutes($delayMinutes);
            $schedule->end_time = $schedule->end_time->addMinutes($delayMinutes);
            $schedule->save();

            echo "✅ Updated Process {$schedule->process_id}: {$schedule->start_time} → {$schedule->end_time}\n";

            // Step 2: Update machine availability tracking
            $machineAvailableAt = [];
            $machineAvailableAt[$schedule->machine_id] = $schedule->end_time->copy();

            // Step 3: Get all next schedules for this product in process order (based on previous_schedule_id)
            $nextSchedules = [];
            $currentScheduleId = $schedule->id;

            while (true) {
                $nextSchedule = Schedule::where('previous_schedule_id', $currentScheduleId)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$nextSchedule) {
                    break;
                }

                $nextSchedules[] = $nextSchedule;
                $currentScheduleId = $nextSchedule->id;
            }

            $prevEndTime = $schedule->end_time->copy();

            foreach ($nextSchedules as $nextSchedule) {

                $machineId = $nextSchedule->machine_id;
                $duration = $nextSchedule->plan_duration; // menit

                // Machine ready time (tracking)
                if (!isset($machineAvailableAt[$machineId])) {
                    // Ambil machineAvailableAt dari last schedule di mesin tsb
                    $lastMachineSchedule = Schedule::where('machine_id', $machineId)
                        ->where('product_id', '!=', $product->id)
                        ->orderByDesc('end_time')
                        ->first();

                    $machineAvailableAt[$machineId] = $lastMachineSchedule
                        ? $lastMachineSchedule->end_time->copy()
                        : $nextSchedule->start_time->copy();
                }

                $machineReady = $machineAvailableAt[$machineId]->copy();

                // Ideal start_time
                $newStart = $prevEndTime->copy()->max($machineReady);
                $newEnd = $newStart->copy()->addMinutes($duration);

                // Conflict resolution loop
                do {
                    $conflict = Schedule::where('machine_id', $machineId)
                        ->where('product_id', '!=', $product->id)
                        ->where(function ($query) use ($newStart, $newEnd) {
                            $query->whereBetween('start_time', [$newStart, $newEnd])
                                ->orWhereBetween('end_time', [$newStart, $newEnd])
                                ->orWhere(function ($q) use ($newStart, $newEnd) {
                                    $q->where('start_time', '<', $newStart)
                                        ->where('end_time', '>', $newEnd);
                                });
                        })
                        ->exists();

                    if ($conflict) {
                        echo "⚠️ Conflict detected on Machine {$machineId} → shifting Process {$nextSchedule->process_id}\n";
                        $newStart->addMinutes(1); // Geser 1 menit (bisa di-tune)
                        $newEnd = $newStart->copy()->addMinutes($duration);

                        if ($newEnd->gt($shippingDate)) {
                            throw new \Exception("⛔️ Cannot apply delay, Process {$nextSchedule->process_id} will exceed shipping date after conflict resolution!");
                        }
                    }

                } while ($conflict);

                // Apply new start and end
                $nextSchedule->start_time = $newStart;
                $nextSchedule->end_time = $newEnd;
                $nextSchedule->save();

                echo "✅ Updated Process {$nextSchedule->process_id}: {$newStart} → {$newEnd}\n";

                // Update machine availability
                $machineAvailableAt[$machineId] = $newEnd->copy();

                // Update prevEndTime for next process
                $prevEndTime = $newEnd->copy();
            }

            echo "🎉 Delay applied successfully to Product {$product->code}\n";
        });
    }

    function hasMachineConflict($machineId, Carbon $start, Carbon $end, $excludeId = null): bool
    {
        return Schedule::where('machine_id', $machineId)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                    });
            })
            ->exists();
    }

    function getNextAvailableSmartTime($machineId, Carbon $afterTime, int $durationMinutes, Carbon $shipmentDeadline): Carbon
    {
        $time = $afterTime->copy();

        while (true) {
            $conflict = Schedule::where('machine_id', $machineId)
                ->where(function ($query) use ($time, $durationMinutes) {
                    $end = $time->copy()->addMinutes($durationMinutes);
                    $query->whereBetween('start_time', [$time, $end])
                        ->orWhereBetween('end_time', [$time, $end])
                        ->orWhere(function ($q) use ($time, $end) {
                            $q->where('start_time', '<=', $time)
                                ->where('end_time', '>=', $end);
                        });
                })
                ->orderBy('start_time')
                ->first();

            if (!$conflict) {
                // Pastikan waktu selesai tidak melebihi batas shipment
                if ($time->copy()->addMinutes($durationMinutes)->lessThanOrEqualTo($shipmentDeadline)) {
                    return $time;
                } else {
                    throw new \Exception("Tidak ada slot kosong sebelum pengiriman.");
                }
            }

            // Pindah ke waktu setelah conflict berakhir
            $time = Carbon::parse($conflict->end_time)->copy();
        }
    }


    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}
