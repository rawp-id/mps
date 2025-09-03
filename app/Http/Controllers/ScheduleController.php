<?php

namespace App\Http\Controllers;

use App\Models\CoProduct;
use Carbon\Carbon;
use App\Models\Machine;
use App\Models\Operations;
use App\Models\Process;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ScheduleGraph\ScheduleGraph;

class ScheduleController extends Controller
{
    public function index()
    {
        $coProducts = CoProduct::all();
        return view('schedules.index', compact('coProducts'));
    }

    public function gantt(Request $request)
    {
        $startDate = $request->input('start_date') ?? Carbon::today()->toDateString();
        $endDate = $request->input('end_date') ?? Carbon::today()->toDateString();

        $schedules = Schedule::with(['coProducts', 'process', 'machine', 'operation'])
            ->whereDate('start_time', '>=', $startDate)
            ->whereDate('end_time', '<=', $endDate)
            ->whereHas('coProducts', function ($query) {
                $query->orderByDesc('shipment_date');
            })
            ->get();

        $machines = Machine::all();
        $processes = Process::all();
        $operations = Operations::with(['process', 'machine'])->get();

        // dd($schedules);

        return view('schedules.gantt-chart', compact('schedules', 'startDate', 'endDate', 'machines', 'processes', 'operations'));
    }

    public function ganttByMachine(Request $request, $id)
    {
        $startDate = $request->input('start_date') ?? Carbon::today()->toDateString();
        $endDate = $request->input('end_date') ?? Carbon::today()->toDateString();

        $machines = Machine::all();
        $processes = Process::all();
        $operations = Operations::with(['process', 'machine'])->get();

        if ($id) {
            $schedules = Schedule::with(['coProducts', 'process', 'machine', 'operation', 'operation.process', 'operation.machine'])
                ->whereDate('schedules.start_time', '>=', $startDate)
                ->whereDate('schedules.end_time', '<=', $endDate)
                ->whereHas('operation', function ($query) use ($id) {
                    $query->where('machine_id', $id);
                })
                ->latest()
                ->get();
        } else {
            $schedules = Schedule::with(['coProducts', 'process', 'machine', 'operation'])
                ->whereDate('schedules.start_time', '>=', $startDate)
                ->whereDate('schedules.end_time', '<=', $endDate)
                ->latest()
                ->get();
        }

        // dd($schedules->toArray());
        // $schedules = Schedule::with(['product', 'process', 'machine'])->latest()->get();
        return view('schedules.gantt-chart-machine', compact('schedules', 'startDate', 'endDate', 'id', 'machines', 'processes', 'operations'));
    }

    public function ganttByProcess(Request $request, $id)
    {
        $startDate = $request->input('start_date') ?? Carbon::today()->toDateString();
        $endDate = $request->input('end_date') ?? Carbon::today()->toDateString();
        $machines = Machine::all();
        $processes = Process::all();
        $operations = Operations::with(['process', 'machine'])->get();

        $schedules = Schedule::with(['coProducts', 'process', 'machine', 'operation', 'operation.process', 'operation.machine'])
            ->whereDate('schedules.start_time', '>=', $startDate)
            ->whereDate('schedules.end_time', '<=', $endDate)
            ->whereHas('operation', function ($query) use ($id) {
                $query->where('process_id', $id);
            })
            ->latest()
            ->get();
        // dd($schedules->toArray());
        // $schedules = Schedule::with(['product', 'process', 'machine'])
        //     ->orderBy('process_id')
        //     ->latest()
        //     ->get();

        return view('schedules.gantt-chart-process', compact('schedules', 'startDate', 'endDate', 'id', 'machines', 'processes', 'operations'));
    }

    public function showByProduct($coProductId)
    {
        $schedules = Schedule::with(relations: ['coProducts', 'process', 'machine'])
            ->where('co_product_id', $coProductId)
            ->latest()
            ->get();

        // dd($schedules);

        return view('schedules.product', compact('schedules'));
    }

