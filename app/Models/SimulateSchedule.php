<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulateSchedule extends Model
{
    //
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function process()
    {
        return $this->belongsTo(Process::class);
    }
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
    public function previousSchedule()
    {
        return $this->belongsTo(SimulateSchedule::class, 'previous_schedule_id');
    }
    public function processDependency()
    {
        return $this->belongsTo(SimulateSchedule::class, 'process_dependency_id');
    }
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operations::class);
    }
}
