<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Machine;
use App\Models\Process;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Http\Request;

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
