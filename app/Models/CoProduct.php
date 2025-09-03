<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoProduct extends Model
{
    protected $guarded = ['id'];

    public function co()
    {
        return $this->belongsTo(Co::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function simulateSchedules()
    {
        return $this->hasMany(SimulateSchedule::class);
    }
}
