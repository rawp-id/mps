<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $guarded = ['id'];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
