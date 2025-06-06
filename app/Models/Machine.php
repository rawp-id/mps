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
    public function processes()
    {
        return $this->hasMany(Process::class);
    }
}
