<?php

namespace App\Http\Controllers;

use App\Models\WorkLocation;
use App\Http\Requests\StoreWorkLocationRequest;
use App\Services\StationQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workLocations = WorkLocation::withCount('employees')->orderBy('name')->paginate(20);
        return view('work-locations.index', compact('workLocations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('work-locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkLocationRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['qr_secret'] = Str::random(64);
        WorkLocation::create($data);
        return redirect()->route('work-locations.index')->with('success', 'Sede creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkLocation $workLocation)
    {
        return redirect()->route('work-locations.edit', $workLocation);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkLocation $workLocation)
    {
        return view('work-locations.edit', compact('workLocation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreWorkLocationRequest $request, WorkLocation $workLocation)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $workLocation->update($data);
        return redirect()->route('work-locations.index')->with('success', 'Sede actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkLocation $workLocation)
    {
        if ($workLocation->employees()->exists()) return back()->with('error', 'No se puede eliminar una sede asignada a colaboradores.');
        $workLocation->delete();
        return redirect()->route('work-locations.index')->with('success', 'Sede eliminada.');
    }

    public function station(WorkLocation $workLocation)
    {
        abort_unless($workLocation->is_active, 404);
        return view('work-locations.station', compact('workLocation'));
    }

    public function stationToken(WorkLocation $workLocation, StationQrService $qr)
    {
        abort_unless($workLocation->is_active, 404);
        return response()->json($qr->issue($workLocation));
    }
}
