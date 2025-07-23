<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $guarded = ['id'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
    public function operations()
    {
        return $this->hasMany(Operations::class);
    }
}
