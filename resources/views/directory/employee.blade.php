@extends('layouts.directory')

@section('title', $employee->position->name ?? 'Colaborador')

@section('content')

    @php
        $fullName = trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names);
        $jobTitle = $employee->positions->name ?? 'Colaborador';
        $deptName = $employee->departament->name ?? '';
    @endphp

    <h2>{{ $jobTitle }}</h2>
    <hr>

    <div class="row">
        <div class="col-md-3 text-center">
            <img src="{{ asset('storage/' . $employee->photo) }}"
                 style="width:140px;height:140px;object-fit:cover;border-radius:4px;">

            <h4 class="mt-3 mb-1">{{ $fullName }}</h4>
            <p class="mb-1"><strong>No. de Colaborador:</strong> {{ $employee->employee_number }}</p>
            @if ($deptName)
                <p class="mb-0">{{ $deptName }}</p>
            @endif
        </div>

        <div class="col-md-9">
            <ul>
                <li>
                    <strong>Domicilio de sede:</strong>
                    {{ $employee->site ?? '5 B Sur 5741, Col. Villa Encantada, Puebla, Puebla' }}
                </li>
                <li>
                    <strong>Teléfono:</strong>
                    {{ $employee->phone ?? '(222) 604 4913 Ext.111' }}
                </li>
                <li>
                    <strong>Correo:</strong>
                    {{ $employee->email ?? 'recursos.humanos@sumed.com.mx' }}
                </li>
            </ul>

            {{-- Botones de QR y firma (por ahora solo placeholder) --}}
            <button class="btn btn-success mr-2" type="button" disabled>
                DESCARGAR QR
            </button>
            <button class="btn btn-success" type="button" disabled>
                DESCARGAR FIRMA
            </button>
        </div>
    </div>

@endsection
