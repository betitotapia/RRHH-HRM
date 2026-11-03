<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceIncident extends Model
{
    protected $fillable = [
        'employee_id', 'created_by', 'type', 'starts_at', 'ends_at',
        'notes', 'document_path', 'status', 'approved_by', 'approved_at',
    ];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'approved_at' => 'datetime'];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
