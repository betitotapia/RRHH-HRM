<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id','day_of_week','start_time','end_time','tolerance_minutes','breakfast_minutes','lunch_minutes','active'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
