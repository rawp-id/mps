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

    public function schedule()
    {
        $products = Product::with('schedules')->get();
        $machines = Machine::all();
        $processes = Process::all();
    }

    public function updateSchedule(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'delay_minutes' => 'required|integer|min:1',
        ]);

        try {
            $this->updateScheduleWithDelay($schedule, $validated['delay_minutes']);
            return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    function updateScheduleWithDelay(Schedule $schedule, int $delayMinutes)
    {
        $product = $schedule->product;
        $shipmentDeadline = Carbon::parse($product->shipping_date);

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

        if (!$next) return;

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

    function hasProductProcessConflict($productId, $processId, Carbon $start, Carbon $end, $excludeId = null): bool
    {
        return Schedule::where('product_id', $productId)
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

    function getNextAvailableSmartTimeForProductProcess($productId, $processId, Carbon $afterTime, int $durationMinutes, Carbon $shipmentDeadline): Carbon
    {
        $time = $afterTime->copy();

        while (true) {
            $conflict = Schedule::where('product_id', $productId)
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
        return redirect()->route('schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}
