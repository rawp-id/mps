<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'machine_id',
        'name',
        'start_time',
        'end_time',
        'is_active',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
