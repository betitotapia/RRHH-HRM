<?php

namespace App\Http\Controllers;

use App\Models\Departament;
use App\Models\Commission;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Positions;
use Barryvdh\DomPDF\Facade\Pdf;

class DirectoryController extends Controller
{
    // Página principal: tabs Departamentos / Comisiones
    public function index(Request $request)
    {
        // Para la pestaña activa (por defecto departamentos)
        $tab = $request->query('tab', 'departments');
          $search = $request->query('search');

        $departaments = Departament::where('active', true)
            ->orderBy('name')
            ->get();

        $commissions = Commission::where('active', true)
            ->orderBy('name')
            ->get();
            $employees = collect();

        if ($search) {
            $employees = Employee::with(['departament', 'positions'])
                ->where('active', true)
                ->where(function ($q) use ($search) {
                    $q->where('first_names', 'like', "%{$search}%")
                      ->orWhere('last_name_1', 'like', "%{$search}%")
                      ->orWhere('last_name_2', 'like', "%{$search}%")
                      ->orWhereHas('departament', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('positions', function ($q3) use ($search) {
                          $q3->where('name', 'like', "%{$search}%");
                      });
                })
                ->orderBy('last_name_1')
                ->orderBy('last_name_2')
                ->orderBy('first_names')
                ->get();
        }

        return view('directory.index', compact('departaments', 'commissions', 'tab', 'employees', 'search' ));
    }

    // Vista de un departamento con sus miembros
    public function department(Departament $departament)
    {
      $departaments = Departament::where('active', true)
        ->orderBy('name')
        ->get();

    $employees = Employee::with('positions', 'departament')
        ->where('departament_id', $departament->id)
        // si quieres seguir filtrando, déjalo, si no, comentarlo:
        // ->where('active', true)
        ->orderBy('last_name_1')
        ->orderBy('last_name_2')
        ->orderBy('first_names')
        ->get();

    return view('directory.department', compact('departament', 'departaments', 'employees'));
    }

    // Vista de una comisión con sus miembros
    public function commission(Commission $commission)
    {
        $commissions = Commission::where('active', true)
            ->orderBy('name')
            ->get();

        $commission->load(['employees' => function ($q) {
            $q->orderBy('last_name_1')
              ->orderBy('last_name_2')
              ->orderBy('first_names');
        }]);

        return view('directory.commission', compact('commission', 'commissions'));
    }

    // Ficha del colaborador
    public function employee(Employee $employee)
    {
        // Por si quieres mostrar depto / puesto / comisiones
        $employee->load(['departament', 'positions', 'commissions']);

        return view('directory.employee', compact('employee'));
    }

      public function download()
    {
        $departaments = Departament::where('active', true)
            ->with(['employees' => function ($q) {
                $q->where('active', true)
                  ->orderBy('last_name_1')
                  ->orderBy('last_name_2')
                  ->orderBy('first_names')
                  ->with('positions');
            }])
            ->orderBy('name')
            ->get();

        $commissions = Commission::where('active', true)
            ->with(['employees' => function ($q) {
                $q->where('active', true)
                  ->orderBy('last_name_1')
                  ->orderBy('last_name_2')
                  ->orderBy('first_names')
                  ->with('positions');
            }])
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('directory.pdf', compact('departaments', 'commissions'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('directorio-sumed.pdf');
    }
}
