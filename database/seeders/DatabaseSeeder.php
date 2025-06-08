<?php

namespace Database\Seeders;

use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Machine;
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
            ['name' => 'Printing Machine KOMORI GL440', 'capacity' => 10000],
            ['name' => 'Laminator Machine Fengchi GW-2200L', 'capacity' => 3500],
            ['name' => 'CADS-1050 Automatic Die Cutting & Creasing Machine', 'capacity' => 4000],
            ['name' => 'Manual Packing process', 'capacity' => 1000],
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
                'machine_id' => $machineIds[0],
                'speed' => 20000,
            ],
            [
                'code' => 'P02',
                'name' => 'Printing',
                'machine_id' => $machineIds[1],
                'speed' => 10000,
            ],
            [
                'code' => 'P03',
                'name' => 'Die-cut',
                'machine_id' => $machineIds[3],
                'speed' => 3200,
            ],
            [
                'code' => 'P04',
                'name' => 'Glueing',
                'machine_id' => $machineIds[2],
                'speed' => 4000,
            ],
            [
                'code' => 'P05',
                'name' => 'Packing',
                'machine_id' => $machineIds[4],
                'speed' => 1800,
            ],
        ];

        foreach ($proccesses as $proccess) {
            Process::create($proccess);
        }

        $products = [
            Product::create([
                'code' => 'P001',
                'name' => 'Product 1',
                'shipping_date' => now()->addHours(2),
            ]),
            Product::create([
                'code' => 'P002',
                'name' => 'Product 2',
                'shipping_date' => now()->addHours(2.5),
            ]),
        ];

        $baseTime = Carbon::create(2025, 5, 7, 8, 0, 0);
        $speeds = [8000, 4000, 2000, 2000, 4000];
        $quantity = 8000;

        $machineAvailableAt = collect(range(1, 5))->mapWithKeys(fn($id) => [$id => $baseTime->copy()])->toArray();

        foreach ($products as $product) {
            $prevScheduleId = null;
            $prevStartTime = $product->shipping_date->copy()->subMinutes(60); // Mulai dari 1 jam sebelum shipping date

            $tempSchedules = []; // Simpan sementara di array

            for ($i = 5; $i >= 1; $i--) {
                $planSpeed = $speeds[$i - 1];
                $conversion = $planSpeed / $quantity;
                $duration = $conversion * 60; // menit

                $machineId = $i;
                $machineReady = $machineAvailableAt[$machineId]->copy();

                $end = $prevStartTime->copy();
                $start = $end->copy()->subMinutes($duration);

                // Pastikan mesin ready sebelum start
                if ($machineReady->gt($start)) {
                    $start = $machineReady->copy();
                    $end = $start->copy()->addMinutes($duration);

                    if ($end->gt($product->shipping_date)) {
                        echo "⚠️ CONFLICT: Product {$product->code}, process $i cannot fit before shipping date.\n";
                        break;
                    }
                }

                // Simpan di temp array, nanti kita urutkan proses_id ascending
                $tempSchedules[] = [
                    'product_id' => $product->id,
                    'process_id' => $i,
                    'machine_id' => $machineId,
                    'previous_schedule_id' => null, // nanti diisi saat insert
                    'quantity' => $quantity,
                    'plan_speed' => $planSpeed,
                    'conversion_value' => $conversion,
                    'plan_duration' => $duration,
                    'start_time' => $start,
                    'end_time' => $end,
                ];

                // Update machine availability → mundur
                $machineAvailableAt[$machineId] = $start->copy();

                // Update prevStartTime untuk proses sebelumnya
                $prevStartTime = $start->copy();
            }

            // Setelah semua proses selesai dihitung → sort ascending by process_id
            usort($tempSchedules, fn($a, $b) => $a['process_id'] <=> $b['process_id']);

            // Insert ke DB dengan previous_schedule_id chain
            $prevScheduleId = null;
            foreach ($tempSchedules as $scheduleData) {
                $scheduleData['previous_schedule_id'] = $prevScheduleId;

                $schedule = Schedule::create($scheduleData);

                $prevScheduleId = $schedule->id;
            }
        }

        // // Simulasi 5 mesin dan 5 proses
        // $baseTime = Carbon::create(2025, 5, 7, 8, 0, 0);
        // $speeds = [8000, 4000, 2000, 2000, 4000];
        // $quantity = 8000;

        // // Inisialisasi waktu available awal untuk semua mesin
        // $machineAvailableAt = collect(range(1, 5))->mapWithKeys(fn($id) => [$id => $baseTime->copy()])->toArray();

        // foreach ($products as $product) {
        //     $prevScheduleId = null;
        //     $prevEndTime = $baseTime->copy(); // start dari jam 8.00

        //     for ($i = 1; $i <= 5; $i++) {
        //         $planSpeed = $speeds[$i - 1];
        //         $conversion = $planSpeed / $quantity;
        //         $duration = $conversion * 60; // dalam menit

        //         $machineId = $i;
        //         $machineReady = $machineAvailableAt[$machineId]->copy();

        //         // Mulai proses setelah proses sebelumnya selesai DAN mesin ready
        //         $start = $prevEndTime->copy()->max($machineReady);
        //         $end = $start->copy()->addMinutes($duration);

        //         // ⛔️ Skip atau warning jika endTime lewat dari shipping
        //         if ($end->gt($product->shipping_date)) {
        //             echo "⚠️ SKIPPED: Product {$product->code}, process $i exceeds shipping date ({$product->shipping_date})\n";
        //             break;
        //         }

        //         $schedule = Schedule::create([
        //             'product_id' => $product->id,
        //             'process_id' => $i,
        //             'machine_id' => $machineId,
        //             'previous_schedule_id' => $prevScheduleId,
        //             'quantity' => $quantity,
        //             'plan_speed' => $planSpeed,
        //             'conversion_value' => $conversion,
        //             'plan_duration' => $duration,
        //             'start_time' => $start,
        //             'end_time' => $end,
        //         ]);

        //         // Update waktu tersedia mesin
        //         $machineAvailableAt[$machineId] = $end->copy();

        //         // Update ke proses selanjutnya
        //         $prevEndTime = $end->copy();
        //         $prevScheduleId = $schedule->id;
        //     }
        // }

    }
}
