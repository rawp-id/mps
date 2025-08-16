<?php

namespace App\Http\Controllers;

use App\Models\ComponentProduct;
use App\Models\ProcessProduct;
use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Machine;
use App\Models\Operations;
use App\Models\Process;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        $products = $query->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $products = Product::all();
        return view('products.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:products,code',
            'name' => 'required',
            'shipping_date' => 'nullable|date',
            'process_details' => 'nullable|string',
            'main_components' => 'required|array',
            'main_components.*.name' => 'required|string|max:255',
            'main_components.*.quantity' => 'required|numeric|min:0',
            'main_components.*.unit' => 'required|string|in:pcs,kg,liter,meter,other',
            'main_components.*.unit_custom' => 'nullable|string|required_if:main_components.*.unit,other',
        ]);

        $product = Product::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'shipping_date' => $validated['shipping_date'],
            'process_details' => $validated['process_details']
        ]);

        foreach ($validated['main_components'] as $component) {
            if ($product) {
                $letters = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 3);

                $numbers = rand(1000, 9999);
                $code = $letters . '-' . $numbers;

                ComponentProduct::create([
                    'product_id' => $product->id,
                    'code' => $code,
                    'name' => $component['name'],
                    'quantity' => $component['quantity'],
                    'unit' => $component['unit_custom'] ?? $component['unit']
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $ganttData = Schedule::where('product_id', $product->id)
            ->with(['process', 'machine', 'operation', 'operation.machine', 'operation.process'])
            ->orderBy('start_time')
            ->get();

        return view('products.show', compact('product', 'ganttData'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'required|unique:products,code,' . $product->id,
            'name' => 'required',
            'shipping_date' => 'nullable|date',
            'process_details' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function importForm()
    {
        return view('products.import');
    }

    // public function importPreview(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,csv,txt',
    //     ]);

    //     $path = $request->file('file')->store('temp');

    //     $data = Excel::toCollection(null, storage_path('app/' . $path))->first();

    //     if ($data->isEmpty()) {
    //         return back()->withErrors('File is empty or invalid format.');
    //     }

    //     // Remove header row if needed
    //     $rows = $data->skip(1)->values();

    //     // Simpan sementara di session untuk konfirmasi
    //     session(['import_products_data' => $rows]);

    //     return view('products.import-preview', [
    //         'headers' => $data->first(),
    //         'rows' => $rows,
    //     ]);
    // }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,txt',
        ]);

        // Parse langsung
        $collection = Excel::toCollection(null, $request->file('file'))->first();

        if ($collection->isEmpty()) {
            return back()->withErrors('File is empty or invalid format.');
        }

        // Misal header di baris pertama
        $headers = $collection->first();
        $rows = $collection->skip(1)->values();

        // SIMPAN array KE SESSION
        session([
            'import_products_headers' => $headers,
            'import_products_data' => $rows
        ]);

        return view('products.import-preview', compact('headers', 'rows'));
    }

    public function importStore(Request $request)
    {
        $rows = session('import_products_data');

        if (!$rows) {
            return redirect()->route('products.import')->withErrors('No data to import.');
        }

        $startTime = microtime(true);

        foreach ($rows as $row) {
            Product::create([
                'code' => $row[0],
                'name' => $row[1],
                'shipping_date' => $row[2] ?? null,
                'process_details' => $row[3] ?? null,
            ]);
        }

        $duration = round(microtime(true) - $startTime, 2);

        session()->forget(['import_products_data', 'import_products_headers']);

        return redirect()->route('products.index')->with('success', "Products imported successfully. (Processed in {$duration} seconds)");
    }

    public function generatePlans(Request $request)
    {
        try {
            $start = $request->input('start_time', Carbon::now()->startOfDay());

            $apiUrl = 'https://rest.mps.rawp.my.id/schedule';

            $startTime = microtime(true);

            $start_products = Product::orderBy('shipping_date')->first();

            $start = Carbon::parse($start_products->shipping_date)->subDay()->startOfDay();

            $products = DB::table('products')
                ->select(
                    'id',
                    'code',
                    'name',
                    DB::raw('shipping_date as shipment_deadline'),
                    'process_details',
                    'created_at',
                    'updated_at'
                )
                ->get();

            $operations = Operations::with(['process', 'machine'])->get()->keyBy('id');

            $datas = [
                'start_time' => $start instanceof Carbon ? $start->format('Y-m-d H:i') : $start,
                'products' => $products->map(function ($product) use ($operations) {
                    $operationIds = is_array($product->process_details)
                        ? $product->process_details
                        : explode(',', $product->process_details);

                    $tasks = [];
                    foreach ($operationIds as $opId) {
                        $opId = trim($opId);
                        if ($opId && isset($operations[$opId])) {
                            $duration = $operations[$opId]->duration ?? 0;
                            $tasks[] = [(int) $opId, $duration];
                        }
                    }

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'shipment_deadline' => Carbon::parse($product->shipment_deadline)->format('Y-m-d H:i'),
                        'tasks' => $tasks,
                    ];
                })->toArray(),
            ];

            $response = Http::post($apiUrl, $datas);

            $response_data = $response->object();

            foreach ($response_data->tasks as $schedule) {
                Schedule::create([
                    'product_id' => $schedule->product_id ?? $schedule->productId ?? null,
                    'operation_id' => $schedule->operation_id ?? null,
                    'is_start_process' => $schedule->is_start_process ?? $schedule->isStartProcess ?? false,
                    'is_final_process' => $schedule->is_final_process ?? $schedule->isFinalProcess ?? false,
                    'quantity' => $schedule->quantity ?? 0,
                    'plan_speed' => $schedule->plan_speed ?? 0,
                    'conversion_value' => $schedule->conversion_value ?? 0,
                    'plan_duration' => $schedule->duration ?? 0,
                    'start_time' => isset($schedule->start_time) ? Carbon::parse($schedule->start_time) : (isset($schedule->startTime) ? Carbon::parse($schedule->startTime) : null),
                    'end_time' => isset($schedule->end_time) ? Carbon::parse($schedule->end_time) : (isset($schedule->endTime) ? Carbon::parse($schedule->endTime) : null),
                ]);
            }

            $duration = round(microtime(true) - $startTime, 2);

            if ($response->successful()) {
                return redirect()->route('schedules.gantt')->with('success', 'Plans generated successfully. Processed in ' . $duration . ' seconds.');
            }

            return redirect()->route('products.index')->withErrors('Failed to generate plans.');
        } catch (\Throwable $e) {
            return redirect()->route('products.index')->withErrors('Error: ' . $e->getMessage());
        }
    }

    public function process($id)
    {
        $product_id = $id;

        $product = Product::findOrFail($product_id);
        $componentProducts = ComponentProduct::where('product_id', $product_id)->get();

        // dd($componentProducts);

        $operations = Operations::with(['process', 'machine'])->where('is_setting', false)->get();
        $settings = Operations::with(['process', 'machine'])->where('is_setting', true)->get();

        $processProduct = ProcessProduct::where('product_id', $product_id)->get();

        return view('products.process', compact('operations', 'settings', 'product_id', 'processProduct', 'componentProducts', 'product'));
    }

    public function inputProcess(Request $request, $id)
    {
        $validated = $request->validate([
            'steps' => 'required|array',
            'steps.*.type' => 'required|string|in:operation,setting',
            'steps.*.operation_id' => 'nullable|integer',
            'steps.*.setting_id' => 'nullable|integer',
        ]);

        foreach ($validated['steps'] as $step) {
            ProcessProduct::updateOrCreate(
                [
                    'product_id' => $id,
                    'type' => $step['type'],
                    'operation_id' => $step['operation_id'] ?? $step['setting_id'],
                ],
                [
                    'product_id' => $id,
                    'type' => $step['type'],
                    'operation_id' => $step['operation_id'] ?? $step['setting_id'],
                ]
            );
        }

        return redirect()->route('products.index')->with('success', 'Process steps saved successfully.');
    }

    public function deleteProcess($process_id)
    {
        $process = ProcessProduct::find($process_id);

        if ($process) {
            $process->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    // public function generatePlans(Request $request)
    // {
    //     $startTime = microtime(true);

    //     // CONFIG
    //     $speeds = [8000, 4000, 2000, 2000, 4000];
    //     $quantity = 8000;
    //     $gapBetweenProcesses = 10; // 30 menit antar proses
    //     $shiftStep = 15;           // kalau bentrok, geser mundur 15 menit
    //     $maxTries = 200;

    //     // Ambil akhir jadwal per mesin
    //     $rawSchedules = Schedule::select('machine_id', DB::raw('MAX(end_time) as latest_end'))
    //         ->groupBy('machine_id')
    //         ->get();

    //     $machineAvailableAt = [];
    //     foreach ($rawSchedules as $row) {
    //         $machineAvailableAt[$row->machine_id] = Carbon::parse($row->latest_end);
    //     }
    //     foreach (range(1, 5) as $machineId) {
    //         if (!isset($machineAvailableAt[$machineId])) {
    //             $machineAvailableAt[$machineId] = Carbon::now();
    //         }
    //     }

    //     // Ambil semua produk
    //     $products = Product::orderBy('shipping_date')->get();

    //     foreach ($products as $product) {

    //         $shipmentDeadline = Carbon::parse($product->shipping_date)->subMinutes(30); // Kurangi 30 menit sebagai buffer

    //         // 1️⃣ HITUNG DURASI MASING2 PROSES
    //         $durations = [];
    //         for ($i = 1; $i <= 5; $i++) {
    //             $planSpeed = $speeds[$i - 1];
    //             $durations[$i] = ($planSpeed / $quantity) * 60;
    //         }

    //         // echo "🔄 Product {$product->id} - Shipment Date: {$shipmentDeadline->toDateTimeString()}<br>";

    //         // dd($durations);

    //         // 2️⃣ MULAI PLANNING DARI SHIPMENT DATE
    //         $endTimes = [];
    //         $startTimes = [];

    //         $endTimes[5] = $shipmentDeadline->copy();
    //         $startTimes[5] = $endTimes[5]->copy()->subMinutes($durations[5]);

    //         for ($i = 4; $i >= 1; $i--) {
    //             $endTimes[$i] = $startTimes[$i + 1]->copy()->subMinutes($gapBetweenProcesses);
    //             $startTimes[$i] = $endTimes[$i]->copy()->subMinutes($durations[$i]);
    //         }

    //         // 4️⃣ SIMPAN KE DATABASE
    //         $lastSchedulePerProcess = [];
    //         $prevScheduleId = null;

    //         for ($i = 1; $i <= 5; $i++) {
    //             $planSpeed = $speeds[$i - 1];
    //             $conversion = $planSpeed / $quantity;
    //             $duration = $durations[$i];

    //             $schedule = Schedule::create([
    //                 'product_id' => $product->id,
    //                 'process_id' => $i,
    //                 'machine_id' => $i,
    //                 'previous_schedule_id' => $prevScheduleId,
    //                 'process_dependency_id' => $lastSchedulePerProcess[$i] ?? null,
    //                 'is_start_process' => $i == 1,
    //                 'is_final_process' => $i == 5,
    //                 'quantity' => $quantity,
    //                 'plan_speed' => $planSpeed,
    //                 'conversion_value' => $conversion,
    //                 'plan_duration' => $duration,
    //                 'start_time' => $startTimes[$i],
    //                 'end_time' => $endTimes[$i],
    //             ]);

    //             $prevScheduleId = $schedule->id;
    //             $machineAvailableAt[$i] = $endTimes[$i]->copy();
    //             $lastSchedulePerProcess[$i] = $schedule->id;
    //         }

    //         // echo "✅ Product {$product->id} berhasil dijadwalkan\n";
    //     }

    //     // echo "✅ Semua produk berhasil dijadwalkan.\n";

    //     $duration = round(microtime(true) - $startTime, 2);

    //     return redirect()->route('products.index')->with('success', 'All products have been scheduled successfully. Processed in ' . $duration . ' seconds.');
    // }


    // public function generatePlans()
    // {
    //     $products = Product::orderBy('shipment_date')->get();

    //     // dd($products);

    //     $speeds = [8000, 4000, 2000, 2000, 4000];
    //     $quantity = 8000;
    //     $gapBetweenProcesses = 20;
    //     $gapBetweenDependencies = 20;

    //     // Ambil akhir jadwal per mesin
    //     $rawSchedules = Schedule::select('machine_id', DB::raw('MAX(end_time) as latest_end'))
    //         ->groupBy('machine_id')
    //         ->get();

    //         // dd($rawSchedules);

    //     $machineAvailableAt = [];
    //     foreach ($rawSchedules as $row) {
    //         $machineAvailableAt[$row->machine_id] = Carbon::parse($row->latest_end);
    //     }

    //     foreach (range(1, 5) as $machineId) {
    //         if (!isset($machineAvailableAt[$machineId])) {
    //             $machineAvailableAt[$machineId] = Carbon::now();
    //         }
    //     }

    //     foreach ($products as $product) {
    //         $lastSchedulePerProcess = [];
    //         $prevScheduleId = null;
    //         $prevEndTime = Carbon::now();

    //         for ($i = 1; $i <= 5; $i++) {
    //             $planSpeed = $speeds[$i - 1];
    //             $conversion = $planSpeed / $quantity;
    //             $duration = $conversion * 60; // in minutes

    //             $machineId = $i;
    //             $machineReady = $machineAvailableAt[$machineId]->copy();

    //             // Cek dependency
    //             $lastDependencyScheduleId = $lastSchedulePerProcess[$i] ?? null;
    //             $dependencyEndTime = null;
    //             if ($lastDependencyScheduleId) {
    //                 $dependencySchedule = Schedule::find($lastDependencyScheduleId);
    //                 if ($dependencySchedule) {
    //                     $dependencyEndTime = Carbon::parse($dependencySchedule->end_time)->addMinutes($gapBetweenDependencies);
    //                 }
    //             }

    //             // Hitung earliest possible start
    //             $startCandidates = [$machineReady];
    //             if ($i == 1) {
    //                 $startCandidates[] = $prevEndTime;
    //             } else {
    //                 $startCandidates[] = $prevEndTime->copy()->addMinutes($gapBetweenProcesses);
    //             }
    //             if ($dependencyEndTime) {
    //                 $startCandidates[] = $dependencyEndTime;
    //             }

    //             $start = collect($startCandidates)->max();
    //             $end = $start->copy()->addMinutes($duration);

    //             // Tambahan validasi shipment date untuk proses terakhir
    //             if ($i == 5 && $end->greaterThan(Carbon::parse($product->shipment_date))) {
    //                 throw new \Exception("Tidak bisa jadwalkan product {$product->id} - end_time melebihi shipment_date");
    //             }

    //             // Cek bentrok dengan schedule lain di mesin
    //             $overlap = Schedule::where('machine_id', $machineId)
    //                 ->where(function ($query) use ($start, $end) {
    //                     $query->whereBetween('start_time', [$start, $end])
    //                         ->orWhereBetween('end_time', [$start, $end])
    //                         ->orWhere(function ($q) use ($start, $end) {
    //                             $q->where('start_time', '<=', $start)
    //                                 ->where('end_time', '>=', $end);
    //                         });
    //                 })->exists();

    //             if ($overlap) {
    //                 throw new \Exception("Schedule conflict detected on Machine $machineId between $start and $end.");
    //             }

    //             // Simpan schedule
    //             $schedule = Schedule::create([
    //                 'product_id' => $product->id,
    //                 'process_id' => $i,
    //                 'machine_id' => $machineId,
    //                 'previous_schedule_id' => $prevScheduleId,
    //                 'process_dependency_id' => $lastDependencyScheduleId,
    //                 'is_start_process' => $i == 1,
    //                 'is_final_process' => $i == 5,
    //                 'quantity' => $quantity,
    //                 'plan_speed' => $planSpeed,
    //                 'conversion_value' => $conversion,
    //                 'plan_duration' => $duration,
    //                 'start_time' => $start,
    //                 'end_time' => $end,
    //             ]);

    //             $prevScheduleId = $schedule->id;
    //             $prevEndTime = $end->copy();
    //             $machineAvailableAt[$machineId] = $end->copy();
    //             $lastSchedulePerProcess[$i] = $schedule->id;
    //         }
    //     }
    // }


}
