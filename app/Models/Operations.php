<?php

namespace App\Models;

use App\Models\Machine;
use App\Models\Process;
use Illuminate\Database\Eloquent\Model;

class Operations extends Model
{
    protected $guarded = [];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
