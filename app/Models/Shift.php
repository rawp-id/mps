<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $guarded = ['id'];
    public function operation()
    {
        return $this->belongsTo(Operations::class);
    }
}
