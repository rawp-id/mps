<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Co extends Model
{
    protected $guarded = ['id'];

    public function planProductCos()
    {
        return $this->hasMany(PlanProductCo::class);
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_product_cos');
    }

    public function coProducts()
    {
        return $this->hasMany(CoProduct::class);
    }
}
