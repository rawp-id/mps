<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = ['id'];

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }
}
