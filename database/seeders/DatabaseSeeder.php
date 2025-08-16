<?php

namespace Database\Seeders;

use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Process;
use App\Models\Product;
use App\Models\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $machines = [
            ['name' => 'Cutting Machine Telson MP680H ST', 'capacity' => 10000],
            ['name' => 'Machine for Cutting ITOH', 'capacity' => 10000],
            ['name' => 'Machine for Cutting KYODO', 'capacity' => 10000],
            ['name' => 'Printing Machine KOMORI GL440', 'capacity' => 10000],
            ['name' => 'Laminator Machine Fengchi GW-2200L', 'capacity' => 3500],
            ['name' => 'CADS-1050 Automatic Die Cutting & Creasing Machine', 'capacity' => 4000],
            ['name' => 'Manual Packing process', 'capacity' => 1000],
            ['name' => 'Glueing Machine', 'capacity' => 2000],
            ['name' => 'Machine for Shrink Wrapping', 'capacity' => 2000],
            ['name' => 'Manual Cutting process', 'capacity' => 1000],
            ['name' => 'Manual Printing process', 'capacity' => 1000],
            ['name' => 'Manual Die-cutting process', 'capacity' => 1000],
            ['name' => 'Manual Glueing process', 'capacity' => 1000],
            ['name' => 'Manual Packing process', 'capacity' => 1000],
            ['name' => 'Manual Counting process', 'capacity' => 1000],
        ];

        $machineIds = [];
        foreach ($machines as $machine) {
            $m = Machine::create($machine);
            $machineIds[] = $m->id;
        }

        $proccesses = [
            [
                'code' => 'P01',
                'name' => 'Cutting',
                'speed' => 20000,
            ],
            [
                'code' => 'P02',
                'name' => 'Printing',
                'speed' => 10000,
            ],
            [
                'code' => 'P03',
                'name' => 'Die-cut',
                'speed' => 3200,
            ],
            [
                'code' => 'P04',
                'name' => 'Glueing',
                'speed' => 4000,
            ],
            [
                'code' => 'P05',
                'name' => 'Packing',
                'speed' => 1800,
            ],
            [
                'code' => 'P06',
                'name' => 'Counting',
                'speed' => 1000,
            ],
            [
                'code' => 'P08',
                'name' => 'Finishing',
                'speed' => 1000,
            ],
            [
                'code' => 'P09',
                'name' => 'Shrink Wrapping',
                'speed' => 1800,
            ],
        ];

        $proccesses_ids = [];
        foreach ($proccesses as $proccess) {
            $p = Process::create($proccess);
            $proccesses_ids[] = $p->id;
        }

        $operations = [
            [
                'code' => 'P01',
                'name' => 'Cutting Machine Telson MP680H ST',
                'duration' => 60,
                'machine_id' => $machineIds[0],
                'process_id' => $proccesses_ids[0],
            ],
            [
                'code' => 'P02',
                'name' => 'Machine for Cutting ITOH',
                'duration' => 120,
                'machine_id' => $machineIds[1],
                'process_id' => $proccesses_ids[0],
            ],
            [
                'code' => 'P03',
                'name' => 'Machine for Cutting KYODO',
                'duration' => 60,
                'machine_id' => $machineIds[2],
                'process_id' => $proccesses_ids[0],
            ],
            [
                'code' => 'P04',
                'name' => 'Printing Machine KOMORI GL440',
                'duration' => 120,
                'machine_id' => $machineIds[3],
                'process_id' => $proccesses_ids[1],
            ],
            [
                'code' => 'P05',
                'name' => 'Laminator Machine Fengchi GW-2200L',
                'duration' => 120,
                'machine_id' => $machineIds[4],
                'process_id' => $proccesses_ids[1],
            ],
            [
                'code' => 'P06',
                'name' => 'CADS-1050 Automatic Die Cutting & Creasing Machine',
                'duration' => 60,
                'machine_id' => $machineIds[5],
                'process_id' => $proccesses_ids[2],
            ],
            [
                'code' => 'P07',
                'name' => 'Manual Packing process',
                'duration' => 90,
                'machine_id' => $machineIds[6],
                'process_id' => $proccesses_ids[4],
            ],
            [
                'code' => 'P08',
                'name' => 'Glueing Machine',
                'duration' => 100,
                'machine_id' => $machineIds[7],
                'process_id' => $proccesses_ids[3],
            ],
            [
                'code' => 'P09',
                'name' => 'Machine for Shrink Wrapping',
                'duration' => 60,
                'machine_id' => $machineIds[8],
                'process_id' => $proccesses_ids[7],
            ],
        ];

        foreach ($operations as $operation) {
            \App\Models\Operations::create($operation);
        }

        $products = [
            [
                'code' => 'P001',
                'name' => 'Bungkus Roti Rasa Coklat',
                'process_details' => '1,2,3,4,5,6',
                // 'shipping_date' => now()->addHours(2),
            ],
            [
                'code' => 'P002',
                'name' => 'WK KALENDER DUDUK 2017 COVER BALIK KERTAS',
                // 'shipping_date' => now()->addHours(4),
            ],
            [
                'code' => 'P003',
                'name' => 'WK KALENDER DUDUK 2017 COVER DEPAN KERTAS',
                // 'shipping_date' => now()->addHours(6),
            ],
            [
                'code' => 'P004',
                'name' => 'Kardus Kado 2017',
                'process_details' => '1,2,3,4,5,6',
                // 'shipping_date' => now()->addHours(8),
            ],
            [
                'code' => 'P005',
                'name' => 'Tutup Kardus Kado 2017',
                // 'shipping_date' => now()->addHours(10),
            ],
            [
                'code' => 'P006',
                'name' => 'Body Kardus Kado 2017',
                // 'shipping_date' => now()->addHours(12),
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $product_components = [
            [
                'parent_product_id' => 4,
                'component_product_id' => 5,
                'quantity' => 1.00,
                'unit' => 'pcs',
            ],
            [
                'parent_product_id' => 4,
                'component_product_id' => 6,
                'quantity' => 1.00,
                'unit' => 'pcs',
            ],
        ];

        foreach ($product_components as $pc) {
            \App\Models\ProductComponent::create($pc);
        }

        $component_products = [
            [
                'product_id' => 4,
                'code' => 'CP001',
                'name' => 'Kardus Kado 2017 - Tutup',
            ],
            [
                'product_id' => 4,
                'code' => 'CP002',
                'name' => 'Kardus Kado 2017 - Body',
            ],
            [
                'product_id' => 4,
                'code' => 'CP003',
                'name' => 'Kardus Kado 2017 - Lateral',
            ],
            [
                'product_id' => 4,
                'code' => 'CP004',
                'name' => 'Kardus Kado 2017 - Bottom',
            ],
        ];

        foreach ($component_products as $component) {
            \App\Models\ComponentProduct::create($component);
        }

        $cos = [
            [
                'product_id' => 1,
                'code' => 'CO001',
                'name' => 'CO for Product 1',
                'description' => 'CO description for Product 1',
                'co_user' => 'user1',
                'shipping_date' => now()->addDays(1),
                'process_details' => 'Process details for Product 1',
                'is_completed' => false,
                'status' => 'pending',
                'remarks' => null,
                'draft' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 4,
                'code' => 'CO002',
                'name' => 'CO for Product 4',
                'description' => 'CO description for Product 4',
                'co_user' => 'user2',
                'shipping_date' => now()->addDays(2),
                'process_details' => 'Process details for Product 4',
                'is_completed' => false,
                'status' => 'pending',
                'remarks' => null,
                'draft' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($cos as $co) {
            \App\Models\Co::create($co);
        }
        
        Plan::create([
            'name' => 'Plan for Product 1',
            'description' => 'Plan description for Product 1',
            'is_applied' => false,
            'start_date' => now(),
        ]);

        // $products = [];
        // for ($i = 1; $i <= 100; $i++) {
        //     $products[] = Product::create([
        //         'code' => sprintf('P%03d', $i),
        //         'name' => 'WK KALENDER DUDUK 2017 COVER BALIK KERTAS ' . $i,
        //         'shipping_date' => now()->addHours(2 + ($i - 1) * 0.5),
        //     ]);
        // }

        // $baseTime = Carbon::create(2025, 5, 7, 8, 0, 0);
        // $speeds = [8000, 4000, 2000, 2000, 4000];
        // $quantity = 8000;
        // $gapBetweenProcesses = 20; // menit
        // $gapBetweenDependencies = 20; // menit antar dependency

        // $machineAvailableAt = collect(range(1, 5))->mapWithKeys(fn($id) => [$id => $baseTime->copy()])->toArray();

        // $lastSchedulePerProcess = []; // per process_id

        // foreach ($products as $product) {
        //     $prevScheduleId = null;
        //     $prevEndTime = $baseTime->copy(); // jadwal dimulai dari baseTime

        //     $tempSchedules = [];

        //     for ($i = 1; $i <= 5; $i++) {
        //         $planSpeed = $speeds[$i - 1];
        //         $conversion = $planSpeed / $quantity;
        //         $duration = $conversion * 60;

        //         $machineId = $i;
        //         $machineReady = $machineAvailableAt[$machineId]->copy();

        //         // Cari dependency schedule (product sebelumnya pada process yang sama)
        //         $lastDependencyScheduleId = $lastSchedulePerProcess[$i] ?? null;
        //         $dependencyEndTime = null;
        //         if ($lastDependencyScheduleId) {
        //             $dependencySchedule = Schedule::find($lastDependencyScheduleId);
        //             if ($dependencySchedule) {
        //                 $dependencyEndTime = Carbon::parse($dependencySchedule->end_time)->addMinutes($gapBetweenDependencies);
        //             }
        //         }

        //         // Hitung waktu mulai: max(prevEndTime + gap, machineReady, dependencyEndTime)
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

        //         if ($end->gt($product->shipping_date)) {
        //             echo "⚠️ CONFLICT: Product {$product->code}, process $i cannot fit before shipping date.\n";
        //             break;
        //         }

        //         $tempSchedules[] = [
        //             'product_id' => $product->id,
        //             'process_id' => $i,
        //             'machine_id' => $machineId,
        //             'previous_schedule_id' => null, // set nanti
        //             'process_dependency_id' => $lastDependencyScheduleId, // set sekarang
        //             'is_start_process' => $i == 1,
        //             'is_final_process' => $i == 5,
        //             'quantity' => $quantity,
        //             'plan_speed' => $planSpeed,
        //             'conversion_value' => $conversion,
        //             'plan_duration' => $duration,
        //             'start_time' => $start,
        //             'end_time' => $end,
        //         ];

        //         // Update waktu siap mesin dan waktu selesai proses
        //         $machineAvailableAt[$machineId] = $end->copy();
        //         $prevEndTime = $end->copy(); // proses selanjutnya mulai setelah ini
        //     }

        //     // Insert schedules
        //     foreach ($tempSchedules as $scheduleData) {
        //         $scheduleData['previous_schedule_id'] = $prevScheduleId;

        //         $schedule = Schedule::create($scheduleData);

        //         echo "Inserted Product {$schedule->product_id} Process {$schedule->process_id} → Schedule ID {$schedule->id} → Dependency: " .
        //             ($scheduleData['process_dependency_id'] ?? 'NULL') . "\n";

        //         $prevScheduleId = $schedule->id;
        //         $lastSchedulePerProcess[$scheduleData['process_id']] = $schedule->id;
        //     }
        // }
    }
}
