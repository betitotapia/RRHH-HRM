<?php

namespace App\Http\Controllers;

use App\Models\Departament;
use Illuminate\Http\Request;

class DepartamentController extends Controller
{
    // Listar departamentos
    public function index()
    {
        $departaments = Departament::orderBy('name')->paginate(10);

        return view('departaments.index', compact('departaments'));
    }

    // Guardar nuevo departamento
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'active'      => 'nullable|boolean',
        ]);

        // Si el checkbox viene marcado, será true, si no, false
        $validated['active'] = $request->has('active');

        Departament::create($validated);

        return redirect()
            ->route('departaments.index')
            ->with('success', 'Departamento creado correctamente.');
    }
 public function edit(Departament $departament)
    {
        return view('departaments.edit', compact('departament'));
    }

    // ACTUALIZAR
    public function update(Request $request, Departament $departament)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'active'      => 'nullable|boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $departament->update($validated);

        return redirect()
            ->route('departaments.index')
            ->with('success', 'Departamento actualizado correctamente.');
    }

    // ELIMINAR
    public function destroy(Departament $departament)
    {
        $departament->delete();

        return redirect()
            ->route('departaments.index')
            ->with('success', 'Departamento eliminado correctamente.');
    }
}
