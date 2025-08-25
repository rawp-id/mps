<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupingProccess extends Model
{
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function processProduct()
    {
        return $this->belongsTo(ProcessProduct::class);
    }
}
