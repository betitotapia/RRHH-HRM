<?php

namespace App\Http\Controllers;

use App\Models\TerminationType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TerminationTypeController extends Controller
{
    public function index()
    {
        $types = TerminationType::withCount('terminations')->orderBy('name')->paginate(25);
        return view('terminations.types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150|unique:termination_types,name']);
        $data['name'] = mb_strtoupper(trim($data['name']));
        $data['active'] = $request->boolean('active');
        TerminationType::create($data);
        return back()->with('success', 'Tipo de salida agregado correctamente.');
    }

    public function edit(TerminationType $terminationType)
    {
        return view('terminations.types.edit', compact('terminationType'));
    }

    public function update(Request $request, TerminationType $terminationType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('termination_types', 'name')->ignore($terminationType)],
        ]);
        $terminationType->update(['name' => mb_strtoupper(trim($data['name'])), 'active' => $request->boolean('active')]);
        return redirect()->route('termination-types.index')->with('success', 'Tipo de salida actualizado.');
    }

    public function destroy(TerminationType $terminationType)
    {
        if ($terminationType->terminations()->exists()) {
            return back()->with('error', 'No se puede eliminar un tipo que ya fue utilizado. Puedes desactivarlo.');
        }
        $terminationType->delete();
        return back()->with('success', 'Tipo de salida eliminado.');
    }
}
