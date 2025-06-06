<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
