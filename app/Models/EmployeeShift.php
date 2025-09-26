<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    protected $guarded = ['id'];
    protected $table = 'employee_shift';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
