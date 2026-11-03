<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Departament;
use App\Models\Positions; // <-- Si tu modelo se llama Positions, cambia a: use App\Models\Positions as Position;
use App\Models\User;
use App\Models\WorkLocation;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    // LISTA
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $employees = Employee::with(['departament', 'positions', 'user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('first_names', 'like', "%{$search}%")
                        ->orWhere('last_name_1', 'like', "%{$search}%")
                        ->orWhere('last_name_2', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name_1')
            ->orderBy('last_name_2')
            ->orderBy('first_names')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees', 'search'));
    }

    // FORM NUEVO
    public function create()
    {
        $departaments = Departament::where('active', true)
            ->orderBy('name')
            ->get();

        // positions tiene active=1 por defecto :contentReference[oaicite:4]{index=4}
        $positions = Positions::where('active', true)
            ->orderBy('name')
            ->get();

        $workLocations = WorkLocation::where('is_active', true)->orderBy('name')->get();
        $workSchedules = WorkSchedule::where('active', true)->orderBy('name')->get();
        $supervisors = Employee::where('active', true)->orderBy('last_name_1')->orderBy('first_names')->get();
        return view('employees.create', compact('departaments', 'positions', 'workLocations', 'workSchedules', 'supervisors'));
    }

    // GUARDAR NUEVO
    public function store(Request $request)
{
    $validated = $this->validateEmployee($request, null);
    if (in_array($validated['work_mode'], ['mixed', 'home_office'], true)) {
        $validated['work_location_id'] = null;
    }

    DB::transaction(function () use ($request, &$validated) {

        // checkbox active
        $validated['active'] = $request->boolean('active');

        // foto (columna employees.photo)
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('employees', 'public');
        }

        /** @var \App\Models\Employee $employee */
        $employee = Employee::create($validated);

        // ===== ACCESO AL PORTAL =====
        // En create normalmente usarás create_user, pero soportamos también update_user por consistencia.
        $wantsUser  = $request->boolean('create_user') || $request->boolean('update_user');
        $wantsReset = $request->boolean('send_reset_link'); // <- tu checkbox

        if ($wantsUser) {
            $email = trim((string) ($request->input('user_email') ?: $employee->email));
            $role  = (string) ($request->input('user_role', 'employee'));

            if ($email !== '') {
                // Si existe un user con ese email ligado a OTRO empleado, no permitas
                $existing = User::where('email', $email)->first();
                if ($existing && $existing->employee_id && $existing->employee_id !== $employee->id) {
                    throw new \RuntimeException('Ese correo ya está ligado a otro empleado.');
                }

                // Crear o recuperar usuario por email
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'     => trim($employee->first_names . ' ' . $employee->last_name_1),
                        'password' => Hash::make(Str::random(40)), // temporal
                        'role'     => $role ?: 'employee',
                    ]
                );

                // Vincular empleado y actualizar rol/nombre
                $user->employee_id = $employee->id;
                $user->role = $role ?: ($user->role ?: 'employee');
                $user->name = trim($employee->first_names . ' ' . $employee->last_name_1);
                $user->save();

                // Enviar link de reset (si lo marcaste)
                if ($wantsReset) {
                    Password::sendResetLink(['email' => $email]);
                }
            }
        }
    });

    return redirect()
        ->route('employees.index')
        ->with('success', 'Empleado creado correctamente.');
}

    // DETALLE
    public function show(Employee $employee)
    {
        $employee->load(['departament', 'positions', 'user']);
        return view('employees.show', compact('employee'));
    }
    // PDF
    public function pdf(Employee $employee)
    {
        $employee->load(['departament', 'positions', 'user']);

        $pdf = Pdf::loadView('employees.pdf', compact('employee'));
        return $pdf->download('employee_' . $employee->employee_number . '.pdf');
    }    

    // FORM EDITAR
    public function edit(Employee $employee)
    {
        $departaments = Departament::where('active', true)
            ->orderBy('name')
            ->get();

        $positions = Positions::where('active', true)
            ->orderBy('name')
            ->get();

        $employee->load('user');

        $workLocations = WorkLocation::where('is_active', true)->orderBy('name')->get();
        $workSchedules = WorkSchedule::where('active', true)
            ->orWhere('id', $employee->work_schedule_id)
            ->orderBy('name')->get();
        $supervisors = Employee::where('active', true)->whereKeyNot($employee->id)->orderBy('last_name_1')->orderBy('first_names')->get();
        return view('employees.edit', compact('employee', 'departaments', 'positions', 'workLocations', 'workSchedules', 'supervisors'));
    }

    // ACTUALIZAR
    public function update(Request $request, Employee $employee)
{
    $validated = $this->validateEmployee($request, $employee->id);
    if (in_array($validated['work_mode'], ['mixed', 'home_office'], true)) {
        $validated['work_location_id'] = null;
    }
    if ((int) ($validated['supervisor_id'] ?? 0) === $employee->id) {
        throw \Illuminate\Validation\ValidationException::withMessages(['supervisor_id' => 'Un empleado no puede ser su propio superior inmediato.']);
    }

    DB::transaction(function () use ($request, $employee, &$validated) {

        $validated['active'] = $request->boolean('active');

        // foto (columna employees.photo)
        if ($request->hasFile('photo')) {
            if (!empty($employee->photo) && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }
            $validated['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee->update($validated);

        // ===== ACCESO AL PORTAL =====
        // En edit usas update_user (switch). También soportamos create_user por si lo usas en create.
        $wantsUser  = $request->boolean('update_user') || $request->boolean('create_user');
        $wantsReset = $request->boolean('send_reset_link'); // <- tu checkbox

        if ($wantsUser) {
            $email = trim((string) ($request->input('user_email') ?: $employee->email));
            $role  = (string) ($request->input('user_role', 'employee'));

            if ($email !== '') {
                // Si existe un user con ese email ligado a OTRO empleado, no permitas
                $existing = User::where('email', $email)->first();
                if ($existing && $existing->employee_id && $existing->employee_id !== $employee->id) {
                    throw new \RuntimeException('Ese correo ya está ligado a otro empleado.');
                }

                // Busca user ligado al empleado, si no existe lo crea (por email)
                $user = User::where('employee_id', $employee->id)->first();

                if (!$user) {
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name'     => trim($employee->first_names . ' ' . $employee->last_name_1),
                            'password' => Hash::make(Str::random(40)),
                            'role'     => $role ?: 'employee',
                        ]
                    );
                }

                // Actualiza email/rol/nombre y vínculo
                $user->email = $email;
                $user->role  = $role ?: ($user->role ?: 'employee');
                $user->name  = trim($employee->first_names . ' ' . $employee->last_name_1);
                $user->employee_id = $employee->id;
                $user->save();

                // Enviar link de reset (si lo marcaste)
                if ($wantsReset) {
                    Password::sendResetLink(['email' => $email]);
                }
            }
        }
    });

    return redirect()
        ->route('employees.edit', $employee)
        ->with('success', 'Empleado actualizado correctamente.');
}

    // ELIMINAR
    public function destroy(Employee $employee)
    {
        // borrar foto
        if (!empty($employee->photo) && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }

        // desvincular usuario (si existe)
        $user = User::where('employee_id', $employee->id)->first();
        if ($user) {
            $user->employee_id = null;
            $user->save();
        }

        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }

    /**
     * Validación centralizada con nombres reales de tu tabla employees
     * (photo, hire_date, gender, email, city, emergency_contact_*)
     */
    private function validateEmployee(Request $request, ?int $employeeId): array
    {
        return $request->validate([
            // Identidad básica :contentReference[oaicite:6]{index=6}
            'employee_number' => 'required|string|max:50|unique:employees,employee_number,' . ($employeeId ?? 'NULL') . ',id',
            'last_name_1'     => 'required|string|max:100',
            'last_name_2'     => 'nullable|string|max:100',
            'first_names'     => 'required|string|max:150',

            // Relaciones (tabla positions existe) :contentReference[oaicite:7]{index=7}
            'position_id'     => 'required|integer|exists:positions,id',
            'departament_id'  => 'required|integer|exists:departaments,id',

            // Campos RH :contentReference[oaicite:8]{index=8}
            'site'            => 'nullable|string|max:100',
            'work_mode'       => 'required|in:office,hospital,mixed,home_office',
            'work_location_id'=> 'nullable|required_if:work_mode,office,hospital|exists:work_locations,id',
            'work_schedule_id'=> 'required|exists:work_schedules,id',
            'supervisor_id'   => 'nullable|exists:employees,id',
            'vacation_days_available' => 'required|integer|min:0|max:365',
            'hire_date'       => 'nullable|date',
            'imss_date'       => 'nullable|date',
            'net_salary'      => 'nullable|numeric|min:0',

            // Personales :contentReference[oaicite:9]{index=9}
            'gender' => 'nullable|in:Masculino,Femenino,Otro',
            'disability'      => 'nullable|string|max:100',
            'birth_date'      => 'nullable|date',
            'age'             => 'nullable|integer|min:0|max:120',

            // Contacto :contentReference[oaicite:10]{index=10}
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:150',

            // Identificadores :contentReference[oaicite:11]{index=11}
            'curp'            => 'nullable|string|max:25',
            'rfc'             => 'nullable|string|max:20',
            'nss'             => 'nullable|string|max:20',

            // Extra :contentReference[oaicite:12]{index=12}
            'marital_status'  => 'nullable|string|max:50',
            'blood_type'      => 'nullable|string|max:10',

            // Dirección :contentReference[oaicite:13]{index=13}
            'street'          => 'nullable|string|max:150',
            'ext_number'      => 'nullable|string|max:30',
            'int_number'      => 'nullable|string|max:30',
            'neighborhood'    => 'nullable|string|max:120',
            'city'            => 'nullable|string|max:120',
            'postal_code'     => 'nullable|string|max:10',
            'state'           => 'nullable|string|max:120',

            // Emergencias :contentReference[oaicite:14]{index=14}
            'emergency_contact_name_1'  => 'nullable|string|max:150',
            'emergency_contact_phone_1' => 'nullable|string|max:50',
            'emergency_contact_name_2'  => 'nullable|string|max:150',
            'emergency_contact_phone_2' => 'nullable|string|max:50',

            // Foto (se guarda en employees.photo) :contentReference[oaicite:15]{index=15}
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // UI helpers
            'active'             => 'nullable|boolean',
            'create_user'        => 'nullable|boolean',
            'send_password_link' => 'nullable|boolean',
            'user_email'         => 'nullable|email|max:150',
            'update_user'      => 'nullable|boolean',
            'send_reset_link'  => 'nullable|boolean',
            'user_role'        => 'nullable|in:employee,assistant,analyst,supervisor,manager,hr,admin,super_admin',
        ]);
    }
}
