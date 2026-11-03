<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LeaveRequest::with(['employee.departament', 'employee.positions', 'employee.supervisor', 'assignedApprover', 'decisionMaker']);
        if (! $user->isSuperAdmin()) {
            if ($user->role === 'supervisor') $query->where('assigned_approver_id', $user->id);
            elseif (in_array($user->role, ['admin', 'hr'], true)) $query->where(fn ($q) => $q->where('approval_route', 'hr')->orWhere('assigned_approver_id', $user->id));
            else abort(403);
        }
        $requests = $query->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->type, fn ($q, $type) => $q->where('request_type', $type))
            ->latest()->paginate(25)->withQueryString();
        return view('leave-requests.index', compact('requests'));
    }

    public function show(LeaveRequest $leaveRequest, LeaveRequestService $service, Request $request)
    {
        $leaveRequest->load(['employee.departament', 'employee.positions', 'employee.supervisor', 'assignedApprover', 'decisionMaker']);
        abort_unless($service->canReview($request->user(), $leaveRequest), 403);
        $administrativeRemaining = max(0, 3 - $service->administrativePermissionsUsed($leaveRequest->employee, $leaveRequest->start_date));
        return view('leave-requests.show', compact('leaveRequest', 'administrativeRemaining'));
    }

    public function decide(Request $request, LeaveRequest $leaveRequest, LeaveRequestService $service)
    {
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'decision_notes' => 'nullable|string|max:2000']);
        $service->decide($leaveRequest, $request->user(), $data['decision'], $data['decision_notes'] ?? null);
        return redirect()->route('leave-requests.index')->with('success', $data['decision'] === 'approved' ? 'Solicitud aprobada.' : 'Solicitud rechazada.');
    }

    public function evidence(LeaveRequest $leaveRequest, LeaveRequestService $service, Request $request)
    {
        abort_unless($service->canReview($request->user(), $leaveRequest), 403);
        abort_unless($leaveRequest->evidence_path && Storage::disk('local')->exists($leaveRequest->evidence_path), 404);
        return Storage::disk('local')->download($leaveRequest->evidence_path, $leaveRequest->evidence_original_name);
    }
}
