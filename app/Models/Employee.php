<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Departament;
use App\Models\Positions;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'departament_id',
        'position_id',
        'employee_number',
        'last_name_1',
        'last_name_2',
        'first_names',
        'site',
        'hire_date',
        'imss_date',
        'net_salary',
        'gender',
        'disability',
        'birth_date',
        'age',
        'phone',
        'email',
        'curp',
        'rfc',
        'nss',
        'marital_status',
        'blood_type',
        'street',
        'ext_number',
        'int_number',
        'neighborhood',
        'city',
        'postal_code',
        'state',
        'emergency_contact_name_1',
        'emergency_contact_phone_1',
        'emergency_contact_name_2',
        'emergency_contact_phone_2',
        'active',
        'photo',
        'work_mode',
        'work_location_id',
        'work_schedule_id',
        'supervisor_id',
        'vacation_days_available',
    ];

    public function departament()
    {
        // clave foránea departament_id
        return $this->belongsTo(Departament::class, 'departament_id');
    }

    public function positions()
    {
        return $this->belongsTo(Positions::class, 'position_id');
    }
public function getBirthDateFormattedAttribute()
{
    return $this->birth_date
        ? Carbon::parse($this->birth_date)->format('d-m-Y')
        : null;
}
    public function getHireDateFormattedAttribute()
    {
        return $this->hire_date
            ? Carbon::parse($this->hire_date)->format('d-m-Y')
            : null;
    }
public function getImssDateFormattedAttribute()
    {
        return $this->imss_date
            ? Carbon::parse($this->imss_date)->format('d-m-Y')
            : null;
    }

public function commissions()
{
    return $this->belongsToMany(Commission::class, 'commission_employee')
                ->withPivot('role')
                ->withTimestamps();
}
public function attendanceRecords()
{
    return $this->hasMany(\App\Models\AttendanceRecord::class);
}

public function user()
{
    return $this->hasOne(\App\Models\User::class);
}

public function workSchedule(){ return $this->belongsTo(WorkSchedule::class); }

public function workLocation()
{
    return $this->belongsTo(WorkLocation::class);
}

public function attendanceIncidents()
{
    return $this->hasMany(AttendanceIncident::class);
}

public function termination()
{
    return $this->hasOne(EmployeeTermination::class);
}

public function supervisor() { return $this->belongsTo(Employee::class, 'supervisor_id'); }
public function directReports() { return $this->hasMany(Employee::class, 'supervisor_id'); }
public function leaveRequests() { return $this->hasMany(LeaveRequest::class); }



}
