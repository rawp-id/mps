<?php

namespace App\Services\ScheduleGraph;

use Carbon\Carbon;

class ScheduleNode
{
    public int $id;
    public ?int $product_id;
    public ?int $previous_schedule_id;
    public ?int $process_dependency_id;
    public ?Carbon $start_time;
    public ?Carbon $end_time;
    public array $children = [];

    public function __construct($schedule)
    {
        $this->id = $schedule->id;
        $this->product_id = $schedule->product_id;
        $this->previous_schedule_id = $schedule->previous_schedule_id;
        $this->process_dependency_id = $schedule->process_dependency_id;
        $this->start_time = $schedule->start_time ? Carbon::parse($schedule->start_time) : null;
        $this->end_time = $schedule->end_time ? Carbon::parse($schedule->end_time) : null;
    }

    public function durationInMinutes(): int
    {
        if ($this->start_time && $this->end_time) {
            return $this->end_time->diffInMinutes($this->start_time);
        }

        return 0;
    }

    public function applyDelay(Carbon $newStart)
    {
        $this->start_time = $newStart;
        $this->end_time = $newStart->copy()->addMinutes($this->durationInMinutes());
    }
}
