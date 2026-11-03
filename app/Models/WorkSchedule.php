<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = ['name','code','description','has_breakfast','has_lunch','active'];
    protected $casts = ['active'=>'boolean','has_breakfast'=>'boolean','has_lunch'=>'boolean'];
    public function days(){ return $this->hasMany(WorkScheduleDay::class)->orderBy('day_of_week'); }
    public function employees(){ return $this->hasMany(Employee::class); }
}
