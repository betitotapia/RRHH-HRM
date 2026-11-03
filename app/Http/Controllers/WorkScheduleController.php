<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workSchedules = WorkSchedule::withCount(['days','employees'])->orderBy('name')->paginate(20);
        return view('work-schedules.index', compact('workSchedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workSchedule = new WorkSchedule(['active'=>true,'has_breakfast'=>true,'has_lunch'=>true]); $days = collect();
        return view('work-schedules.form', compact('workSchedule','days'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data=$this->validateData($request); $workSchedule=DB::transaction(function()use($data){$s=WorkSchedule::create($data['schedule']);$this->syncDays($s,$data['days']);return $s;});
        return redirect()->route('attendance-schedules.index')->with('success','Horario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkSchedule $workSchedule)
    {
        return redirect()->route('attendance-schedules.edit',$workSchedule);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkSchedule $workSchedule)
    {
        $days=$workSchedule->days->keyBy('day_of_week'); return view('work-schedules.form',compact('workSchedule','days'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkSchedule $workSchedule)
    {
        $data=$this->validateData($request,$workSchedule); DB::transaction(function()use($data,$workSchedule){$workSchedule->update($data['schedule']);$this->syncDays($workSchedule,$data['days']);});
        return redirect()->route('attendance-schedules.index')->with('success','Horario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkSchedule $workSchedule)
    {
        if($workSchedule->employees()->exists()) return back()->with('error','No se puede eliminar un horario asignado a empleados.');
        $workSchedule->delete(); return back()->with('success','Horario eliminado.');
    }

    private function validateData(Request $request, ?WorkSchedule $workSchedule=null): array
    {
        $v=$request->validate(['name'=>'required|string|max:150','code'=>['required','alpha_dash','max:30',Rule::unique('work_schedules','code')->ignore($workSchedule)],'description'=>'nullable|string|max:1000','has_breakfast'=>'nullable|boolean','has_lunch'=>'nullable|boolean','active'=>'nullable|boolean','days'=>'nullable|array','days.*.active'=>'nullable|boolean','days.*.start_time'=>'nullable|date_format:H:i','days.*.end_time'=>'nullable|date_format:H:i','days.*.tolerance_minutes'=>'nullable|integer|min:0|max:180','days.*.breakfast_minutes'=>'nullable|integer|min:0|max:180','days.*.lunch_minutes'=>'nullable|integer|min:0|max:240']);
        return ['schedule'=>['name'=>$v['name'],'code'=>$v['code'],'description'=>$v['description']??null,'has_breakfast'=>$request->boolean('has_breakfast'),'has_lunch'=>$request->boolean('has_lunch'),'active'=>$request->boolean('active')],'days'=>$v['days']??[]];
    }
    private function syncDays(WorkSchedule $schedule,array $days): void
    {
        foreach(range(0,6) as $day){$row=$days[$day]??[];if(empty($row['active'])){$schedule->days()->where('day_of_week',$day)->delete();continue;}validator($row,['start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i'])->validate();$schedule->days()->updateOrCreate(['day_of_week'=>$day],['start_time'=>$row['start_time'],'end_time'=>$row['end_time'],'tolerance_minutes'=>$row['tolerance_minutes']??10,'breakfast_minutes'=>$row['breakfast_minutes']??30,'lunch_minutes'=>$row['lunch_minutes']??60]);}
    }
}
