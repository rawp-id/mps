<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
    public function process()
    {
        return $this->belongsTo(Process::class);
    }
    public function operation()
    {
        return $this->belongsTo(Operations::class);
    }
}
