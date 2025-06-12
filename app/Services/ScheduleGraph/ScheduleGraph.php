<?php
namespace App\Services\ScheduleGraph;

use App\Models\Schedule;
use Illuminate\Support\Collection;

class ScheduleGraph
{
    /** @var array<int, ScheduleNode> */
    public array $nodes = [];

    public function __construct(Collection $schedules)
    {
        // Bangun node
        foreach ($schedules as $schedule) {
            $this->nodes[$schedule->id] = new ScheduleNode($schedule);
        }

        // Bangun edge
        foreach ($this->nodes as $node) {
            foreach ($this->nodes as $potentialChild) {
                if (
                    $potentialChild->previous_schedule_id === $node->id ||
                    $potentialChild->process_dependency_id === $node->id
                ) {
                    $node->children[] = $potentialChild;
                }
            }
        }
    }

    public function propagateDelay(int $startId, int $delayMinutes)
    {
        $visited = [];

        $this->dfsDelay($this->nodes[$startId], $delayMinutes, $visited);
    }

    private function dfsDelay(ScheduleNode $node, int $delayMinutes, array &$visited)
    {
        if (isset($visited[$node->id]))
            return;
        $visited[$node->id] = true;

        $node->applyDelay($node->start_time->copy()->addMinutes($delayMinutes));

        foreach ($node->children as $child) {
            // Atur waktu anak mengikuti waktu selesai node sekarang
            $child->applyDelay($node->end_time->copy());
            $this->dfsDelay($child, 0, $visited); // tidak delay tambahan, karena sudah diatur
        }
    }

    public function getUpdatedSchedules(): array
    {
        return $this->nodes;
    }
}
