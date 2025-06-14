<?php

namespace App\Services\ScheduleGraph;

use Carbon\Carbon;
use App\Models\Schedule;

class ScheduleNode
{
    public int $id;
    public ?int $product_id;
    public ?int $previous_schedule_id;
    public ?int $process_dependency_id;
    public ?Carbon $start_time;
    public ?Carbon $end_time;
    public ?int $process_id; // ID proses yang terkait, jika ada
    public array $children = [];
    // public int $duration;
    public bool $is_final_process;
    public bool $is_start_process; // penting!
    public Schedule $originalModel;

    /** @var ScheduleNode[] */
    public array $prev_children = [];

    /** @var ScheduleNode[] */
    public array $dependency_children = [];

    public function __construct(Schedule $schedule)
    {
        $this->originalModel = $schedule;

        $this->id = $schedule->id;
        $this->product_id = $schedule->product_id;
        $this->previous_schedule_id = $schedule->previous_schedule_id;
        $this->process_dependency_id = $schedule->process_dependency_id;
        $this->start_time = $schedule->start_time ? Carbon::parse($schedule->start_time) : null;
        $this->end_time = $schedule->end_time ? Carbon::parse($schedule->end_time) : null;
        $this->process_id = $schedule->process_id; // ID proses yang terkait, jika ada
        $this->is_final_process = $schedule->is_final_process; // penting!
        $this->is_start_process = $schedule->is_start_process; // penting!
    }

    public function applyDelay(Carbon $newStart): void
    {
        if ($this->start_time < $newStart) {
            $durationInMinutes = $this->start_time->diffInMinutes($this->end_time);
            $this->start_time = $newStart;
            $this->end_time = $newStart->copy()->addMinutes($durationInMinutes);
        }
    }


    public function save()
    {
        $this->originalModel->start_time = $this->start_time;
        $this->originalModel->end_time = $this->end_time;
        $this->originalModel->save();
    }
}

