<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $hidden = ['evidence_path'];
    protected $appends = ['has_evidence'];
    protected $fillable = [
        'employee_id', 'request_type', 'start_date', 'end_date', 'requested_start_at', 'requested_end_at', 'requested_business_days',
        'return_date', 'approval_route', 'assigned_approver_id', 'observations', 'status',
        'evidence_path', 'evidence_original_name', 'decided_by', 'decided_at', 'decision_notes',
    ];

    protected $casts = [
        'start_date' => 'date', 'end_date' => 'date', 'requested_start_at' => 'datetime', 'requested_end_at' => 'datetime',
        'return_date' => 'date', 'decided_at' => 'datetime',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function assignedApprover() { return $this->belongsTo(User::class, 'assigned_approver_id'); }
    public function decisionMaker() { return $this->belongsTo(User::class, 'decided_by'); }
    public function getHasEvidenceAttribute(): bool { return ! empty($this->evidence_path); }
}