    public function create()
    {
        return view('schedules.create', [
            'coProducts' => CoProduct::all(),
            'machines' => Machine::all(),
            'processes' => Process::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'co_product_id' => 'required|exists:co_products,id',
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

        return redirect()->route('calender.index')->with('success', 'Schedule created successfully.');
    }

    public function show(Schedule $schedule)
    {
        return view('schedules.show', compact('schedule'));
    }

    public function edit(Schedule $schedule)
    {
        return view('schedules.edit', [
            'schedule' => $schedule,
            'coProducts' => CoProduct::all(),
            'machines' => Machine::all(),
            'processes' => Process::all(),
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'co_product_id' => 'required|exists:co_products,id',
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

        return redirect()->route('calender.index')->with('success', 'Schedule updated successfully.');
    }

    public function delaySchedule(Request $request, $id)
    {
        $schedules = Schedule::all();
        $thisSchedule = Schedule::findOrFail($id); // Bisa dinamis nanti

        $shipmentDeadline = Carbon::parse($thisSchedule->coProducts->shipment_date);

        $endProcessProduct = null;

        // $delayMinutes = (int) $request->input('delay_minutes', 10);
        $inputStartTime = $request->input('start_time');
        $delayMinutes = $inputStartTime ? Carbon::parse($inputStartTime)->diffInMinutes($thisSchedule->start_time) : 10;

        foreach ($schedules as $item) {
            if ($item->co_product_id == $thisSchedule->co_product_id && $item->is_final_process) {
                $endProcessProduct = $item;
                break;
            }
        }

        $endTimeProcessProduct = Carbon::parse($endProcessProduct->end_time);

        if ($endTimeProcessProduct->greaterThan($shipmentDeadline)) {
            dd("Proses ini melebihi batas pengiriman produk.");
            return redirect()->back()->withErrors(['error' => 'Proses ini melebihi batas pengiriman produk.']);
        }

        // Geser semua proses dari thisSchedule sampai endProcessProduct (berdasarkan previous_schedule_id chain)
        $current = $thisSchedule;

        while ($current && $current->id != $endProcessProduct->id) {
            $current->start_time = Carbon::parse($current->start_time)->addMinutes($delayMinutes);
            $current->end_time = Carbon::parse($current->start_time)->addMinutes($current->plan_duration);
            // $current->end_time = Carbon::parse($current->end_time)->addMinutes($delayMinutes);
            $current->save();

            // Cari proses berikutnya dalam chain produk yang sama
            $current = Schedule::where('previous_schedule_id', $current->id)
                ->where('co_product_id', $thisSchedule->co_product_id)
                ->first();
        }

        // Update endProcessProduct juga
        if ($current && $current->id == $endProcessProduct->id) {
            $current->start_time = Carbon::parse($current->start_time)->addMinutes($delayMinutes);
            $current->end_time = Carbon::parse($current->start_time)->addMinutes($current->plan_duration);
            $current->save();
        }

        $schedulesInProcess = Schedule::where('process_id', $thisSchedule->process_id)->get();

        foreach ($schedulesInProcess as $schedule) {
            if ($schedule->id != $thisSchedule->id) {
                // Geser semua proses yang memiliki process_dependency_id ke depan
                $schedule->start_time = Carbon::parse($schedule->start_time)->addMinutes($delayMinutes);
                $schedule->end_time = Carbon::parse($schedule->start_time)->addMinutes($schedule->plan_duration);
                $schedule->save();
            }
        }

        $schedulesUpdateInProcess = Schedule::where('process_id', $thisSchedule->process_id)->get();

        foreach ($schedulesUpdateInProcess as $schedule) {
            $current = $schedule;
            while ($current) {
                $lastTime = Carbon::parse($current->end_time);
                $nextSchedule = Schedule::where('previous_schedule_id', $current->id)
                    ->where('co_product_id', $current->co_product_id)
                    ->first();
                if ($nextSchedule) {
                    $nextSchedule->start_time = $lastTime;
                    $nextSchedule->end_time = $lastTime->copy()->addMinutes($nextSchedule->plan_duration);
                    $nextSchedule->save();

                    // lanjut ke proses berikutnya
                    if ($nextSchedule->is_final_process) {
                        break;
                    }
                    $current = $nextSchedule;
                } else {
                    break;
                }
            }
        }

        // dd('All schedules updated successfully.');
        return redirect()->back()->with('success', 'All schedules updated successfully.');
    }

    public function advanceSchedule(Request $request, $id)
    {
        $schedules = Schedule::all();
        $thisSchedule = Schedule::findOrFail($id); // Bisa dinamis nanti
        $delayMinutes = (int) $request->input('advance_minutes', 10) * -1; // pengurangan waktu

        $shipmentDeadline = Carbon::parse($thisSchedule->coProducts->shipment_date);
        $startProcess = $thisSchedule;

        // Validasi apakah ada proses sebelumnya yang bentrok jika dimajukan
        $prevSchedule = Schedule::where('id', $thisSchedule->previous_schedule_id)
            ->where('co_product_id', $thisSchedule->co_product_id)
            ->first();

        if ($prevSchedule) {
            $newStart = Carbon::parse($thisSchedule->start_time)->addMinutes($delayMinutes);
            $prevEnd = Carbon::parse($prevSchedule->end_time);
            if ($newStart->lessThan($prevEnd)) {
                dd("Tidak bisa memajukan waktu karena bertabrakan dengan proses sebelumnya.");
                return redirect()->back()->withErrors(['error' => 'Tidak bisa memajukan waktu karena bertabrakan dengan proses sebelumnya.']);
            }
        }

        // Geser semua proses dalam rantai previous_schedule_id (ke depan)
        $current = $thisSchedule;
        while ($current) {
            $newStart = Carbon::parse($current->start_time)->addMinutes($delayMinutes);
            $newEnd = Carbon::parse($current->end_time)->addMinutes($delayMinutes);

            // Validasi waktu tidak absurd
            if ($newEnd->lessThanOrEqualTo($newStart)) {
                // dd("Durasi tidak valid setelah dimajukan pada schedule ID {$current->id}.");
                return redirect()->back()->withErrors(['error' => "Durasi tidak valid setelah dimajukan pada schedule ID {$current->id}."]);
            }

            $current->start_time = $newStart;
            $current->end_time = $newEnd;
            $current->save();

            if ($current->is_final_process)
                break;

            $current = Schedule::where('previous_schedule_id', $current->id)
                ->where('co_product_id', $current->co_product_id)
                ->first();
        }

        // Geser semua schedule lain yang bergantung ke process_id ini (process_dependency_id)
        $schedulesInDependency = Schedule::where('process_dependency_id', $thisSchedule->process_id)->get();

        foreach ($schedulesInDependency as $schedule) {
            $newStart = Carbon::parse($schedule->start_time)->addMinutes($delayMinutes);
            $newEnd = Carbon::parse($schedule->end_time)->addMinutes($delayMinutes);

            if ($newEnd->lessThanOrEqualTo($newStart)) {
                // dd("Durasi tidak valid setelah dimajukan pada schedule ID {$schedule->id}.");
                return redirect()->back()->withErrors(['error' => "Durasi tidak valid setelah dimajukan (dependency schedule ID {$schedule->id})."]);
            }

            $schedule->start_time = $newStart;
            $schedule->end_time = $newEnd;
            $schedule->save();
        }

        // Re-adjust waktu semua rantai yang berkaitan agar sesuai dengan dependency-nya
        $chain = Schedule::where('co_product_id', $thisSchedule->co_product_id)->get();

        foreach ($chain as $schedule) {
            $current = $schedule;
            while ($current) {
                $lastTime = Carbon::parse($current->end_time);
                $nextSchedule = Schedule::where('previous_schedule_id', $current->id)
                    ->where('co_product_id', $current->co_product_id)
                    ->first();

                if ($nextSchedule) {
                    $nextSchedule->start_time = $lastTime;
                    $nextSchedule->end_time = $lastTime->copy()->addMinutes($nextSchedule->plan_duration);
                    $nextSchedule->save();

                    if ($nextSchedule->is_final_process)
                        break;
                    $current = $nextSchedule;
                } else {
                    break;
                }
            }
        }

        // dd('All schedules advanced successfully.');
        return redirect()->back()->with('success', 'All schedules advanced successfully.');
    }

    public function updatePlanDurationByIdAndAdjustChain($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['error' => 'Schedule tidak ditemukan.'], 404);
        }

        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);
        $duration = $end->diffInMinutes($start);

        if ($duration <= 0) {
            return response()->json(['error' => 'Durasi tidak valid.'], 400);
        }

        // Update plan_duration
        $schedule->plan_duration = $duration;
        $schedule->save();

        // Adjust rantai schedule setelahnya
        $current = $schedule;
        while (true) {
            $next = Schedule::where('previous_schedule_id', $current->id)
                ->where('co_product_id', $current->co_product_id)
                ->first();

            if (!$next) {
                break;
            }

            $next->start_time = Carbon::parse($current->end_time);
            $next->end_time = Carbon::parse($next->start_time)->addMinutes($next->plan_duration);
            $next->save();

            $current = $next;
        }

        return response()->json([
            'message' => 'Plan duration dan rantai schedule berhasil diperbarui.',
            'schedule_id' => $schedule->id,
            'new_plan_duration' => $duration
        ]);
    }

    public function updateDependencySafe(Request $request, $id)
    {
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['error' => 'Schedule tidak ditemukan.'], 404);
        }

