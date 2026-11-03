<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartamentController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceAdminController;
use App\Http\Controllers\IclockController;
use App\Http\Controllers\AttendanceIncidentController;
use App\Http\Controllers\WorkLocationController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\EmployeeTerminationController;
use App\Http\Controllers\TerminationTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Middleware\VerifyCsrfToken;

// ----------------------
// Página pública o landing
// ----------------------
Route::get('/', function () {
    return view('welcome');
});

// ----------------------
// Dashboard solo para usuarios logueados
// ----------------------
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->role === 'employee') {
        return redirect()->route('attendance.my');
    }

    return redirect()->route('departaments.index');
})->middleware(['auth'])->name('dashboard');


// RUTAS DE AUTENTICACIÓN GENERADAS POR BREEZE
require __DIR__.'/auth.php';
// ----------------------
// Rutas protegidas solo para usuarios logueados
// *Aquí luego aplicaremos roles
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ----------------------
Route::middleware(['auth', 'role:admin,hr'])->group(function () {

    // PERFIL (Breeze)
   
    // ----------------------
    // ERP (Admin + RH)
    // Más adelante aplicaremos ->middleware('role:admin,hr')
    // ----------------------

    // DEPARTAMENTOS
    Route::get('/departaments', [DepartamentController::class, 'index'])->name('departaments.index');
    Route::get('/departaments/create', [DepartamentController::class, 'create'])->name('departaments.create');
    Route::post('/departaments', [DepartamentController::class, 'store'])->name('departaments.store');
    Route::get('/departaments/{departament}/edit', [DepartamentController::class, 'edit'])->name('departaments.edit');
    Route::put('/departaments/{departament}', [DepartamentController::class, 'update'])->name('departaments.update');
    Route::delete('/departaments/{departament}', [DepartamentController::class, 'destroy'])->name('departaments.destroy');

    // PUESTOS
    Route::get('positions/{position}/profile', [PositionController::class, 'viewProfile'])->name('positions.profile.view');
    Route::get('positions/{position}/profile/download', [PositionController::class, 'downloadProfile'])->name('positions.profile.download');
    Route::resource('positions', PositionController::class);

    // EMPLEADOS
    Route::resource('employees', EmployeeController::class);
    Route::get('employee-terminations/{employeeTermination}/document', [EmployeeTerminationController::class, 'document'])->name('employee-terminations.document');
    Route::resource('employee-terminations', EmployeeTerminationController::class)->only(['index', 'create', 'store']);
    Route::resource('termination-types', TerminationTypeController::class)->except(['show', 'create']);

    // PDF de empleado
    Route::get('/employees/{employee}/pdf', [EmployeeController::class, 'pdf'])->name('employees.pdf');

    // GESTIÓN DE USUARIOS (solo admin)
      Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
      Route::post('users/{user}/reset-link', [UserController::class, 'sendResetLink'])->name('users.reset-link');
      Route::resource('users', UserController::class)->except(['show']);
      
        Route::middleware(['auth', 'role:admin,hr'])->group(function () {

    Route::resource('commissions', CommissionController::class);
        });
    

});

// ADMINISTRACIÓN DE ASISTENCIA
    // Route::middleware(['auth', 'role:employee'])->group(function () {
    Route::middleware(['auth', 'role:employee'])->group(function () {
    Route::get('/mi-asistencia', [AttendanceController::class, 'my'])->name('attendance.my');
    Route::post('/mi-asistencia/entrada', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/mi-asistencia/salida', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
});
Route::middleware(['auth', 'role:admin,hr'])->group(function () {
    Route::get('/asistencias', [AttendanceAdminController::class, 'index'])->name('attendance.admin.index');
    Route::get('/asistencias/{attendanceRecord}/foto', [AttendanceAdminController::class, 'photo'])->name('attendance.admin.photo');
    Route::get('work-locations/{work_location}/station', [WorkLocationController::class, 'station'])->name('work-locations.station');
    Route::get('work-locations/{work_location}/station-token', [WorkLocationController::class, 'stationToken'])->name('work-locations.station-token');
    Route::resource('work-locations', WorkLocationController::class)->except(['show']);
    Route::resource('attendance-schedules', WorkScheduleController::class)->parameters(['attendance-schedules'=>'workSchedule'])->except(['show'])->names('attendance-schedules');
});
Route::middleware(['auth', 'role:admin,hr,supervisor,analyst,assistant'])->group(function () {
    Route::resource('attendance-incidents', AttendanceIncidentController::class)->only(['index', 'create', 'store']);
});
Route::middleware(['auth', 'role:admin,hr,supervisor'])->group(function () {
    Route::get('leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    Route::post('leave-requests/{leaveRequest}/decision', [LeaveRequestController::class, 'decide'])->name('leave-requests.decide');
    Route::get('leave-requests/{leaveRequest}/evidence', [LeaveRequestController::class, 'evidence'])->name('leave-requests.evidence');
});

Route::middleware('auth')->group(function () {
   Route::get('/directorio', [DirectoryController::class, 'index'])->name('directory.index');

// Vista de un departamento en el directorio
Route::get('/directorio/departamento/{departament}', [DirectoryController::class, 'department'])
    ->name('directory.department');

// Vista de una comisión en el directorio
Route::get('/directorio/commission/{commission}', [DirectoryController::class, 'commission'])
    ->name('directory.commission');

// Ficha de empleado en el directorio
Route::get('/directorio/empleado/{employee}', [DirectoryController::class, 'employee'])
    ->name('directory.employee');
});

Route::get('/directorio/descargar', [DirectoryController::class, 'download'])
    ->name('directory.download');

// Rutas para el controlador Iclock (exento de CSRF)
Route::prefix('iclock')
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->group(function () {
        Route::get('getrequest', [IclockController::class, 'getrequest']);
        Route::any('cdata', [IclockController::class, 'cdata']);
        Route::get('test', fn() => response('OK', 200));
    });
Route::get('/iclock/getrequest', function () {
    return response("OK");
});
