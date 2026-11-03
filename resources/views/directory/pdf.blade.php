<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Directorio SUMED</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h2, h3 { margin-bottom: 2px; }
        .section-title { background: #eee; padding: 4px 6px; margin-top: 12px; }
        .employee { margin-left: 10px; }
    </style>
</head>
<body>

<h2>Directorio SUMED</h2>

{{-- Departamentos --}}
@foreach ($departaments as $departament)
    <div class="section-title">
        <strong>Departamento:</strong> {{ $departament->name }}
    </div>

    @forelse ($departament->employees as $employee)
        @php
            $fullName = trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names);
            $jobTitle = $employee->positions->name ?? 'Colaborador';
        @endphp
        <div class="employee">
            • {{ $fullName }} — {{ $jobTitle }} — {{ $employee->email ?? '' }}
        </div>
    @empty
        <div class="employee">
            (Sin colaboradores)
        </div>
    @endforelse
@endforeach

{{-- Comisiones --}}
@foreach ($commissions as $commission)
    <div class="section-title">
        <strong>Comisión:</strong> {{ $commission->name }}
    </div>

    @forelse ($commission->employees as $employee)
        @php
            $fullName = trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names);
            $jobTitle = $employee->positions->name ?? 'Colaborador';
        @endphp
        <div class="employee">
            • {{ $fullName }} — {{ $jobTitle }}
        </div>
    @empty
        <div class="employee">
            (Sin miembros)
        </div>
    @endforelse
@endforeach

</body>
</html>
