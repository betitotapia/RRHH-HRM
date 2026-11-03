@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Nombre para mostrar *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Correo de acceso *</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Rol *</label>
        <select name="role" class="form-control" required>
            <option value="">-- Selecciona --</option>
            @foreach($roles as $value => $label)<option value="{{ $value }}" @selected(old('role', $user->role ?? 'employee') === $value)>{{ $label }}</option>@endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Estado de la cuenta</label>
        <div class="custom-control custom-switch mt-2">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
            <label class="custom-control-label" for="is_active">Permitir acceso al sistema</label>
        </div>
    </div>
</div>
<hr>
@isset($user)<p class="text-muted">Deja la contraseña vacía para conservar la actual.</p>@endisset
<div class="form-row">
    <div class="form-group col-md-6"><label>{{ isset($user) ? 'Nueva contraseña' : 'Contraseña *' }}</label><input type="password" name="password" class="form-control" @required(!isset($user))></div>
    <div class="form-group col-md-6"><label>Confirmar contraseña{{ isset($user) ? '' : ' *' }}</label><input type="password" name="password_confirmation" class="form-control" @required(!isset($user))></div>
</div>
