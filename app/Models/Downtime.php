<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Downtime extends Model
{
    protected $guarded = ['id'];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
