<?php

namespace App\Services;

use App\Jobs\SendLeaveRequestDecisionPush;
use App\Models\AttendanceIncident;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function businessDays(Carbon $start, Carbon $end): int
    {
        return collect(CarbonPeriod::create($start, $end))->filter(fn ($date) => $date->isWeekday())->count();
    }

    public function minimumVacationStartDate(): Carbon
    {
        $date = today();
        $remaining = 5;
        while ($remaining > 0) {
            $date->addDay();
            if ($date->isWeekday()) $remaining--;
        }
        return $date;
    }

    public function returnDate(Carbon $end): Carbon
    {
        $date = $end->copy()->addDay();
        while ($date->isWeekend()) $date->addDay();
        return $date;
    }

    public function create(Employee $employee, array $data): LeaveRequest
    {
        $isVacation = $data['request_type'] === 'vacation';
        if ($isVacation) {
            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = Carbon::parse($data['end_date'])->startOfDay();
            if ($start->isWeekend() || $end->isWeekend()) throw ValidationException::withMessages(['start_date' => 'La fecha inicial y la fecha final deben ser días hábiles (lunes a viernes).']);
            $days = $this->businessDays($start, $end);
            if ($start->lt($this->minimumVacationStartDate())) {
                throw ValidationException::withMessages(['start_date' => 'Las vacaciones deben solicitarse con al menos 5 días hábiles de anticipación.']);
            }
            if ($days > $employee->vacation_days_available) {
                throw ValidationException::withMessages(['end_date' => "Solicitas {$days} días hábiles y sólo tienes {$employee->vacation_days_available} disponibles."]);
            }
            $requestedStartAt = null;
            $requestedEndAt = null;
            $returnDate = $this->returnDate($end);
        } else {
            $requestedStartAt = Carbon::parse($data['permission_date'].' '.$data['permission_start_time']);
            if ($requestedStartAt->isWeekend()) throw ValidationException::withMessages(['permission_date' => 'El permiso debe solicitarse para un día hábil (lunes a viernes).']);
            if ($requestedStartAt->lt(now()->addHours(24))) throw ValidationException::withMessages(['permission_date' => 'El permiso administrativo debe solicitarse con al menos 24 horas de anticipación.']);
            if ($this->administrativePermissionsUsed($employee, $requestedStartAt) >= 3) {
                throw ValidationException::withMessages(['permission_date' => 'Ya utilizaste o tienes pendientes los 3 permisos administrativos de este mes.']);
            }
            $sameDay = $employee->leaveRequests()->where('request_type', 'administrative_permission')
                ->whereIn('status', ['pending', 'approved'])->whereDate('start_date', $requestedStartAt->toDateString())->exists();
            if ($sameDay) throw ValidationException::withMessages(['permission_date' => 'No puedes solicitar dos permisos administrativos para el mismo día.']);
            $requestedEndAt = $requestedStartAt->copy()->addHours(2);
            $start = $requestedStartAt->copy()->startOfDay();
            $end = $start->copy();
            $days = 1;
            $returnDate = $start->copy();
        }

        $overlap = $employee->leaveRequests()->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($start, $end, $requestedStartAt, $requestedEndAt, $isVacation) {
                if ($isVacation) {
                    $query->whereDate('start_date', '<=', $end)->whereDate('end_date', '>=', $start);
                } else {
                    $query->where(function ($q) use ($start) {
                        $q->where('request_type', 'vacation')->whereDate('start_date', '<=', $start)->whereDate('end_date', '>=', $start);
                    })->orWhere(function ($q) use ($requestedStartAt, $requestedEndAt) {
                        $q->where('request_type', 'administrative_permission')->where('requested_start_at', '<', $requestedEndAt)->where('requested_end_at', '>', $requestedStartAt);
                    });
                }
            })->exists();
        if ($overlap) throw ValidationException::withMessages(['start_date' => 'Ya existe una solicitud pendiente o aprobada que coincide con esas fechas.']);

        $approverId = null;
        if ($data['approval_route'] === 'supervisor') {
            if (! $employee->supervisor_id) throw ValidationException::withMessages(['approval_route' => 'El empleado no tiene un superior inmediato asignado.']);
            $approver = $employee->supervisor?->user;
            if (! $approver || ! in_array($approver->role, ['super_admin', 'admin', 'hr', 'supervisor'], true)) throw ValidationException::withMessages(['approval_route' => 'El superior inmediato no tiene un usuario con permisos para aprobar solicitudes.']);
            $approverId = $approver->id;
        }

        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'request_type' => $data['request_type'],
            'start_date' => $start,
            'end_date' => $end,
            'requested_start_at' => $requestedStartAt,
            'requested_end_at' => $requestedEndAt,
            'requested_business_days' => $days,
            'return_date' => $returnDate,
            'approval_route' => $data['approval_route'],
            'assigned_approver_id' => $approverId,
            'observations' => $data['observations'] ?? null,
            'evidence_path' => $data['evidence_path'] ?? null,
            'evidence_original_name' => $data['evidence_original_name'] ?? null,
        ]);
    }

    public function administrativePermissionsUsed(Employee $employee, ?Carbon $month = null): int
    {
        $month ??= now();
        return $employee->leaveRequests()->where('request_type', 'administrative_permission')
            ->whereIn('status', ['pending', 'approved'])
            ->whereYear('start_date', $month->year)->whereMonth('start_date', $month->month)->count();
    }

    public function canDecide(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->status !== 'pending') return false;
        return $this->canReview($user, $leaveRequest);
    }

    public function canReview(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($leaveRequest->approval_route === 'supervisor') return $leaveRequest->assigned_approver_id === $user->id;
        return in_array($user->role, ['admin', 'hr'], true);
    }

    public function decide(LeaveRequest $leaveRequest, User $user, string $decision, ?string $notes): LeaveRequest
    {
        if (! $this->canDecide($user, $leaveRequest)) abort(403, 'No tienes autorización para resolver esta solicitud.');

        $resolved = DB::transaction(function () use ($leaveRequest, $user, $decision, $notes) {
            $request = LeaveRequest::with('employee')->lockForUpdate()->findOrFail($leaveRequest->id);
            if ($request->status !== 'pending') throw ValidationException::withMessages(['status' => 'La solicitud ya fue resuelta.']);

            if ($decision === 'approved' && $request->request_type === 'vacation') {
                $employee = Employee::lockForUpdate()->findOrFail($request->employee_id);
                if ($employee->vacation_days_available < $request->requested_business_days) {
                    throw ValidationException::withMessages(['status' => 'El empleado ya no cuenta con días suficientes.']);
                }
                $employee->decrement('vacation_days_available', $request->requested_business_days);
            }

            $request->update(['status' => $decision, 'decided_by' => $user->id, 'decided_at' => now(), 'decision_notes' => $notes]);
            if ($decision === 'approved') {
                AttendanceIncident::create([
                    'employee_id' => $request->employee_id,
                    'type' => $request->request_type === 'vacation' ? 'vacation' : 'administrative_permission',
                    'starts_at' => $request->requested_start_at ?? $request->start_date->startOfDay(),
                    'ends_at' => $request->requested_end_at ?? $request->end_date->endOfDay(),
                    'notes' => 'Generada desde solicitud #'.$request->id.'. '.$request->observations,
                    'status' => 'approved', 'created_by' => $user->id, 'approved_by' => $user->id, 'approved_at' => now(),
                ]);
            }
            return $request->fresh(['employee', 'decisionMaker']);
        });

        SendLeaveRequestDecisionPush::dispatch($resolved->id)->afterCommit();

        return $resolved;
    }
}
