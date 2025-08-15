<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComponentProduct extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function processProducts()
    {
        return $this->hasMany(ProcessProduct::class);
    }
}
