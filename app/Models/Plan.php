<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    //
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function schedules()
    {
        return $this->hasMany(SimulateSchedule::class, 'plan_id');
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
