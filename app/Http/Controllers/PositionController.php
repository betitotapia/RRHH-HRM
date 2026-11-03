<?php

namespace App\Http\Controllers;

use App\Models\Positions;
use App\Models\Departament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PositionController extends Controller
{
    // LISTAR PUESTOS
    public function index()
    {
        $positions = Positions::with('departament')
            ->orderBy('name')
            ->paginate(10);

        // Para el formulario de alta
        $departaments = Departament::where('active', true)
            ->orderBy('name')
            ->get();
        
        return view('positions.index', compact('positions', 'departaments'));
    }

    // GUARDAR NUEVO PUESTO
    public function store(Request $request)
    {
        $validated = $request->validate([
            'departament_id' => 'required|exists:departaments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50',
            'level'         => 'required|string|max:50',
            'base_salary'   => 'nullable|numeric',
            'description'   => 'nullable|string',
            'active'        => 'nullable|boolean',
            'profile_pdf'   => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $validated['active'] = $request->boolean('active');
        unset($validated['profile_pdf']);
        if ($request->hasFile('profile_pdf')) $validated['profile_pdf_path'] = $request->file('profile_pdf')->store('position-profiles', 'local');

        Positions::create($validated);

        return redirect()
            ->route('positions.index')
            ->with('success', 'Puesto creado correctamente.');
    }

    // FORMULARIO DE EDICIÓN
    public function edit(Positions $position)
    {
        $departaments = Departament::where('active', true)
            ->orderBy('name')
            ->get();

        return view('positions.edit', compact('position', 'departaments'));
    }

    // ACTUALIZAR
    public function update(Request $request, Positions $position)
    {
        $validated = $request->validate([
            'departament_id' => 'required|exists:departaments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50',
            'level'         => 'required|string|max:50',
            'base_salary'   => 'nullable|numeric',
            'description'   => 'nullable|string',
            'active'        => 'nullable|boolean',
            'profile_pdf'   => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $validated['active'] = $request->boolean('active');
        unset($validated['profile_pdf']);
        $oldPdf = $position->profile_pdf_path;
        if ($request->hasFile('profile_pdf')) $validated['profile_pdf_path'] = $request->file('profile_pdf')->store('position-profiles', 'local');

        $position->update($validated);
        if ($request->hasFile('profile_pdf') && $oldPdf) Storage::disk('local')->delete($oldPdf);

        return redirect()
            ->route('positions.index')
            ->with('success', 'Puesto actualizado correctamente.');
    }

    // ELIMINAR
    public function destroy(Positions $position)
    {
        $profilePdf = $position->profile_pdf_path;
        $position->delete();
        if ($profilePdf) Storage::disk('local')->delete($profilePdf);

        return redirect()
            ->route('positions.index')
            ->with('success', 'Puesto eliminado correctamente.');
    }

    public function viewProfile(Positions $position)
    {
        abort_unless($position->profile_pdf_path && Storage::disk('local')->exists($position->profile_pdf_path), 404);
        return Storage::disk('local')->response($position->profile_pdf_path, 'perfil-'.Str::slug($position->code ?: (string)$position->id).'.pdf', ['Content-Type'=>'application/pdf','Content-Disposition'=>'inline']);
    }

    public function downloadProfile(Positions $position)
    {
        abort_unless($position->profile_pdf_path && Storage::disk('local')->exists($position->profile_pdf_path), 404);
        return Storage::disk('local')->download($position->profile_pdf_path, 'perfil-'.Str::slug($position->code ?: (string)$position->id).'.pdf');
    }
}
