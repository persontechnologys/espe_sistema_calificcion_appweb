@extends('layouts.app')
@section('title', $user->exists ? 'Editar usuario' : 'Nuevo usuario')
@section('content')
<h1 class="h3 mb-3">{{ $user->exists ? 'Editar usuario' : 'Nuevo '.mb_strtolower($user->role->label()) }}</h1>
<div class="card shadow-sm" style="max-width: 40rem"><div class="card-body">
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf
    @if ($user->exists) @method('PUT') @endif
    <div class="mb-3">
        <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
            @foreach (\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'role'])
    </div>
    @include('partials.input', ['name' => 'institutional_id', 'label' => 'ID institucional', 'value' => $user->institutional_id, 'maxlength' => 20, 'help' => 'Obligatorio para docentes. Se guarda en mayúsculas.'])
    @include('partials.input', ['name' => 'name', 'label' => 'Nombre completo', 'value' => $user->name, 'required' => true, 'maxlength' => 150])
    @include('partials.input', ['name' => 'email', 'label' => 'Correo', 'type' => 'email', 'value' => $user->email, 'required' => true, 'maxlength' => 255])
    <div class="row">
        @include('partials.input', ['class' => 'col-sm-6', 'name' => 'password', 'label' => $user->exists ? 'Nueva contraseña' : 'Contraseña', 'type' => 'password', 'required' => ! $user->exists, 'help' => $user->exists ? 'Déjela vacía para no cambiarla.' : 'Mínimo 8 caracteres con letras y números.'])
        @include('partials.input', ['class' => 'col-sm-6', 'name' => 'password_confirmation', 'label' => 'Confirmar contraseña', 'type' => 'password', 'required' => ! $user->exists])
    </div>
    @include('partials.active-checkbox', ['value' => $user->is_active])
    @include('partials.form-actions', ['cancel' => route('admin.users.index')])
</form>
</div></div>
@endsection
