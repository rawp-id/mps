<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Models\SimulateSchedule;
use Illuminate\Support\Facades\DB;

class PlanSimulateController extends Controller
{
    public function index()
    {
        $plans = Plan::with('product')->get();
        return view('plan-simulate.index', compact('plans'));
    }

    public function create()
    {
        $products = \App\Models\Product::all();
        return view('plan-simulate.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'description' => 'nullable|string|max:1000',
        ]);

        // 1. Buat Plan
        $plan = Plan::create($request->all());

        // 2. Ambil Produk terkait
        $product = Product::findOrFail($plan->product_id);

        // 3. Konfigurasi default simulasi
        $speeds = [8000, 4000, 2000, 2000, 4000];
        $quantity = 8000;
        $gapBetweenProcesses = 20; // menit
        $gapBetweenDependencies = 20; // menit

        // 4. Ambil waktu terakhir per mesin dari schedule (jika ada)
        $rawSchedules = Schedule::select('machine_id', DB::raw('MAX(end_time) as latest_end'))
            ->groupBy('machine_id')
            ->get();

        $machineAvailableAt = [];
        foreach ($rawSchedules as $row) {
            $machineAvailableAt[$row->machine_id] = Carbon::parse($row->latest_end);
        }

        // Inisialisasi mesin yang belum punya jadwal
        foreach (range(1, 5) as $machineId) {
            if (!isset($machineAvailableAt[$machineId])) {
                $machineAvailableAt[$machineId] = Carbon::now();
            }
        }

        // 5. Simulasikan dan buat schedule
        $lastSchedulePerProcess = []; // Simpan schedule terakhir untuk process_id tertentu
        $prevScheduleId = null;
        $prevEndTime = Carbon::now();

        for ($i = 1; $i <= 5; $i++) {
            $planSpeed = $speeds[$i - 1];
            $conversion = $planSpeed / $quantity;
            $duration = $conversion * 60;

            $machineId = $i;
            $machineReady = $machineAvailableAt[$machineId]->copy();

            // Cek dependency jadwal terakhir untuk process ini (produk sebelumnya)
            $lastDependencyScheduleId = $lastSchedulePerProcess[$i] ?? null;
            $dependencyEndTime = null;

            if ($lastDependencyScheduleId) {
                $dependencySchedule = Schedule::find($lastDependencyScheduleId);
                if ($dependencySchedule) {
                    $dependencyEndTime = Carbon::parse($dependencySchedule->end_time)->addMinutes($gapBetweenDependencies);
                }
            }

            // Hitung start time (paling lambat dari semua constraint)
            $startCandidates = [$machineReady];

            if ($i == 1) {
                $startCandidates[] = $prevEndTime;
            } else {
                $startCandidates[] = $prevEndTime->copy()->addMinutes($gapBetweenProcesses);
            }

            if ($dependencyEndTime) {
                $startCandidates[] = $dependencyEndTime;
            }

            $start = collect($startCandidates)->max();
            $end = $start->copy()->addMinutes($duration);

            // Simpan schedule ke database
            $schedule = SimulateSchedule::create([
                'plan_id' => $plan->id,
                'product_id' => $product->id,
                'process_id' => $i,
                'machine_id' => $machineId,
                'previous_schedule_id' => $prevScheduleId,
                'process_dependency_id' => $lastDependencyScheduleId,
                'is_start_process' => $i == 1,
                'is_final_process' => $i == 5,
                'quantity' => $quantity,
                'plan_speed' => $planSpeed,
                'conversion_value' => $conversion,
                'plan_duration' => $duration,
                'start_time' => $start,
                'end_time' => $end,
            ]);

            // Update pointer untuk proses selanjutnya
            $prevScheduleId = $schedule->id;
            $prevEndTime = $end->copy();
            $machineAvailableAt[$machineId] = $end->copy();
            $lastSchedulePerProcess[$i] = $schedule->id;
        }

        return redirect()->route('plan-simulate.index')->with('success', 'Plan dan simulasi jadwal berhasil dibuat tanpa bentrok.');
    }

    public function show($plan)
    {
        // dd($plan);
        $plan = Plan::with('product')->findOrFail($plan);

        $schedules = SimulateSchedule::with(['machine', 'product', 'process'])
            ->where('plan_id', $plan->id)
            ->get();

        // dd($schedules);

        return view('plan-simulate.show', compact('plan', 'schedules'));
    }

    public function destroy($plan)
    {
        $plan = Plan::findOrFail($plan);
        
        // Hapus semua jadwal simulasi terkait plan ini
        SimulateSchedule::where('plan_id', $plan->id)->delete();

        // Hapus plan itu sendiri
        $plan->delete();

        return redirect()->route('plan-simulate.index')->with('success', 'Plan dan semua jadwal simulasi berhasil dihapus.');
    }

}
