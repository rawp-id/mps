<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $guarded = ['id'];

    public function boms()
    {
        return $this->hasMany(BOM::class, 'component_id');
    }
}
