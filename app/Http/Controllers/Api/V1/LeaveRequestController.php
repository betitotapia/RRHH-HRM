<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function options(Request $request, LeaveRequestService $service)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 422, 'Usuario sin empleado asociado.');
        $employee->load('supervisor.user');
        $administrativeUsed = $service->administrativePermissionsUsed($employee);
        return response()->json([
            'vacation_days_available' => $employee->vacation_days_available,
            'minimum_vacation_start_date' => $service->minimumVacationStartDate()->toDateString(),
            'supervisor' => $employee->supervisor ? ['id' => $employee->supervisor->id, 'name' => trim($employee->supervisor->first_names.' '.$employee->supervisor->last_name_1), 'can_approve' => $employee->supervisor->user && in_array($employee->supervisor->user->role, ['super_admin','admin','hr','supervisor'], true)] : null,
            'request_types' => ['vacation', 'administrative_permission'],
            'approval_routes' => ['hr', 'supervisor'],
            'administrative_permissions' => ['monthly_limit' => 3, 'used' => $administrativeUsed, 'remaining' => max(0, 3 - $administrativeUsed), 'duration_minutes' => 120, 'period' => now()->format('Y-m')],
        ]);
    }

    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 422, 'Usuario sin empleado asociado.');
        return $employee->leaveRequests()->with(['assignedApprover', 'decisionMaker'])->latest()->paginate(min((int) $request->input('per_page', 20), 100));
    }

    public function store(Request $request, LeaveRequestService $service)
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $employee->active, 422, 'Empleado inactivo o no asociado.');
        $data = $request->validate([
            'request_type' => 'required|in:vacation,administrative_permission',
            'start_date' => 'nullable|required_if:request_type,vacation|date',
            'end_date' => 'nullable|required_if:request_type,vacation|date|after_or_equal:start_date',
            'permission_date' => 'nullable|required_if:request_type,administrative_permission|date',
            'permission_start_time' => 'nullable|required_if:request_type,administrative_permission|date_format:H:i',
            'approval_route' => 'required|in:hr,supervisor',
            'observations' => 'nullable|string|max:3000',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);
        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('leave-request-evidence/'.now()->format('Y/m'), 'local');
            $data['evidence_path'] = $evidencePath;
            $data['evidence_original_name'] = $request->file('evidence')->getClientOriginalName();
        }
        try {
            $leaveRequest = $service->create($employee, $data);
        } catch (\Throwable $exception) {
            if ($evidencePath) Storage::disk('local')->delete($evidencePath);
            throw $exception;
        }
        return response()->json($leaveRequest->load(['employee.supervisor', 'assignedApprover']), 201);
    }
}
