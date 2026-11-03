@extends('layouts.admin')

@section('title', 'Detalle empleado')

@section('content')

    <div class="row">
        <div class="col-12">

            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        Detalle del empleado
                    </h3>
                    <div>
                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">
                            Volver a la lista
                        </a>
                        <a href="{{ route('employees.pdf', $employee) }}" class="btn btn-sm btn-dark" target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    {{-- CABECERA: FOTO + NOMBRE + DATOS PRINCIPALES --}}
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            @if ($employee->photo)
                                <img src="{{ asset('storage/' . $employee->photo) }}"
                                     class="img-circle elevation-2 mb-2"
                                     style="width: 140px; height: 140px; object-fit: cover;">
                            @else
                                <div class="img-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2"
                                     style="width: 140px; height: 140px;">
                                    <span class="text-white" style="font-size: 2rem;">
                                        {{ strtoupper(mb_substr($employee->first_names, 0, 1)) }}
                                    </span>
                                </div>
                            @endif

                            <div>
                                <span class="badge badge-pill {{ $employee->active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $employee->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <h4 class="mb-1">
                                {{ $employee->last_name_1 }}
                                {{ $employee->last_name_2 }}
                                {{ $employee->first_names }}
                            </h4>
                            <p class="mb-1">
                                <strong>Núm. Empleado:</strong> {{ $employee->employee_number }}
                            </p>
                            <p class="mb-1">
                                <strong>Departamento:</strong> {{ $employee->departament?->name ?? '-' }}<br>
                                <strong>Puesto:</strong> {{ $employee->position?->name ?? '-' }}
                            </p>
                            <p class="mb-0">
                                <strong>Sede:</strong> {{ $employee->site ?? '-' }}<br>
                                <strong>Nivel:</strong> {{ $employee->positions?->level ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    {{-- TRES BLOQUES: Laboral, Personal, Contacto/Dirección --}}
                    <div class="row">

                        {{-- DATOS LABORALES --}}
                        <div class="col-md-4">
                            <h5>Datos laborales</h5>
                            <dl class="mb-0">
                                <dt>Fecha de ingreso</dt>
                                <dd>{{ $employee->hire_date_formatted ?? '-' }}</dd>

                                <dt>Fecha de alta al IMSS</dt>
                                <dd>{{ $employee->imss_date_formatted ?? '-' }}</dd>

                                <dt>Salario neto</dt>
                                <dd>
                                    @if ($employee->net_salary)
                                        ${{ number_format($employee->net_salary, 2) }}
                                    @else
                                        -
                                    @endif
                                </dd>
                            </dl>
                        </div>

                        {{-- DATOS PERSONALES --}}
                        <div class="col-md-4">
                            <h5>Datos personales</h5>
                            <dl class="mb-0">
                                <dt>Sexo</dt>
                                <dd>{{ $employee->gender ?? '-' }}</dd>

                                <dt>Fecha de Nacimiento</dt>
                                <dd>{{ $employee->birth_date_formatted  ?? '-' }}</dd>

                                <dt>Edad</dt>
                                <dd>{{ $employee->age ?? '-' }} años</dd>

                                <dt>Estado Civil</dt>
                                <dd>{{ $employee->marital_status ?? '-' }}</dd>

                                <dt>Tipo de Sangre</dt>
                                <dd>{{ $employee->blood_type ?? '-' }}</dd>

                                <dt>Discapacidad</dt>
                                <dd>{{ $employee->disability ?? '-' }}</dd>
                            </dl>
                        </div>

                        {{-- CONTACTO / FISCAL --}}
                        <div class="col-md-4">
                            <h5>Contacto</h5>
                            <dl class="mb-2">
                                <dt>Teléfono</dt>
                                <dd>{{ $employee->phone ?? '-' }}</dd>

                                <dt>Correo</dt>
                                <dd>{{ $employee->email ?? '-' }}</dd>
                            </dl>

                            <h5 class="mt-3">Identificación</h5>
                            <dl class="mb-0">
                                <dt>CURP</dt>
                                <dd>{{ $employee->curp ?? '-' }}</dd>

                                <dt>RFC</dt>
                                <dd>{{ $employee->rfc ?? '-' }}</dd>

                                <dt>NSS</dt>
                                <dd>{{ $employee->nss ?? '-' }}</dd>
                            </dl>
                        </div>

                    </div>

                    <hr>

                    {{-- DIRECCIÓN Y CONTACTOS DE EMERGENCIA --}}
                    <div class="row">

                        <div class="col-md-6">
                            <h5>Dirección</h5>
                            <dl class="mb-0">
                                <dt>Calle</dt>
                                <dd>{{ $employee->street ?? '-' }}</dd>

                                <dt>Número</dt>
                                <dd>
                                    Ext: {{ $employee->ext_number ?? '-' }},
                                    Int: {{ $employee->int_number ?? '-' }}
                                </dd>

                                <dt>Colonia</dt>
                                <dd>{{ $employee->neighborhood ?? '-' }}</dd>

                                <dt>Municipio / Localidad</dt>
                                <dd>{{ $employee->city ?? '-' }}</dd>

                                <dt>Código Postal</dt>
                                <dd>{{ $employee->postal_code ?? '-' }}</dd>

                                <dt>Estado</dt>
                                <dd>{{ $employee->state ?? '-' }}</dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h5>Contactos de emergencia</h5>
                            <dl class="mb-0">
                                <dt>Contacto 1</dt>
                                <dd>
                                    {{ $employee->emergency_contact_name_1 ?? '-' }}<br>
                                    <small>{{ $employee->emergency_contact_phone_1 ?? '' }}</small>
                                </dd>

                                <dt>Contacto 2</dt>
                                <dd>
                                    {{ $employee->emergency_contact_name_2 ?? '-' }}<br>
                                    <small>{{ $employee->emergency_contact_phone_2 ?? '' }}</small>
                                </dd>
                            </dl>
                        </div>

                         
                            <div class="col-md-6">
                                <h5 >Comisiones</h5>
                                 <dl class="mb-0">
                                    <hr>
                                    @if ($employee->commissions->isEmpty())
                                        <p class="text-muted">Este empleado no está asignado a ninguna comisión.</p>
                                    @else
                                        <ul>
                                            @foreach ($employee->commissions as $commission)
                                                <li>
                                                    {{ $commission->name }}
                                                    @if (!$commission->active)
                                                        <span class="badge badge-secondary">Inactiva</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    </dl>
                             </div>   
                        

                    </div>

                </div>
            </div>

        </div>
    </div>

@endsection
