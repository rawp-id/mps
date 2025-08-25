<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BOM extends Model
{
    protected $guarded = ['id'];

    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
