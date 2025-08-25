<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $guarded = ['id'];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function operations()
    {
        return $this->hasMany(Operations::class);
    }
    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }
    public function downtimes()
    {
        return $this->hasMany(Downtime::class);
    }
}
