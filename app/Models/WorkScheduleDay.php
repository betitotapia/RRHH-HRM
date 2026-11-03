<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkScheduleDay extends Model
{
    protected $fillable = ['work_schedule_id','day_of_week','start_time','end_time','tolerance_minutes','breakfast_minutes','lunch_minutes'];
    public function workSchedule(){ return $this->belongsTo(WorkSchedule::class); }
}
