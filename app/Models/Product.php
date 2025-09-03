<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function componentProduct()
    {
        return $this->hasMany(ComponentProduct::class);
    }

    public function processProducts()
    {
        return $this->hasMany(ProcessProduct::class);
    }

    public function boms()
    {
        return $this->hasMany(BOM::class, 'product_id');
    }

    public function coProducts()
    {
        return $this->hasMany(CoProduct::class);
    }
}
