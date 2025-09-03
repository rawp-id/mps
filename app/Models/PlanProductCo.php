<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanProductCo extends Model
{
    protected $guarded = ['id'];

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }

    public function coProduct()
    {
        return $this->belongsTo('App\Models\CoProduct');
    }

    public function co()
    {
        return $this->belongsTo('App\Models\Co');
    }
}
