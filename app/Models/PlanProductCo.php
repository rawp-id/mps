<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanProductCo extends Model
{
    protected $guarded = [];

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function co()
    {
        return $this->belongsTo('App\Models\Co');
    }
}
