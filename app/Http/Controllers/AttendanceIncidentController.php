<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceIncidentRequest;
use App\Models\{AttendanceIncident, Employee};
use Illuminate\Http\Request;

class AttendanceIncidentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canRegisterAttendanceIncidents(), 403);
        $incidents = AttendanceIncident::with(['employee.departament', 'creator'])
            ->when($request->employee_id, fn ($q, $id) => $q->where('employee_id', $id))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest('starts_at')->paginate(25)->withQueryString();
        $employees = Employee::where('active', true)->orderBy('last_name_1')->orderBy('first_names')->get();
        return view('attendance.incidents.index', compact('incidents', 'employees'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canRegisterAttendanceIncidents(), 403);
        $employees = Employee::where('active', true)->orderBy('last_name_1')->orderBy('first_names')->get();
        return view('attendance.incidents.create', compact('employees'));
    }

    public function store(StoreAttendanceIncidentRequest $request)
    {
        $data = $request->validated();
        unset($data['document']);
        if ($request->hasFile('document')) $data['document_path'] = $request->file('document')->store('attendance-incidents', 'public');
        $data += ['created_by' => $request->user()->id, 'approved_by' => $request->user()->id, 'approved_at' => now(), 'status' => 'approved'];
        AttendanceIncident::create($data);
        return redirect()->route('attendance-incidents.index')->with('success', 'Incidencia registrada correctamente.');
    }
}
