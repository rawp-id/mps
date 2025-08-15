<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assembly extends Model
{
    protected $guarded = ['id'];

    public function processProducts()
    {
        return $this->hasMany(ProcessProduct::class);
    }
}
