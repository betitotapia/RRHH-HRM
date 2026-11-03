<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeTermination;
use App\Models\TerminationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeTerminationController extends Controller
{
    public function index(Request $request)
    {
        $terminations = EmployeeTermination::with(['employee.departament', 'employee.positions', 'type', 'creator'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('employee', fn ($employee) => $employee
                    ->where('employee_number', 'like', "%{$search}%")
                    ->orWhere('first_names', 'like', "%{$search}%")
                    ->orWhere('last_name_1', 'like', "%{$search}%"));
            })
            ->when($request->termination_type_id, fn ($query, $type) => $query->where('termination_type_id', $type))
            ->latest('termination_date')->paginate(25)->withQueryString();
        $types = TerminationType::orderBy('name')->get();
        return view('terminations.index', compact('terminations', 'types'));
    }

    public function create(Request $request)
    {
        $employees = Employee::whereDoesntHave('termination')->orderBy('last_name_1')->orderBy('first_names')->get();
        $types = TerminationType::where('active', true)->orderBy('name')->get();
        return view('terminations.create', compact('employees', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id|unique:employee_terminations,employee_id',
            'termination_type_id' => 'required|exists:termination_types,id',
            'termination_date' => 'required|date',
            'last_working_day' => 'nullable|date|before_or_equal:termination_date',
            'notes' => 'nullable|string|max:3000',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('employee-terminations/'.now()->format('Y/m'), 'local');
            $data['document_path'] = $documentPath;
            $data['document_original_name'] = $request->file('document')->getClientOriginalName();
        }
        unset($data['document']);
        $data['created_by'] = $request->user()->id;

        try {
            DB::transaction(function () use ($data) {
                $termination = EmployeeTermination::create($data);
                $termination->employee()->update(['active' => false]);
                $termination->employee->user?->update(['is_active' => false]);
                $termination->employee->user?->tokens()->delete();
            });
        } catch (\Throwable $exception) {
            if ($documentPath) Storage::disk('local')->delete($documentPath);
            throw $exception;
        }

        return redirect()->route('employee-terminations.index')->with('success', 'Baja registrada y colaborador desactivado correctamente.');
    }

    public function document(EmployeeTermination $employeeTermination)
    {
        abort_unless($employeeTermination->document_path && Storage::disk('local')->exists($employeeTermination->document_path), 404);
        return Storage::disk('local')->download($employeeTermination->document_path, $employeeTermination->document_original_name);
    }
}
