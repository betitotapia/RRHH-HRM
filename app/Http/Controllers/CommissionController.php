<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class CommissionController extends Controller
{
    // LISTA + ALTA RÁPIDA
    public function index(Request $request)
    {
        $search = $request->input('search');

        $commissions = Commission::withCount('employees')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('commissions.index', compact('commissions', 'search'));
    }

    // NO USAREMOS CREATE (form está en index), pero lo dejamos por si se llama
    public function create()
    {
        return redirect()->route('commissions.index');
    }

    // GUARDAR NUEVA COMISIÓN
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'active'      => ['nullable', 'boolean'],
        ]);

        $commission = Commission::create([
            'name'        => $validated['name'],
            'code'        => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'active'      => $request->boolean('active'),
        ]);

        return redirect()
            ->route('commissions.index')
            ->with('success', 'Comisión creada correctamente.');
    }

    // FORM EDITAR
    public function edit(Commission $commission)
    {
        $employees = Employee::orderBy('last_name_1')
        ->orderBy('last_name_2')
        ->orderBy('first_names')
        ->get();

    // [employee_id => role_en_pivot]
    $membersById = $commission->employees
        ->pluck('pivot.role', 'id')
        ->toArray();

    $roles = $this->commissionRoles();

    return view('commissions.edit', compact('commission', 'employees', 'membersById', 'roles'));
    }

    // ACTUALIZAR COMISIÓN + MIEMBROS
    public function update(Request $request, Commission $commission)
    {
      
    $roles = array_keys($this->commissionRoles());

    $validated = $request->validate([
        'name'        => ['required', 'string', 'max:255'],
        'code'        => ['nullable', 'string', 'max:50'],
        'description' => ['nullable', 'string'],
        'active'      => ['nullable', 'boolean'],

        'employee_ids'   => ['nullable', 'array'],
        'employee_ids.*' => ['integer', 'exists:employees,id'],

        'member_roles'   => ['nullable', 'array'],
        'member_roles.*' => ['nullable', Rule::in($roles)],
    ]);

    $commission->name        = $validated['name'];
    $commission->code        = $validated['code'] ?? null;
    $commission->description = $validated['description'] ?? null;
    $commission->active      = $request->boolean('active');
    $commission->save();

    // Construir arreglo para sync con roles por miembro
    $employeeIds  = $validated['employee_ids'] ?? [];
    $memberRoles  = $validated['member_roles'] ?? [];

    $syncData = [];

    foreach ($employeeIds as $employeeId) {
        $syncData[$employeeId] = [
            'role' => $memberRoles[$employeeId] ?? null,
        ];
    }

    $commission->employees()->sync($syncData);

    return redirect()
        ->route('commissions.index')
        ->with('success', 'Comisión actualizada correctamente.');
    }

    // ELIMINAR
    public function destroy(Commission $commission)
    {
        $commission->delete();

        return redirect()
            ->route('commissions.index')
            ->with('success', 'Comisión eliminada correctamente.');
    }

    private function commissionRoles(): array
            {
                return [
                    'coordinator'  => 'Coordinador',
                    'secretary'  => 'Secretario',
                    'vocal'      => 'Vocal',
                ];
            }

            public function show(Commission $commission)
                {
                    $roles = $this->commissionRoles();

                    $commission->load(['employees' => function ($q) {
                        $q->orderBy('last_name_1')
                        ->orderBy('last_name_2')
                        ->orderBy('first_names');
                    }]);

                    return view('commissions.show', compact('commission', 'roles'));
                }



}
