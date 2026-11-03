<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTermination extends Model
{
    protected $fillable = [
        'employee_id', 'termination_type_id', 'termination_date', 'last_working_day',
        'notes', 'document_path', 'document_original_name', 'created_by',
    ];

    protected $casts = ['termination_date' => 'date', 'last_working_day' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function type() { return $this->belongsTo(TerminationType::class, 'termination_type_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
