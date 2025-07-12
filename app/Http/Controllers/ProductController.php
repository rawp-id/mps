<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Machine;
use App\Models\Process;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:products,code',
            'name' => 'required',
            'shipping_date' => 'nullable|date',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
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
            ]);
        }

        $duration = round(microtime(true) - $startTime, 2);

        session()->forget(['import_products_data', 'import_products_headers']);

        return redirect()->route('products.index')->with('success', "Products imported successfully. (Processed in {$duration} seconds)");
    }

    public function generatePlans()
    {
        $startTime = microtime(true);

        // CONFIG
        $speeds = [8000, 4000, 2000, 2000, 4000];
        $quantity = 8000;
        $gapBetweenProcesses = 10; // 30 menit antar proses
        $shiftStep = 15;           // kalau bentrok, geser mundur 15 menit
        $maxTries = 200;

        // Ambil akhir jadwal per mesin
        $rawSchedules = Schedule::select('machine_id', DB::raw('MAX(end_time) as latest_end'))
            ->groupBy('machine_id')
            ->get();

        $machineAvailableAt = [];
        foreach ($rawSchedules as $row) {
            $machineAvailableAt[$row->machine_id] = Carbon::parse($row->latest_end);
        }
        foreach (range(1, 5) as $machineId) {
            if (!isset($machineAvailableAt[$machineId])) {
                $machineAvailableAt[$machineId] = Carbon::now();
            }
        }

        // Ambil semua produk
        $products = Product::orderBy('shipping_date')->get();

        foreach ($products as $product) {

            $shipmentDeadline = Carbon::parse($product->shipping_date)->subMinutes(30); // Kurangi 30 menit sebagai buffer

            // 1️⃣ HITUNG DURASI MASING2 PROSES
            $durations = [];
            for ($i = 1; $i <= 5; $i++) {
                $planSpeed = $speeds[$i - 1];
                $durations[$i] = ($planSpeed / $quantity) * 60;
            }

            // echo "🔄 Product {$product->id} - Shipment Date: {$shipmentDeadline->toDateTimeString()}<br>";

            // dd($durations);

            // 2️⃣ MULAI PLANNING DARI SHIPMENT DATE
            $endTimes = [];
            $startTimes = [];

            $endTimes[5] = $shipmentDeadline->copy();
            $startTimes[5] = $endTimes[5]->copy()->subMinutes($durations[5]);

            for ($i = 4; $i >= 1; $i--) {
                $endTimes[$i] = $startTimes[$i + 1]->copy()->subMinutes($gapBetweenProcesses);
                $startTimes[$i] = $endTimes[$i]->copy()->subMinutes($durations[$i]);
            }

            // 4️⃣ SIMPAN KE DATABASE
            $lastSchedulePerProcess = [];
            $prevScheduleId = null;

            for ($i = 1; $i <= 5; $i++) {
                $planSpeed = $speeds[$i - 1];
                $conversion = $planSpeed / $quantity;
                $duration = $durations[$i];

                $schedule = Schedule::create([
                    'product_id' => $product->id,
                    'process_id' => $i,
                    'machine_id' => $i,
                    'previous_schedule_id' => $prevScheduleId,
                    'process_dependency_id' => $lastSchedulePerProcess[$i] ?? null,
                    'is_start_process' => $i == 1,
                    'is_final_process' => $i == 5,
                    'quantity' => $quantity,
                    'plan_speed' => $planSpeed,
                    'conversion_value' => $conversion,
                    'plan_duration' => $duration,
                    'start_time' => $startTimes[$i],
                    'end_time' => $endTimes[$i],
                ]);

                $prevScheduleId = $schedule->id;
                $machineAvailableAt[$i] = $endTimes[$i]->copy();
                $lastSchedulePerProcess[$i] = $schedule->id;
            }

            // echo "✅ Product {$product->id} berhasil dijadwalkan\n";
        }

        // echo "✅ Semua produk berhasil dijadwalkan.\n";

        $duration = round(microtime(true) - $startTime, 2);

        return redirect()->route('products.index')->with('success', 'All products have been scheduled successfully. Processed in ' . $duration . ' seconds.');
    }


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
