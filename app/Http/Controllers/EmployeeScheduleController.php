<?php

namespace App\Http\Controllers;

use App\Models\{Employee, EmployeeSchedule};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeScheduleController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with('schedules')->orderBy('last_name_1')->orderBy('first_names')->paginate(25);
        return view('attendance.schedules.index', compact('employees'));
    }

    public function edit(Employee $employee)
    {
        $schedules = $employee->schedules->keyBy('day_of_week');
        return view('attendance.schedules.edit', compact('employee', 'schedules'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'days' => ['nullable', 'array'], 'days.*.active' => ['nullable', 'boolean'],
            'days.*.start_time' => ['nullable', 'date_format:H:i'], 'days.*.end_time' => ['nullable', 'date_format:H:i'],
            'days.*.tolerance_minutes' => ['nullable', 'integer', 'min:0', 'max:180'],
            'days.*.breakfast_minutes' => ['nullable', 'integer', 'min:0', 'max:180'],
            'days.*.lunch_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
        ]);
        DB::transaction(function () use ($employee, $data) {
            foreach (range(0, 6) as $day) {
                $row = $data['days'][$day] ?? [];
                if (empty($row['active'])) { $employee->schedules()->where('day_of_week', $day)->delete(); continue; }
                validator($row, ['start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i'])->validate();
                EmployeeSchedule::updateOrCreate(['employee_id'=>$employee->id,'day_of_week'=>$day], [
                    'start_time'=>$row['start_time'],'end_time'=>$row['end_time'],
                    'tolerance_minutes'=>$row['tolerance_minutes']??10,'breakfast_minutes'=>$row['breakfast_minutes']??30,
                    'lunch_minutes'=>$row['lunch_minutes']??60,'active'=>true,
                ]);
            }
        });
        return redirect()->route('attendance-schedules.index')->with('success', 'Horario actualizado correctamente.');
    }
}
