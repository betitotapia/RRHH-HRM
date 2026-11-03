@extends('layouts.admin')

@section('title', 'Editar empleado')

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Editar empleado</h3>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">
                        Volver al listado
                    </a>
                </div>

                <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        {{-- DATOS LABORALES --}}
                        <h5>Datos laborales</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Número de Empleado *</label>
                                    <input type="text" name="employee_number" class="form-control"
                                           value="{{ old('employee_number', $employee->employee_number) }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Departamento *</label>
                                    <select name="departament_id" class="form-control" required>
                                        <option value="">-- Selecciona --</option>
                                        @foreach ($departaments as $departament)
                                            <option value="{{ $departament->id }}"
                                                {{ old('departament_id', $employee->departament_id) == $departament->id ? 'selected' : '' }}>
                                                {{ $departament->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Puesto *</label>
                                    <select name="position_id" class="form-control" required>
                                        <option value="">-- Selecciona --</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>
                                            {{ $position->name }}{{ $position->level ? ' — Nivel '.$position->level : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Referencia de sede anterior</label>
                                    <input type="text" name="site" class="form-control"
                                           value="{{ old('site', $employee->site) }}">
                                </div>
                                <div class="form-group"><label>Modalidad de asistencia *</label><select name="work_mode" id="work_mode" class="form-control" required><option value="office" @selected(old('work_mode',$employee->work_mode)==='office')>Oficina fija (QR de sede)</option><option value="hospital" @selected(old('work_mode',$employee->work_mode)==='hospital')>Personal de hospital (geolocalización)</option><option value="mixed" @selected(old('work_mode',$employee->work_mode)==='mixed')>Mixta (QR o geolocalización en cualquier sede)</option><option value="home_office" @selected(old('work_mode',$employee->work_mode)==='home_office')>Home office (geolocalización sin límite de rango)</option></select><small id="work_mode_help" class="form-text text-muted"></small></div>
                                <div class="form-group" id="work_location_group"><label>Sede de asistencia *</label><select name="work_location_id" id="work_location_id" class="form-control"><option value="">-- Selecciona una sede --</option>@foreach($workLocations as $location)<option value="{{ $location->id }}" @selected((string)old('work_location_id',$employee->work_location_id)===(string)$location->id)>{{ $location->name }}</option>@endforeach</select>@if($workLocations->isEmpty())<small class="text-danger">Primero debe registrarse al menos una sede.</small>@endif</div>
                                <div class="form-group"><label>Horario laboral *</label><select name="work_schedule_id" class="form-control" required><option value="">-- Selecciona un horario --</option>@foreach($workSchedules as $schedule)<option value="{{ $schedule->id }}" @selected((string)old('work_schedule_id',$employee->work_schedule_id)===(string)$schedule->id)>{{ $schedule->name }} ({{ $schedule->code }})</option>@endforeach</select>@if($workSchedules->isEmpty())<small class="text-danger">Primero registra un horario laboral.</small>@endif</div>
                                <div class="form-group"><label>Superior inmediato</label><select name="supervisor_id" class="form-control"><option value="">-- Sin superior asignado --</option>@foreach($supervisors as $supervisor)<option value="{{ $supervisor->id }}" @selected(old('supervisor_id',$employee->supervisor_id)==$supervisor->id)>#{{ $supervisor->employee_number }} - {{ $supervisor->last_name_1 }} {{ $supervisor->first_names }}</option>@endforeach</select><small class="text-muted">Es necesario para enviar solicitudes a aprobación de la jefatura inmediata.</small></div>
                                <div class="form-group"><label>Días de vacaciones disponibles *</label><input type="number" name="vacation_days_available" class="form-control" min="0" max="365" required value="{{ old('vacation_days_available',$employee->vacation_days_available) }}"></div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Ingreso</label>
                                        <input type="date" name="hire_date" class="form-control"
                                               value="{{ old('hire_date', $employee->hire_date) }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Alta al IMSS</label>
                                        <input type="date" name="imss_date" class="form-control"
                                               value="{{ old('imss_date', $employee->imss_date) }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Salario neto</label>
                                    <input type="number" step="0.01" name="net_salary" class="form-control"
                                           value="{{ old('net_salary', $employee->net_salary) }}">
                                </div>

                                <div class="form-group form-check">
                                    <input type="checkbox" name="active" value="1"
                                           class="form-check-input" id="activeCheck"
                                        {{ old('active', $employee->active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activeCheck">Activo</label>
                                </div>
                            </div>
                        </div>

                        {{-- DATOS PERSONALES --}}
                        <h5 class="mt-4">Datos personales</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Primer Apellido *</label>
                                    <input type="text" name="last_name_1" class="form-control"
                                           value="{{ old('last_name_1', $employee->last_name_1) }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Segundo Apellido</label>
                                    <input type="text" name="last_name_2" class="form-control"
                                           value="{{ old('last_name_2', $employee->last_name_2) }}">
                                </div>

                                <div class="form-group">
                                    <label>Nombre(s) *</label>
                                    <input type="text" name="first_names" class="form-control"
                                           value="{{ old('first_names', $employee->first_names) }}" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Fotografía</label><br>

                                    @if ($employee->photo)
                                        <img src="{{ asset('storage/' . $employee->photo) }}"
                                             class="img-circle elevation-2 mb-2"
                                             style="width: 120px; height: 120px; object-fit: cover;">
                                    @endif

                                    <input type="file" name="photo" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group col-md-6">
                                    <label>Sexo</label>
                                    <select name="gender" class="form-control">
                                        <option value="">-- Selecciona --</option>
                                        <option value="Masculino" {{ old('gender', $employee->gender) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                        <option value="Femenino" {{ old('gender', $employee->gender) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                        <option value="Otro" {{ old('gender', $employee->gender) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Discapacidad</label>
                                    <input type="text" name="disability" class="form-control"
                                           value="{{ old('disability', $employee->disability) }}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Fecha de Nacimiento</label>
                                    <input type="date" name="birth_date" class="form-control"
                                           value="{{ old('birth_date', $employee->birth_date) }}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Edad</label>
                                    <input type="number" name="age" class="form-control"
                                           value="{{ old('age', $employee->age) }}">
                                </div>

                                <div class="form-row">
                                    <div class="col-md-6">

                                        <div class="form-group col-md-6">
                                            <label>Estado Civil</label>
                                            <input type="text" name="marital_status" class="form-control"
                                                   value="{{ old('marital_status', $employee->marital_status) }}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Tipo de Sangre</label>
                                            <select name="blood_type" class="form-control">
                                                <option value="">-- Selecciona --</option>
                                                @php
                                                    $bloodOptions = ['O+','O-','A+','A-','B+','B-','AB+','AB-'];
                                                @endphp
                                                @foreach ($bloodOptions as $type)
                                                    <option value="{{ $type }}"
                                                        {{ old('blood_type', $employee->blood_type) == $type ? 'selected' : '' }}>
                                                        {{ $type }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- CONTACTO --}}
                        <h5 class="mt-4">Contacto</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group col-md-6">
                                    <label>No. Telefónico</label>
                                    <input type="text" name="phone" class="form-control"
                                           value="{{ old('phone', $employee->phone) }}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Correo Electrónico</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email', $employee->email) }}">
                                </div>

                                {{-- IDENTIFICACIÓN --}}
                                <h5 class="mt-4">Identificación</h5>
                                <hr>

                                <div class="form-group col-md-6">
                                    <label>CURP</label>
                                    <input type="text" name="curp" class="form-control"
                                           value="{{ old('curp', $employee->curp) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group col-md-6">
                                    <label>RFC</label>
                                    <input type="text" name="rfc" class="form-control"
                                           value="{{ old('rfc', $employee->rfc) }}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>NSS</label>
                                    <input type="text" name="nss" class="form-control"
                                           value="{{ old('nss', $employee->nss) }}">
                                </div>
                            </div>
                        </div>

                        {{-- DIRECCIÓN --}}
                        <h5 class="mt-4">Dirección</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Calle (Dirección)</label>
                                    <input type="text" name="street" class="form-control"
                                           value="{{ old('street', $employee->street) }}">
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Número Exterior</label>
                                        <input type="text" name="ext_number" class="form-control"
                                               value="{{ old('ext_number', $employee->ext_number) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Número Interior</label>
                                        <input type="text" name="int_number" class="form-control"
                                               value="{{ old('int_number', $employee->int_number) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Código Postal</label>
                                        <input type="text" name="postal_code" class="form-control"
                                               value="{{ old('postal_code', $employee->postal_code) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Colonia</label>
                                    <input type="text" name="neighborhood" class="form-control"
                                           value="{{ old('neighborhood', $employee->neighborhood) }}">
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Municipio/Localidad</label>
                                        <input type="text" name="city" class="form-control"
                                               value="{{ old('city', $employee->city) }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Estado</label>
                                        <input type="text" name="state" class="form-control"
                                               value="{{ old('state', $employee->state) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CONTACTOS DE EMERGENCIA --}}
                        <h5 class="mt-4">Contactos de emergencia</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Nombre contacto de emergencia 1</label>
                                    <input type="text" name="emergency_contact_name_1" class="form-control"
                                           value="{{ old('emergency_contact_name_1', $employee->emergency_contact_name_1) }}">
                                </div>

                                <div class="form-group">
                                    <label>Teléfono de contacto de emergencia 1</label>
                                    <input type="text" name="emergency_contact_phone_1" class="form-control"
                                           value="{{ old('emergency_contact_phone_1', $employee->emergency_contact_phone_1) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre contacto de emergencia 2</label>
                                    <input type="text" name="emergency_contact_name_2" class="form-control"
                                           value="{{ old('emergency_contact_name_2', $employee->emergency_contact_name_2) }}">
                                </div>

                                <div class="form-group">
                                    <label>Teléfono de contacto de emergencia 2</label>
                                    <input type="text" name="emergency_contact_phone_2" class="form-control"
                                           value="{{ old('emergency_contact_phone_2', $employee->emergency_contact_phone_2) }}">
                                </div>
                            </div>
                        </div>

                        @push('scripts')
                        <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const workMode = document.getElementById('work_mode');
                            const locationGroup = document.getElementById('work_location_group');
                            const locationSelect = document.getElementById('work_location_id');
                            const modeHelp = document.getElementById('work_mode_help');
                            const modeMessages = {
                                office: 'El colaborador escanea el QR de su sede fija.',
                                hospital: 'La ubicación debe estar dentro del radio de la sede asignada.',
                                mixed: 'Puede escanear el QR de cualquier sede activa o usar geolocalización dentro de su radio.',
                                home_office: 'Se registra la ubicación como evidencia, sin restricción de distancia.'
                            };
                            function toggleWorkLocation() {
                                const needsLocation = ['office', 'hospital'].includes(workMode.value);
                                locationGroup.style.display = needsLocation ? '' : 'none';
                                locationSelect.required = needsLocation;
                                if (!needsLocation) locationSelect.value = '';
                                modeHelp.textContent = modeMessages[workMode.value] || '';
                            }
                            workMode.addEventListener('change', toggleWorkLocation);
                            toggleWorkLocation();

                        });
                        </script>
                        @endpush

                    </div>{{-- card-body --}}

                    <div class="card-footer d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Actualizar empleado</button>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