        $newPrevId = $request->input('previous_schedule_id');
        $newProcessDepId = $request->input('process_dependency_id');

        /*** 1️⃣ Validasi dasar ***/
        if ($newPrevId && !Schedule::find($newPrevId)) {
            return response()->json(['error' => 'Previous schedule ID tidak valid.'], 400);
        }
        if ($newProcessDepId && !Schedule::find($newProcessDepId)) {
            return response()->json(['error' => 'Process dependency ID tidak valid.'], 400);
        }

        if ($newPrevId == $schedule->id) {
            return response()->json(['error' => 'Previous schedule ID tidak boleh dirinya sendiri.'], 400);
        }

        /*** 2️⃣ Cek loop di previous_schedule_id chain ***/
        $current = $newPrevId ? Schedule::find($newPrevId) : null;
        while ($current) {
            if ($current->id == $schedule->id) {
                return response()->json(['error' => 'Update ini akan membuat loop di rantai proses.'], 400);
            }
            $current = $current->previous_schedule_id ? Schedule::find($current->previous_schedule_id) : null;
        }

        /*** 3️⃣ Simpan perubahan ***/
        $schedule->previous_schedule_id = $newPrevId;
        $schedule->process_dependency_id = $newProcessDepId;
        $schedule->save();

        /*** 4️⃣ Sesuaikan rantai berikutnya (prev chain) ***/
        $this->adjustFollowingChain($schedule);

