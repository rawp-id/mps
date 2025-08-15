<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssemblyProcess extends Model
{
    protected $guarded = ['id'];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function processProduct()
    {
        return $this->belongsTo(ProcessProduct::class);
    }
}
