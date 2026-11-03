<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Departament;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceAdminController extends Controller
{
    public function index(Request $request)
    {
        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->query('to', now()->endOfMonth()->toDateString()))->endOfDay();

        $departamentId = $request->query('departament_id');
        $employeeId    = $request->query('employee_id');
        $type          = $request->query('type'); // in/out
        $search        = $request->query('search');

        $query = AttendanceRecord::with(['employee.departament', 'employee.workSchedule.days', 'workLocation'])
            ->whereBetween('registered_at', [$from, $to]);

        if ($type) {
            $query->where('event_type', $type);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($departamentId) {
            $query->whereHas('employee', function ($q) use ($departamentId) {
                $q->where('departament_id', $departamentId);
            });
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_names', 'like', "%{$search}%")
                  ->orWhere('last_name_1', 'like', "%{$search}%")
                  ->orWhere('last_name_2', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('registered_at', 'desc')->paginate(25)->withQueryString();

        $departaments = Departament::where('active', true)->orderBy('name')->get();
        $employees    = Employee::orderBy('last_name_1')->orderBy('last_name_2')->orderBy('first_names')->get();

        return view('attendance.admin.index', compact(
            'records', 'from', 'to', 'departamentId', 'employeeId', 'type', 'search',
            'departaments', 'employees'
        ));
    }
    public function photo(AttendanceRecord $attendanceRecord)
    {
        abort_unless($attendanceRecord->photo_path && Storage::disk('local')->exists($attendanceRecord->photo_path), 404);

        return Storage::disk('local')->response($attendanceRecord->photo_path);
    }
}
