<?php

namespace App\Models;

use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    //
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        Route::model('plan', self::class); // <-- ini kuncinya
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function co()
    {
        return $this->belongsTo(Co::class);
    }
    public function schedules()
    {
        return $this->hasMany(SimulateSchedule::class, 'plan_id');
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
    public function planProductCos()
    {
        return $this->hasMany(PlanProductCo::class);
    }
    public function cos()
    {
        return $this->belongsToMany(Co::class, 'plan_product_cos');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'plan_product_cos');
    }
}