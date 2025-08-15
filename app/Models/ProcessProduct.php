<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessProduct extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function componentProduct()
    {
        return $this->belongsTo(ComponentProduct::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operations::class);
    }

    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }
}
