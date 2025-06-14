<?php

namespace App\Services\ScheduleGraph;

use App\Models\Schedule;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ScheduleGraph
{
    /** @var array<int, ScheduleNode> */
    public array $nodes = [];

    public function __construct(Collection $schedules)
    {
        // Buat node
        foreach ($schedules as $schedule) {
            $this->nodes[$schedule->id] = new ScheduleNode($schedule);
        }

        // Buat edge: previous dan dependency
        foreach ($this->nodes as $node) {
            foreach ($this->nodes as $other) {
                if ($other->previous_schedule_id === $node->id) {
                    $node->prev_children[] = $other;
                }
                if ($other->process_dependency_id === $node->id) {
                    $node->dependency_children[] = $other;
                }
            }
        }
    }

    public function propagateDelay(int $startId, int $delayMinutes)
    {
        $visited = [];
        $startNode = $this->nodes[$startId];

        // Tambah delay pada node awal
        $startNode->applyDelay($startNode->start_time->copy()->addMinutes($delayMinutes));

        // Jalankan DFS
        $this->dfs($startNode, $visited);
    }

    private function dfs(ScheduleNode $node, array &$visited)
    {
        if (isset($visited[$node->id])) return;
        $visited[$node->id] = true;

        foreach ($node->prev_children as $child) {
            // Proses berikutnya dalam product yang sama
            $child->applyDelay($node->end_time->copy());
            $this->dfs($child, $visited);
        }

        if ($node->is_start_process) {
            foreach ($node->dependency_children as $dependentStartNode) {
                // Jika ini adalah proses pertama pada produk dependent,
                // maka mulai setelah proses final produk sebelumnya selesai
                $dependentStartNode->applyDelay($node->end_time->copy());
                $this->dfs($dependentStartNode, $visited);

                // Lanjutkan ke proses dalam produk dependent
                $this->dfsForwardWithinProduct($dependentStartNode, $visited);
            }
        }
    }

    private function dfsForwardWithinProduct(ScheduleNode $node, array &$visited)
    {
        foreach ($node->prev_children as $child) {
            // Mulai dari end time node sebelumnya
            $child->applyDelay($node->end_time->copy());
            $this->dfsForwardWithinProduct($child, $visited);
        }
    }

    public function getUpdatedSchedules(): array
    {
        return $this->nodes;
    }
}