        /*** 5️⃣ Sesuaikan dependency ***/
        $this->adjustDependencySchedules($schedule);

        return redirect()->back()->with('success', 'Dependency updated safely.');
    }

    private function adjustFollowingChain(Schedule $schedule)
    {
        $current = $schedule;
        while (true) {
            $next = Schedule::where('previous_schedule_id', $current->id)
                ->where('co_product_id', $current->co_product_id)
                ->first();

            if (!$next)
                break;

            $next->start_time = Carbon::parse($current->end_time);
            $next->end_time = Carbon::parse($next->start_time)->addMinutes($next->plan_duration);
            $next->save();

            $current = $next;
        }
    }

    private function adjustDependencySchedules(Schedule $schedule)
    {
        $dependentSchedules = Schedule::where('process_dependency_id', $schedule->process_id)->get();

        foreach ($dependentSchedules as $dep) {
            $latestEnd = Schedule::where('process_id', $schedule->process_id)
                ->where('co_product_id', $schedule->co_product_id)
                ->max('end_time');

            if ($latestEnd) {
                $dep->start_time = Carbon::parse($latestEnd);
                $dep->end_time = Carbon::parse($dep->start_time)->addMinutes($dep->plan_duration);
                $dep->save();
            }
        }
    }

    public function addDelay(Request $request, $schedule)
    {
        $delayMinutes = (int) $request->input('delay_minutes');
        $original = Schedule::findOrFail($schedule);
        // dd($original, $delayMinutes);

        try {
            $schedules = $this->simulateGraphDelay($original->id, $delayMinutes);

            // dd($schedules);

            // Simulasikan semua delay dan hasilkan daftar schedule baru
            // $schedules = $this->simulateDelays($original, $delayMinutes);

            // dd($schedules);

            // Validasi: bentrok mesin?
            // foreach ($schedules as $schedule) {
            //     if ($this->isScheduleConflict($schedule)) {
            //         // dd($schedule);
            //         throw new \Exception("Schedule conflict detected for process {$schedule->process_id} on machine {$schedule->machine_id}");
            //     }
            // }

            // Validasi: shipment check
            // foreach ($schedules as $schedule) {
            //     if ($schedule->is_final_process) {
            //         // dd($schedule);
            //         $shippingDate = optional($schedule->product)->shipping_date;
            //         if ($shippingDate && $schedule->end_time && $schedule->end_time->gt(Carbon::parse($shippingDate))) {
            //             return redirect()->back()->with(
            //                 'error',
            //                 "Schedule ID {$schedule->id} exceeds the shipping deadline of product ID {$schedule->product_id}."
            //             );
            //         }
            //     }
            // }

            // Semua valid → Simpan perubahan
            foreach ($schedules as $schedule) {
                $schedule->save();
            }

            // return response()->json(['message' => 'Delay applied successfully']);
            return redirect()->route('calender.index')->with('success', 'Delay applied successfully.');
        } catch (\Exception $e) {
            // return response()->json(['error' => $e->getMessage()], 400);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function simulateGraphDelay($scheduleId, $delayMinutes)
    {
        $schedules = Schedule::all(); // ambil semua jadwal
        // dd($schedules);

        $graph = new ScheduleGraph($schedules);

        $graph->propagateDelay($scheduleId, $delayMinutes);

        $updated = $graph->getUpdatedSchedules();
        // dd($updated);

        // echo "Original Schedule ID: {$scheduleId} with delay of {$delayMinutes} minutes<br>";

        // // foreach data awal
        // foreach ($schedules as $schedule) {
        //     if (isset($updated[$schedule->id])) {
        //         $startMark = $schedule->is_start_process ? '✅' : '';
        //         $finalMark = $schedule->is_final_process ? '🏁' : '';
        //         echo "Original Schedule {$schedule->id} - Product {$schedule->product_id} - Process {$schedule->process_id} start: {$schedule->start_time}, end: {$schedule->end_time} {$startMark}{$finalMark}<br>";
        //     }
        // }

        // echo "<br>Updated Schedules:<br>";

        // // foreach data yang sudah diupdate
        // foreach ($updated as $node) {
        //     $startMark = $node->is_start_process ? '✅' : '';
        //     $finalMark = $node->is_final_process ? '🏁' : '';
        //     echo "Schedule {$node->id} - Product {$node->product_id} - Process {$node->process_id} new start: {$node->start_time}, new end: {$node->end_time} {$startMark}{$finalMark}<br>";
        // }

        return $updated;
    }


    private function simulateDelays(
        Schedule $schedule,
        int $delayMinutes,
        array &$visited = [],
        array &$clonedMap = []
    ): array {

        // 1. Hindari siklus
        if (isset($visited[$schedule->id])) {
            return [];
        }
        $visited[$schedule->id] = true;

        // 2. Klon + geser
        $clone = clone $schedule;
        $clone->start_time = $schedule->start_time
            ? Carbon::parse($schedule->start_time)->addMinutes($delayMinutes)
            : null;
        $clone->end_time = $schedule->end_time
            ? Carbon::parse($schedule->end_time)->addMinutes($delayMinutes)
            : null;

        $clonedMap[$clone->id] = $clone;     // simpan agar bisa dipakai anaknya
        $result = [$clone];

        // 3. Ambil anak-anaknya
        $children = Schedule::where(function ($q) use ($schedule) {
            // linear flow dalam produk yg sama
            $q->where('previous_schedule_id', $schedule->id)
                ->where('co_product_id', $schedule->co_product_id);
        })
            ->orWhere(function ($q) use ($schedule) {
                // dependency lintas-produk
                $q->where('process_dependency_id', $schedule->id);
            })
            ->get();

        foreach ($children as $child) {
            // hitung start berdasarkan dependency (kalau ada)
            $depEnd = null;
            if (
                $child->process_dependency_id &&
                isset($clonedMap[$child->process_dependency_id])
            ) {
                $depEnd = Carbon::make(
                    $clonedMap[$child->process_dependency_id]->end_time
                );
            }

            $childClone = clone $child;

            if ($depEnd) {                               // mengikuti proses dependency
                $durasi = $child->start_time && $child->end_time
                    ? Carbon::parse($child->end_time)
                    ->diffInMinutes(Carbon::parse($child->start_time))
                    : 0;

                $childClone->start_time = $depEnd;
                $childClone->end_time = $depEnd->copy()->addMinutes($durasi);
            } else {                                     // cukup digeser delay
                $childClone->start_time = $child->start_time
                    ? Carbon::parse($child->start_time)->addMinutes($delayMinutes)
                    : null;
                $childClone->end_time = $child->end_time
                    ? Carbon::parse($child->end_time)->addMinutes($delayMinutes)
                    : null;
            }

            $clonedMap[$childClone->id] = $childClone;
            $visited[$childClone->id] = true;
            $result[] = $childClone;

            // ⬇️  **REKURSIKAN CLONE-NYA**, bukan objek asli
            $result = array_merge(
                $result,
                $this->simulateDelays($childClone, $delayMinutes, $visited, $clonedMap)
            );
        }

        return $result;
    }

    private function isScheduleConflict(Schedule $schedule): bool
    {
        return Schedule::where('id', '!=', $schedule->id)
            ->where('machine_id', $schedule->machine_id)
            ->where(function ($query) use ($schedule) {
                $query->where(function ($q) use ($schedule) {
                    $q->where('start_time', '<', $schedule->end_time)
                        ->where('end_time', '>', $schedule->start_time);
                });
            })->exists();
    }


    public function updateSchedule(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'delay_minutes' => 'required|integer|min:1',
        ]);

        try {
            $this->updateScheduleWithDelay($schedule, $validated['delay_minutes']);
            return redirect()->route('calender.index')->with('success', 'Schedule updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    function updateScheduleWithDelay(Schedule $schedule, int $delayMinutes)
    {
        $coProduct = $schedule->co_product;
        $shipmentDeadline = Carbon::parse($coProduct->shipment_date);

        // Step 1. Geser proses ini
        $schedule->start_time = Carbon::parse($schedule->start_time)->addMinutes($delayMinutes);
        $schedule->end_time = Carbon::parse($schedule->end_time)->addMinutes($delayMinutes);

        // Validasi batas pengiriman
        if ($schedule->end_time->greaterThan($shipmentDeadline)) {
            throw new \Exception("Proses ID {$schedule->id} melebihi batas pengiriman.");
        }

        $schedule->save();

        // Step 2. Update proses setelahnya di chain
        $this->updateNextSchedules($schedule, $delayMinutes, $shipmentDeadline);

        // Step 3. Update proses-proses yang depend
        $this->updateDependencySchedules($schedule, $delayMinutes, $shipmentDeadline);
    }

    function updateNextSchedules(Schedule $schedule, int $delayMinutes, Carbon $shipmentDeadline)
    {
        $next = Schedule::where('previous_schedule_id', $schedule->id)->first();

        if (!$next)
            return;

        $duration = Carbon::parse($next->end_time)->diffInMinutes(Carbon::parse($next->start_time));

        $startTime = Carbon::parse($schedule->end_time);
        $endTime = $startTime->copy()->addMinutes($duration);

        // Cek bentrok (di product dan process yang sama)
        if ($this->hasProductProcessConflict($next->product_id, $next->process_id, $startTime, $endTime, $next->id)) {
            $startTime = $this->getNextAvailableSmartTimeForProductProcess(
                $next->product_id,
                $next->process_id,
                $startTime,
                $duration,
                $shipmentDeadline
            );
            $endTime = $startTime->copy()->addMinutes($duration);
        }

        // Cek batas shipment
        if ($endTime->greaterThan($shipmentDeadline)) {
            throw new \Exception("Proses ID {$next->id} tidak bisa dijadwalkan sebelum batas pengiriman.");
        }

        // Update next schedule
        $next->start_time = $startTime;
        $next->end_time = $endTime;
        $next->save();

        // Recursive call
        $this->updateNextSchedules($next, $delayMinutes, $shipmentDeadline);
    }

    function updateDependencySchedules(Schedule $schedule, int $delayMinutes, Carbon $shipmentDeadline)
    {
        $dependentSchedules = Schedule::where('process_dependency_id', $schedule->id)->get();

        foreach ($dependentSchedules as $dependent) {
            $duration = Carbon::parse($dependent->end_time)->diffInMinutes(Carbon::parse($dependent->start_time));

            $startTime = Carbon::parse($schedule->end_time);
            $endTime = $startTime->copy()->addMinutes($duration);

            // Cek bentrok (di product dan process yang sama)
            if ($this->hasProductProcessConflict($dependent->product_id, $dependent->process_id, $startTime, $endTime, $dependent->id)) {
                $startTime = $this->getNextAvailableSmartTimeForProductProcess(
                    $dependent->product_id,
                    $dependent->process_id,
                    $startTime,
                    $duration,
                    $shipmentDeadline
                );
                $endTime = $startTime->copy()->addMinutes($duration);
            }

            // Cek batas shipment
            if ($endTime->greaterThan($shipmentDeadline)) {
                throw new \Exception("Proses ID {$dependent->id} tidak bisa dijadwalkan sebelum batas pengiriman.");
            }

            // Update dependent schedule
            $dependent->start_time = $startTime;
            $dependent->end_time = $endTime;
            $dependent->save();

            // Recursive call ke chain dan dependency lagi
            $this->updateNextSchedules($dependent, $delayMinutes, $shipmentDeadline);
            $this->updateDependencySchedules($dependent, $delayMinutes, $shipmentDeadline);
        }
    }

    function hasProductProcessConflict($coProductId, $processId, Carbon $start, Carbon $end, $excludeId = null): bool
    {
        return Schedule::where('co_product_id', $coProductId)
            ->where('process_id', $processId)
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

    function getNextAvailableSmartTimeForProductProcess($coProductId, $processId, Carbon $afterTime, int $durationMinutes, Carbon $shipmentDeadline): Carbon
    {
        $time = $afterTime->copy();

        while (true) {
            $conflict = Schedule::where('co_product_id', $coProductId)
                ->where('process_id', $processId)
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

            // Geser ke waktu setelah conflict
            $time = Carbon::parse($conflict->end_time)->copy();
        }
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('calender.index')->with('success', 'Schedule deleted successfully.');
    }
}
