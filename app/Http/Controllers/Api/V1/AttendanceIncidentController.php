<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAttendanceIncidentRequest;
use App\Models\AttendanceIncident;

class AttendanceIncidentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canRegisterAttendanceIncidents(), 403);
        return AttendanceIncident::with(['employee', 'creator'])->latest('starts_at')->paginate(min((int)$request->input('per_page', 25), 100));
    }

    public function store(StoreAttendanceIncidentRequest $request)
    {
        $data = $request->validated(); unset($data['document']);
        if ($request->hasFile('document')) $data['document_path'] = $request->file('document')->store('attendance-incidents', 'public');
        $data += ['created_by'=>$request->user()->id,'approved_by'=>$request->user()->id,'approved_at'=>now(),'status'=>'approved'];
        return response()->json(AttendanceIncident::create($data)->load(['employee','creator']), 201);
    }
}
