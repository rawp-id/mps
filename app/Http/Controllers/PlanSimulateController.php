<?php

namespace App\Http\Controllers;

use App\Models\Co;
use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\Operations;
use App\Models\PlanProductCo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SimulateSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PlanSimulateController extends Controller
{
    public static function generateSimulationSchedule($planProductCos, $start)
    {
        // Kumpulkan mapping product_id -> (co_id, is_locked) dari planProductCos
        $entries = collect($planProductCos)->map(function ($ppc) {
            return [
                'product_id' => optional($ppc->co)->product_id,
                'co_id'      => optional($ppc->co)->id,
                'is_locked'  => (bool) (optional($ppc->co)->is_locked ?? false),
            ];
        })->filter(fn($e) => !empty($e['product_id']) && !empty($e['co_id']))->values();

        // Ambil produk dari DB berdasarkan product_id di entries
        $productIds = $entries->pluck('product_id')->unique()->values()->all();
        $products = Product::whereIn('id', $productIds)->orderBy('shipping_date')->get()->keyBy('id');

        $urlLocal = 'http://localhost:5000/schedule';
        $url      = 'http://202.58.200.244:5000/schedule';

        // Tentukan start default bila null
        $firstProduct = $products->first();
        $startCarbon  = $start
            ? Carbon::parse($start)
            : ($firstProduct ? Carbon::parse($firstProduct->shipping_date)->subDay()->startOfDay() : now());

        $operations = Operations::with(['process', 'machine'])->get()->keyBy('id');

        // Gabungkan co info per product
        $byProduct = $entries->groupBy('product_id')->map(function ($rows) {
            $row = collect($rows)->first();
            return [
                'co_id'     => (int) $row['co_id'],
                'is_locked' => (bool) $row['is_locked'],
            ];
        });

        // Optional body-level locked map { "co_id": 0/1 } untuk Python
        $locksMap = $entries->mapWithKeys(fn($e) => [
            (string) (int) $e['co_id'] => (int) $e['is_locked']
        ])->toArray();

        $productsPayload = [];
        foreach ($byProduct as $pid => $coInfo) {
            /** @var Product|null $p */
            $p = $products->get($pid);
            if (!$p) continue;

            $operationIds = is_array($p->process_details)
                ? $p->process_details
                : explode(',', (string) $p->process_details);

            $tasks = [];
            foreach ($operationIds as $opId) {
                $opId = (int) trim($opId);
                if ($opId && isset($operations[$opId])) {
                    $duration = (int) ($operations[$opId]->duration ?? 0);
                    $tasks[] = [$opId, $duration, (bool) $coInfo['is_locked']];
                }
            }

            $shipmentDeadline = $p->shipment_deadline ?? $p->shipping_date;

            $productsPayload[] = [
                'id'                => (int) $p->id,
                'name'              => (string) $p->name,
                'shipment_deadline' => Carbon::parse($shipmentDeadline)->format('Y-m-d H:i'),
                'tasks'             => $tasks,
            ];
        }

        $datas = [
            'start_time' => $startCarbon->format('Y-m-d H:i'),
            'products'   => $productsPayload,
        ];

        // dd($datas);

        try {
            try {
                $response = Http::timeout(30)->post($urlLocal, $datas);
                if (!$response->ok()) {
                    $response = Http::timeout(30)->post($url, $datas);
                }
            } catch (\Exception $ex) {
                $response = Http::timeout(30)->post($url, $datas);
            }
            return $response->object();
        } catch (\Exception $e) {
            throw new \Exception('Gagal menghubungi API: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $plans = Plan::all();
        return view('plan-simulate.index', compact('plans'));
    }

    public function create()
    {
        $plans = Plan::all();
        $products = Product::where('is_completed', false)->get();
        $cos = Co::with('product')->where('is_completed', false)->get(); // Ambil semua CO jika diperlukan
        // dd($products, $cos, $plans);
        return view('plan-simulate.create', compact('products', 'cos', 'plans'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $plan = new Plan();

        // Generate unique name for plan
        if (isset($request->plan_id)) {
            $plan = Plan::findOrFail($request->plan_id);
            $generatedName = $plan->name; // Gunakan nama yang sudah ada
        } else if (isset($request->new_plan_name)) {
            $generatedName = $request->new_plan_name;
        } else {
            do {
                $generatedName = 'PLAN-' . strtoupper(Str::random(8));
            } while (Plan::where('name', $generatedName)->exists());
        }

        $request->merge(['name' => $generatedName]);

        // $request->validate([
        //     'name' => 'required|string|max:255|unique:plans,name',
        //     'product_id' => 'required|exists:products,id',
        //     'product_ids' => 'nullable|array',
        //     'description' => 'nullable|string|max:1000',
        // ]);

        try {

            DB::beginTransaction();

            // 1. Buat Plan
            $plan = Plan::create($request->all());

            foreach ($request->input('co_ids', []) as $coId) {
                $plan->planProductCos()->create([
                    'plan_id' => $plan->id,
                    'product_id' => null,
                    'co_id' => $coId,
                ]);
            }

            // dd($plan->planProductCos());

            $coIds = $request->input('co_ids', []);

            $cos = Co::whereIn('id', $coIds)
                ->get();

            // dd($products);

            // dd($products);

            // 2. Ambil Produk terkait
            // $product = Product::findOrFail($plan->product_id);

            // $start = $request->input('start_time', Carbon::now()->startOfDay());

            // $apiUrlLocal = 'http://127.0.0.1:5000/schedule';

            // $apiUrl = 'https://rest.mps.rawp.my.id/schedule';

            // $startTime = microtime(true);

            // $start_products = Product::orderBy('shipping_date')->first();

            // // Cek apakah start_product sudah ada di Schedule
            // $schedule = Schedule::where('product_id', $start_products->id)
            //     ->orderBy('process_id', 'asc')
            //     ->first();

            // if ($schedule) {
            //     // Ambil process sebelumnya (jika ada)
            //     $prevProcessId = $schedule->process_id - 1;
            //     $prevSchedule = Schedule::where('product_id', $start_products->id)
            //         ->where('process_id', $prevProcessId)
            //         ->orderBy('end_time', 'desc')
            //         ->first();

            //     if ($prevSchedule) {
            //         $start = Carbon::parse($prevSchedule->end_time);
            //     } else {
            //         $start = Carbon::parse($start_products->shipping_date)->subDay()->startOfDay();
            //     }
            // } else {
            //     $start = Carbon::parse($start_products->shipping_date)->subDay()->startOfDay();
            // }

            // // $products = DB::table('products')
            // //     ->select(
            // //         'id',
            // //         'code',
            // //         'name',
            // //         DB::raw('shipping_date as shipment_deadline'),
            // //         'process_details',
            // //         'created_at',
            // //         'updated_at'
            // //     )
            // //     ->get();

            // $operations = Operations::with(['process', 'machine'])->get()->keyBy('id');

            // // dd($operations);

            // $datas = [
            //     'start_time' => $start instanceof Carbon ? $start->format('Y-m-d H:i') : $start,
            //     'products' => $products->map(function ($product) use ($operations) {
            //         $operationIds = is_array($product->process_details)
            //             ? $product->process_details
            //             : explode(',', $product->process_details);

            //         $tasks = [];
            //         foreach ($operationIds as $opId) {
            //             $opId = trim($opId);
            //             if ($opId && isset($operations[$opId])) {
            //                 $duration = $operations[$opId]->duration ?? 0;
            //                 $tasks[] = [(int) $opId, $duration];
            //             }
            //         }

            //         return [
            //             'id' => $product->id,
            //             'name' => $product->name,
            //             'shipment_deadline' => Carbon::parse($product->shipment_deadline)->format('Y-m-d H:i'),
            //             'tasks' => $tasks,
            //         ];
            //     })->toArray(),
            // ];
            // // dd($datas);

            // try {
            //     try {
            //         $response = Http::timeout(30)->post($apiUrlLocal, $datas);
            //         if (!$response->ok()) {
            //             // Jika gagal, coba ke API kedua
            //             $response = Http::timeout(30)->post($apiUrl, $datas);
            //         }
            //     } catch (\Exception $ex) {
            //         // Jika gagal, coba ke API kedua
            //         $response = Http::timeout(30)->post($apiUrl, $datas);
            //     }
            //     $response_data = $response->object();
            // } catch (\Exception $e) {
            //     DB::rollBack();
            //     return redirect()->route('plan-simulate.index')->withErrors('Gagal menghubungi API: ' . $e->getMessage());
            // }

            // foreach ($response_data->tasks as $schedule) {
            //     // dd($schedule);
            //     $simulate = SimulateSchedule::create([
            //         'plan_id' => $plan->id,
            //         'product_id' => $schedule->product_id ?? $schedule->productId ?? null,
            //         'operation_id' => $schedule->operation_id ?? null,
            //         'quantity' => $schedule->quantity ?? 0,
            //         'plan_speed' => $schedule->plan_speed ?? 0,
            //         'conversion_value' => $schedule->conversion_value ?? 0,
            //         'plan_duration' => $schedule->duration ?? 0,
            //         'start_time' => isset($schedule->start_time) ? Carbon::parse($schedule->start_time) : (isset($schedule->startTime) ? Carbon::parse($schedule->startTime) : null),
            //         'end_time' => isset($schedule->end_time) ? Carbon::parse($schedule->end_time) : (isset($schedule->endTime) ? Carbon::parse($schedule->endTime) : null),
            //     ]);

            //     // dd($simulate);
            // }

            // dd(SimulateSchedule::all());

            // $duration = round(microtime(true) - $startTime, 2);

            // dd($duration);

            // dd($response_data, isset($response_data->tasks));

            // if ($response_data && isset($response_data->tasks)) {
            // }
            DB::commit();
            // return redirect()->route('plan-simulate.index')->with('success', 'Plans generated successfully. Processed in ' . $duration . ' seconds.');
            return redirect()->route('plan-simulate.index')->with('success', 'Plans created successfully.');

            // DB::rollBack();
            // return redirect()->route('plan-simulate.index')->withErrors('Failed to generate plans.');
        } catch (\Throwable $e) {
            return redirect()->route('plan-simulate.index')->withErrors('Error: ' . $e->getMessage());
        }

        // return redirect()->route('plan-simulate.index')->with('success', 'Plan dan simulasi jadwal berhasil dibuat tanpa bentrok.');
    }

    // public function store(Request $request)
    // {
    //     dd($request->all());
    //     // Generate unique name for plan
    //     do {
    //         $generatedName = 'PLAN-' . strtoupper(Str::random(8));
    //     } while (Plan::where('name', $generatedName)->exists());

    //     $request->merge(['name' => $generatedName]);

    //     $request->validate([
    //         'name' => 'required|string|max:255|unique:plans,name',
    //         // 'product_id' => 'required|exists:products,id',
    //         'product_ids' => 'nullable|array',
    //         'description' => 'nullable|string|max:1000',
    //     ]);

    //     try {

    //         DB::beginTransaction();

    //         // 1. Buat Plan
    //         $plan = Plan::create($request->all());

    //         foreach ($request->input('product_ids', []) as $productId) {
    //             $plan->planProductCos()->create([
    //                 'plan_id' => $plan->id,
    //                 'product_id' => $productId,
    //                 'co_id' => null, // Atau bisa diisi dengan CO yang relevan jika ada
    //             ]);
    //         }

    //         // dd($plan->planProductCos());

    //         $productIds = $request->input('product_ids', []);

    //         $products = Product::whereIn('id', $productIds)
    //             ->get();

    //         // dd($products);

    //         // dd($products);

    //         // 2. Ambil Produk terkait
    //         // $product = Product::findOrFail($plan->product_id);

    //         $start = $request->input('start_time', Carbon::now()->startOfDay());

    //         $apiUrlLocal = 'http://127.0.0.1:5000/schedule';

    //         $apiUrl = 'https://rest.mps.rawp.my.id/schedule';

    //         $startTime = microtime(true);

    //         $start_products = Product::orderBy('shipping_date')->first();

    //         // Cek apakah start_product sudah ada di Schedule
    //         $schedule = Schedule::where('product_id', $start_products->id)
    //             ->orderBy('process_id', 'asc')
    //             ->first();

    //         if ($schedule) {
    //             // Ambil process sebelumnya (jika ada)
    //             $prevProcessId = $schedule->process_id - 1;
    //             $prevSchedule = Schedule::where('product_id', $start_products->id)
    //                 ->where('process_id', $prevProcessId)
    //                 ->orderBy('end_time', 'desc')
    //                 ->first();

    //             if ($prevSchedule) {
    //                 $start = Carbon::parse($prevSchedule->end_time);
    //             } else {
    //                 $start = Carbon::parse($start_products->shipping_date)->subDay()->startOfDay();
    //             }
    //         } else {
    //             $start = Carbon::parse($start_products->shipping_date)->subDay()->startOfDay();
    //         }

    //         // $products = DB::table('products')
    //         //     ->select(
    //         //         'id',
    //         //         'code',
    //         //         'name',
    //         //         DB::raw('shipping_date as shipment_deadline'),
    //         //         'process_details',
    //         //         'created_at',
    //         //         'updated_at'
    //         //     )
    //         //     ->get();

    //         $operations = Operations::with(['process', 'machine'])->get()->keyBy('id');

    //         // dd($operations);

    //         $datas = [
    //             'start_time' => $start instanceof Carbon ? $start->format('Y-m-d H:i') : $start,
    //             'products' => $products->map(function ($product) use ($operations) {
    //                 $operationIds = is_array($product->process_details)
    //                     ? $product->process_details
    //                     : explode(',', $product->process_details);

    //                 $tasks = [];
    //                 foreach ($operationIds as $opId) {
    //                     $opId = trim($opId);
    //                     if ($opId && isset($operations[$opId])) {
    //                         $duration = $operations[$opId]->duration ?? 0;
    //                         $tasks[] = [(int) $opId, $duration];
    //                     }
    //                 }

    //                 return [
    //                     'id' => $product->id,
    //                     'name' => $product->name,
    //                     'shipment_deadline' => Carbon::parse($product->shipment_deadline)->format('Y-m-d H:i'),
    //                     'tasks' => $tasks,
    //                 ];
    //             })->toArray(),
    //         ];
    //         // dd($datas);

    //         try {
    //             try {
    //                 $response = Http::timeout(30)->post($apiUrlLocal, $datas);
    //                 if (!$response->ok()) {
    //                     // Jika gagal, coba ke API kedua
    //                     $response = Http::timeout(30)->post($apiUrl, $datas);
    //                 }
    //             } catch (\Exception $ex) {
    //                 // Jika gagal, coba ke API kedua
    //                 $response = Http::timeout(30)->post($apiUrl, $datas);
    //             }
    //             $response_data = $response->object();
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             return redirect()->route('plan-simulate.index')->withErrors('Gagal menghubungi API: ' . $e->getMessage());
    //         }

    //         foreach ($response_data->tasks as $schedule) {
    //             // dd($schedule);
    //             $simulate = SimulateSchedule::create([
    //                 'plan_id' => $plan->id,
    //                 'product_id' => $schedule->product_id ?? $schedule->productId ?? null,
    //                 'operation_id' => $schedule->operation_id ?? null,
    //                 'quantity' => $schedule->quantity ?? 0,
    //                 'plan_speed' => $schedule->plan_speed ?? 0,
    //                 'conversion_value' => $schedule->conversion_value ?? 0,
    //                 'plan_duration' => $schedule->duration ?? 0,
    //                 'start_time' => isset($schedule->start_time) ? Carbon::parse($schedule->start_time) : (isset($schedule->startTime) ? Carbon::parse($schedule->startTime) : null),
    //                 'end_time' => isset($schedule->end_time) ? Carbon::parse($schedule->end_time) : (isset($schedule->endTime) ? Carbon::parse($schedule->endTime) : null),
    //             ]);

    //             // dd($simulate);
    //         }

    //         // dd(SimulateSchedule::all());

    //         $duration = round(microtime(true) - $startTime, 2);

    //         // dd($duration);

    //         // dd($response_data, isset($response_data->tasks));

    //         if ($response_data && isset($response_data->tasks)) {
    //             DB::commit();
    //             return redirect()->route('plan-simulate.index')->with('success', 'Plans generated successfully. Processed in ' . $duration . ' seconds.');
    //         }

    //         DB::rollBack();
    //         return redirect()->route('plan-simulate.index')->withErrors('Failed to generate plans.');
    //     } catch (\Throwable $e) {
    //         return redirect()->route('plan-simulate.index')->withErrors('Error: ' . $e->getMessage());
    //     }

    //     // return redirect()->route('plan-simulate.index')->with('success', 'Plan dan simulasi jadwal berhasil dibuat tanpa bentrok.');
    // }

    // public function store(Request $request)
    // {
    //     // Generate unique name for plan
    //     do {
    //         $generatedName = 'PLAN-' . strtoupper(Str::random(8));
    //     } while (Plan::where('name', $generatedName)->exists());

    //     $request->merge(['name' => $generatedName]);

    //     $request->validate([
    //         'name' => 'required|string|max:255|unique:plans,name',
    //         'product_id' => 'required|exists:products,id',
    //         'description' => 'nullable|string|max:1000',
    //     ]);

    //     // 1. Buat Plan
    //     $plan = Plan::create($request->all());

    //     // 2. Ambil Produk terkait
    //     $product = Product::findOrFail($plan->product_id);

    //     // 3. Konfigurasi default simulasi
    //     $speeds = [8000, 4000, 2000, 2000, 4000];
    //     $quantity = 8000;
    //     $gapBetweenProcesses = 20; // menit
    //     $gapBetweenDependencies = 20; // menit

    //     // 4. Ambil waktu terakhir per mesin dari schedule (jika ada)
    //     $rawSchedules = Schedule::select('machine_id', DB::raw('MAX(end_time) as latest_end'))
    //         ->groupBy('machine_id')
    //         ->get();

    //     $machineAvailableAt = [];
    //     foreach ($rawSchedules as $row) {
    //         $machineAvailableAt[$row->machine_id] = Carbon::parse($row->latest_end);
    //     }

    //     // Inisialisasi mesin yang belum punya jadwal
    //     foreach (range(1, 5) as $machineId) {
    //         if (!isset($machineAvailableAt[$machineId])) {
    //             $machineAvailableAt[$machineId] = Carbon::now();
    //         }
    //     }

    //     // 5. Simulasikan dan buat schedule
    //     $lastSchedulePerProcess = []; // Simpan schedule terakhir untuk process_id tertentu
    //     $prevScheduleId = null;
    //     $prevEndTime = Carbon::now();

    //     for ($i = 1; $i <= 5; $i++) {
    //         $planSpeed = $speeds[$i - 1];
    //         $conversion = $planSpeed / $quantity;
    //         $duration = $conversion * 60;

    //         $machineId = $i;
    //         $machineReady = $machineAvailableAt[$machineId]->copy();

    //         // Cek dependency jadwal terakhir untuk process ini (produk sebelumnya)
    //         $lastDependencyScheduleId = $lastSchedulePerProcess[$i] ?? null;
    //         $dependencyEndTime = null;

    //         if ($lastDependencyScheduleId) {
    //             $dependencySchedule = Schedule::find($lastDependencyScheduleId);
    //             if ($dependencySchedule) {
    //                 $dependencyEndTime = Carbon::parse($dependencySchedule->end_time)->addMinutes($gapBetweenDependencies);
    //             }
    //         }

    //         // Hitung start time (paling lambat dari semua constraint)
    //         $startCandidates = [$machineReady];

    //         if ($i == 1) {
    //             $startCandidates[] = $prevEndTime;
    //         } else {
    //             $startCandidates[] = $prevEndTime->copy()->addMinutes($gapBetweenProcesses);
    //         }

    //         if ($dependencyEndTime) {
    //             $startCandidates[] = $dependencyEndTime;
    //         }

    //         $start = collect($startCandidates)->max();
    //         $end = $start->copy()->addMinutes($duration);

    //         // Simpan schedule ke database
    //         $schedule = SimulateSchedule::create([
    //             'plan_id' => $plan->id,
    //             'product_id' => $product->id,
    //             'process_id' => $i,
    //             'machine_id' => $machineId,
    //             'previous_schedule_id' => $prevScheduleId,
    //             'process_dependency_id' => $lastDependencyScheduleId,
    //             'is_start_process' => $i == 1,
    //             'is_final_process' => $i == 5,
    //             'quantity' => $quantity,
    //             'plan_speed' => $planSpeed,
    //             'conversion_value' => $conversion,
    //             'plan_duration' => $duration,
    //             'start_time' => $start,
    //             'end_time' => $end,
    //         ]);

    //         // Update pointer untuk proses selanjutnya
    //         $prevScheduleId = $schedule->id;
    //         $prevEndTime = $end->copy();
    //         $machineAvailableAt[$machineId] = $end->copy();
    //         $lastSchedulePerProcess[$i] = $schedule->id;
    //     }

    //     return redirect()->route('plan-simulate.index')->with('success', 'Plan dan simulasi jadwal berhasil dibuat tanpa bentrok.');
    // }

    public function show($plan)
    {
        // dd($plan);
        $plan = Plan::with('product', 'co', 'planProductCos', 'planProductCos.product', 'planProductCos.co')->findOrFail($plan);

        // dd($plan->planProductCos);

        $products = Product::whereNotIn('id', $plan->planProductCos->pluck('product_id'))->get();
        // dd($products);

        $cos = Co::whereNotIn('id', $plan->planProductCos->pluck('co_id'))->get();
        // dd($cos);

        // dd($plan);
        // Ambil filter dari request
        $startDate = request('start_date');
        $endDate = request('end_date');
        $category = request('category');
        $machineId = request('machine_id');
        $processId = request('process_id');

        $schedulesQuery = SimulateSchedule::with([
            'machine',
            'product',
            'process',
            'operation',
            'operation.machine',
            'operation.process'
        ])->where('plan_id', $plan->id);

        if ($startDate) {
            $schedulesQuery->whereDate('start_time', '>=', $startDate);
        }
        if ($endDate) {
            $schedulesQuery->whereDate('end_time', '<=', $endDate);
        }
        if ($machineId) {
            $schedulesQuery->whereHas('operation', function ($query) use ($machineId) {
                $query->where('machine_id', $machineId);
            });
        }
        if ($processId) {
            $schedulesQuery->whereHas('operation', function ($query) use ($processId) {
                $query->where('process_id', $processId);
            });
        }

        $schedules = $schedulesQuery->orderBy('start_time', 'asc')->get();

        // dd($schedules);

        // Untuk modal filter, ambil semua mesin dan proses
        $machines = \App\Models\Machine::all();
        $processes = \App\Models\Process::all();

        // dd($schedules);

        return view('plan-simulate.show', compact('plan', 'schedules', 'machines', 'processes', 'startDate', 'endDate', 'category', 'machineId', 'processId', 'products', 'cos'));
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

    public function updateDuration(Request $request, $plan)
    {
        $plan = Plan::findOrFail($plan);

        $request->validate([
            'duration' => 'required|integer|min:0',
        ]);



        // Update durasi plan
        $plan->update(['duration' => $request->input('duration')]);

        return redirect()->route('plan-simulate.index')->with('success', 'Plan duration updated successfully.');
    }

    public function applyToSchedule($plan)
    {
        $plan = Plan::findOrFail($plan);

        // Ambil semua jadwal simulasi terkait plan ini
        $schedules = SimulateSchedule::where('plan_id', $plan->id)->get();

        foreach ($schedules as $schedule) {
            Schedule::create([
                'product_id' => $schedule->product_id,
                'co_id' => $schedule->co_id,
                'operation_id' => $schedule->operation_id,
                'process_id' => $schedule->process_id,
                'machine_id' => $schedule->machine_id,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'quantity' => $schedule->quantity,
                'plan_speed' => $schedule->plan_speed,
                'conversion_value' => $schedule->conversion_value,
                'plan_duration' => $schedule->plan_duration,
            ]);
        }

        $plan->update(['is_applied' => true]);

        return redirect()->route('schedules.gantt')->with('success', 'Schedules applied from plan successfully.');
    }

    public function addCoToPlan(Request $request, $plan)
    {
        // dd($request->all());

        $plan = Plan::findOrFail($plan);

        $request->validate([
            'co_ids' => 'required|array',
            'co_ids.*' => 'exists:cos,id',
        ]);

        // Tambahkan CO ke plan
        foreach ($request->input('co_ids', []) as $coId) {
            $plan->planProductCos()->create([
                'plan_id' => $plan->id,
                'product_id' => null, // Atau bisa diisi dengan ID produk jika ada
                'co_id' => $coId,
            ]);
        }

        return redirect()->route('plan-simulate.show', $plan->id)->with('success', 'CO added to plan successfully.');
    }

    public function destroyCoFromPlan($id)
    {
        $planProductCo = PlanProductCo::findOrFail($id);
        $plan = $planProductCo->plan;

        // Hapus CO dari plan
        $planProductCo->delete();

        return redirect()->route('plan-simulate.show', $plan->id)->with('success', 'CO removed from plan successfully.');
    }

    public function generatePlan(Request $request, $planId)
    {
        $locked = $request->input('locked', []);

        // normalkan map locked dari request (opsional)
        $lockedNorm = [];
        foreach ($locked as $k => $v) {
            $lockedNorm[(int) $k] = (int) $v;
        }

        $plan = Plan::with('planProductCos', 'planProductCos.co')->findOrFail($planId);

        // suntikkan is_locked ke CO
        $plan->planProductCos->transform(function ($ppc) use ($lockedNorm) {
            if ($ppc->co) {
                $coId = (int) $ppc->co->id;
                $ppc->co->setAttribute(
                    'is_locked',
                    array_key_exists($coId, $lockedNorm) ? (bool) $lockedNorm[$coId] : (bool) ($ppc->co->is_locked ?? false)
                );
            }
            return $ppc;
        });

        // >>> PANGGIL METHOD BARU (V2) YANG BAWA co_id + is_locked <<<
        $generate = $this->generateSimulationSchedule($plan->planProductCos, $plan->start_time);

        // dd($generate);
        // simpan hasil
        foreach ($generate->tasks ?? [] as $schedule) {
            SimulateSchedule::create([
                'plan_id'          => $plan->id,
                'product_id'       => $schedule->product_id ?? $schedule->productId ?? null,
                'operation_id'     => $schedule->operation_id ?? null,
                'quantity'         => $schedule->quantity ?? 0,
                'plan_speed'       => $schedule->plan_speed ?? 0,
                'conversion_value' => $schedule->conversion_value ?? 0,
                'plan_duration'    => $schedule->duration ?? 0,
                'start_time'       => isset($schedule->start_time) ? Carbon::parse($schedule->start_time)
                    : (isset($schedule->startTime) ? Carbon::parse($schedule->startTime) : null),
                'end_time'         => isset($schedule->end_time) ? Carbon::parse($schedule->end_time)
                    : (isset($schedule->endTime) ? Carbon::parse($schedule->endTime) : null),
                'is_locked'        => (bool) ($schedule->is_locked ?? false),
            ]);
        }

        return redirect()->route('plan-simulate.show', $plan->id)->with('success', 'Plan generated successfully.');
    }
}
