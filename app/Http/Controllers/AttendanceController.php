<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Vista: Mi asistencia
    public function my(Request $request)
    {
        $user = $request->user();

        // IMPORTANTE: necesitamos vincular usuario con employee.
        // Opción A: si ya tienes user->employee_id
        // Opción B: buscar por email, etc.
        // Aquí asumo que tienes employee_id en users:
        $employeeId = $user->employee_id ?? null;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Tu usuario no está vinculado a un empleado.');
        }

        $employee = Employee::findOrFail($employeeId);

        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->query('to', now()->endOfMonth()->toDateString()))->endOfDay();

        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('registered_at', [$from, $to])
            ->orderBy('registered_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // último registro para saber qué botón mostrar
        $last = AttendanceRecord::where('employee_id', $employee->id)
            ->orderBy('registered_at', 'desc')
            ->first();

        return view('attendance.my', compact('employee', 'records', 'from', 'to', 'last'));
    }

    // Registrar entrada
    public function checkIn(Request $request)
    {
        $user = $request->user();
        $employeeId = $user->employee_id ?? null;

      if (!$employeeId) {
    return redirect()->route('dashboard')
        ->with('error', 'Tu usuario no está vinculado a un empleado. Pide a RH que lo asigne en Usuarios.');
}
        $employee = Employee::findOrFail($employeeId);

        $last = AttendanceRecord::where('employee_id', $employee->id)
            ->orderBy('registered_at', 'desc')
            ->first();

        // No permitir doble IN sin OUT
        if ($last && $last->type === 'in') {
            return back()->with('error', 'Ya registraste tu entrada. Primero registra tu salida.');
        }

        AttendanceRecord::create([
            'employee_id'   => $employee->id,
            'type'          => 'in',
            'registered_at' => now(),
            'source'        => 'web',
            'ip_address'    => $request->ip(),
            'user_agent'    => substr((string)$request->userAgent(), 0, 255),
        ]);

        return back()->with('success', 'Entrada registrada correctamente.');
    }

    // Registrar salida
    public function checkOut(Request $request)
    {
        $user = $request->user();
        $employeeId = $user->employee_id ?? null;

        if (!$employeeId) {
    return redirect()->route('dashboard')
        ->with('error', 'Tu usuario no está vinculado a un empleado. Pide a RH que lo asigne en Usuarios.');
}

        $employee = Employee::findOrFail($employeeId);

        $last = AttendanceRecord::where('employee_id', $employee->id)
            ->orderBy('registered_at', 'desc')
            ->first();

        // No permitir OUT si no hay IN
        if (!$last || $last->type !== 'in') {
            return back()->with('error', 'No puedes registrar salida si no has registrado entrada.');
        }

        AttendanceRecord::create([
            'employee_id'   => $employee->id,
            'type'          => 'out',
            'registered_at' => now(),
            'source'        => 'web',
            'ip_address'    => $request->ip(),
            'user_agent'    => substr((string)$request->userAgent(), 0, 255),
        ]);

        return back()->with('success', 'Salida registrada correctamente.');
    }
}
