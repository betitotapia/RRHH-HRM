@php
    // Helper para fechas
    function formatDate($date) {
        return $date ? \Carbon\Carbon::parse($date)->format('d-m-Y') : '-';
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de colaborador {{ $employee->employee_number }}</title>
    <style>
        * {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11px;
        }
        body {
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .logo {
            float: left;
        }
        .logo img {
            height: 60px;
        }
        .company-info {
            float: left;
            margin-left: 10px;
        }
        .company-info h2 {
            margin: 0;
            font-size: 16px;
        }
        .company-info p {
            margin: 2px 0;
        }
        .title {
            text-align: right;
            font-size: 16px;
        }
        .clear {
            clear: both;
        }
        .section-title {
            background: #f0f0f0;
            padding: 4px 6px;
            margin-top: 12px;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        td, th {
            padding: 3px 4px;
            vertical-align: top;
        }
        .bordered td, .bordered th {
            border: 1px solid #ccc;
        }
        .label {
            font-weight: bold;
            width: 30%;
        }
        .small {
            font-size: 10px;
        }
        .photo {
            border: 1px solid #ccc;
            width: 105px;
            height: 130px;
            text-align: center;
            vertical-align: middle;
        }
        .photo img {
            width: 105px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <div class="header">
        <div class="logo">
            {{-- Cambia la ruta al logo de tu empresa --}}
            <img src="{{ public_path('img/logo.png') }}" alt="Logo">
        </div>
        <div class="company-info">
            <h2>ERP RH - SUMED</h2>
            <p class="small">Ficha de colaborador</p>
        </div>
        <div class="title">
            FICHA DE COLABORADOR<br>
            <span class="small">Generada: {{ now()->format('d-m-Y H:i') }}</span>
        </div>
        <div class="clear"></div>
    </div>

    {{-- DATOS PRINCIPALES --}}
    <table>
        <tr>
            <td>
                <strong style="font-weight:bold; font-size:16px;">{{ $employee->last_name_1 }} {{ $employee->last_name_2 }} {{ $employee->first_names }}</strong><br>
                Núm. Empleado: <strong>{{ $employee->employee_number }}</strong><br>
                Departamento: {{ $employee->departament?->name ?? '-' }}<br>
                Puesto: {{ $employee->position?->name ?? '-' }}<br>
                Sede: {{ $employee->site ?? '-' }}<br>
                Nivel: {{ $employee->positions?->level ?? '-' }}<br>
                Estatus: {{ $employee->active ? 'ACTIVO' : 'INACTIVO' }}
            </td>
            <td style="width:140px; text-align:right;">
                <table>
                    <tr>
                        <td class="photo">
                            @if ($employee->photo)
                                <img src="{{ public_path('storage/'.$employee->photo) }}" alt="Foto">
                            @else
                                <span class="small">Sin fotografía</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- DATOS LABORALES --}}
    <div class="section-title">Datos laborales</div>
    <table class="bordered">
        <tr>
            <td class="label">Ingreso</td>
            <td>{{ formatDate($employee->hire_date) }}</td>
            <td class="label">Alta al IMSS</td>
            <td>{{ formatDate($employee->imss_date) }}</td>
        </tr>
        <tr>
            <td class="label">Salario neto</td>
            <td>
                @if ($employee->net_salary)
                    ${{ number_format($employee->net_salary, 2) }}
                @else
                    -
                @endif
            </td>
            <td class="label">NSS</td>
            <td>{{ $employee->nss ?? '-' }}</td>
        </tr>
    </table>

    {{-- DATOS PERSONALES --}}
    <div class="section-title">Datos personales</div>
    <table class="bordered">
        <tr>
            <td class="label">Fecha de nacimiento</td>
            <td>{{ formatDate($employee->birth_date) }}</td>
            <td class="label">Edad</td>
            <td>{{ $employee->age ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Sexo</td>
            <td>{{ $employee->gender ?? '-' }}</td>
            <td class="label">Estado civil</td>
            <td>{{ $employee->marital_status ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tipo de sangre</td>
            <td>{{ $employee->blood_type ?? '-' }}</td>
            <td class="label">Discapacidad</td>
            <td>{{ $employee->disability ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">CURP</td>
            <td>{{ $employee->curp ?? '-' }}</td>
            <td class="label">RFC</td>
            <td>{{ $employee->rfc ?? '-' }}</td>
        </tr>
    </table>

    {{-- CONTACTO --}}
    <div class="section-title">Contacto</div>
    <table class="bordered">
        <tr>
            <td class="label">Teléfono</td>
            <td>{{ $employee->phone ?? '-' }}</td>
            <td class="label">Correo electrónico</td>
            <td>{{ $employee->email ?? '-' }}</td>
        </tr>
    </table>

    {{-- DIRECCIÓN --}}
    <div class="section-title">Dirección</div>
    <table class="bordered">
        <tr>
            <td class="label">Calle</td>
            <td colspan="3">{{ $employee->street ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Núm. exterior</td>
            <td>{{ $employee->ext_number ?? '-' }}</td>
            <td class="label">Núm. interior</td>
            <td>{{ $employee->int_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Colonia</td>
            <td>{{ $employee->neighborhood ?? '-' }}</td>
            <td class="label">C.P.</td>
            <td>{{ $employee->postal_code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Municipio / Localidad</td>
            <td>{{ $employee->city ?? '-' }}</td>
            <td class="label">Estado</td>
            <td>{{ $employee->state ?? '-' }}</td>
        </tr>
    </table>

    {{-- CONTACTOS DE EMERGENCIA --}}
    <div class="section-title">Contactos de emergencia</div>
    <table class="bordered">
        <tr>
            <td class="label">Contacto 1</td>
            <td>
                {{ $employee->emergency_contact_name_1 ?? '-' }}<br>
                <span class="small">{{ $employee->emergency_contact_phone_1 ?? '' }}</span>
            </td>
            <td class="label">Contacto 2</td>
            <td>
                {{ $employee->emergency_contact_name_2 ?? '-' }}<br>
                <span class="small">{{ $employee->emergency_contact_phone_2 ?? '' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>
