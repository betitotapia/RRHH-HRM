<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
    'employee_id',
    'device_uid',
    'device_sn',
    'type',
    'registered_at',
    'source',
    'photo_path',
    'device_ip',
    'ip_address',
    'user_agent',
    'event_type',
    'work_location_id',
    'work_mode',
    'latitude',
    'longitude',
    'distance_meters',
    'in_geofence',
    'verification_method',
    'station_nonce',
    'punctuality',
    'late_minutes',
    'break_duration_minutes',
    'break_limit_minutes',
    'break_excess_minutes',
    'break_compliance',
    'overtime_minutes',
    'overtime_reason',
    'notes',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'in_geofence' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function workLocation()
    {
        return $this->belongsTo(WorkLocation::class);
    }
}
