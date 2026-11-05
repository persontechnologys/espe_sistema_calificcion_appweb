@extends('layouts.app')
@section('title', $user->exists ? 'Editar usuario' : 'Nuevo usuario')
@section('content')
<x-ui.page-header :title="$user->exists ? 'Editar usuario' : 'Nuevo '.mb_strtolower($user->role->label())" :subtitle="$user->exists ? $user->name : null" />
<div class="card form-narrow"><div class="card-body p-4">
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" novalidate data-validate>
    @csrf
    @if ($user->exists) @method('PUT') @endif
    <x-form.section title="Identificación institucional" icon="bi-person-vcard">
        <div class="row">
            <div class="col-sm-5 mb-3">
                <label for="role" class="form-label">Rol<span class="req" aria-hidden="true">*</span></label>
                <select id="role" name="role" required aria-required="true" class="form-select @error('role') is-invalid @enderror">
                    @foreach (\App\Enums\UserRole::cases() as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                <x-form.error name="role" />
            </div>
            <x-form.input col="col-sm-7" name="institutional_id" label="ID institucional" :value="$user->institutional_id" maxlength="20"
                          help="Obligatorio para docentes. Se guarda en mayúsculas." />
        </div>
    </x-form.section>
    <x-form.section title="Datos personales" icon="bi-person">
        <x-form.input name="name" label="Nombre completo" :value="$user->name" required maxlength="150" />
    </x-form.section>
    <x-form.section title="Acceso" icon="bi-key">
        <x-form.input name="email" label="Correo" type="email" :value="$user->email" required maxlength="255" autocomplete="off" help="Se usa para iniciar sesión." />
        <div class="row">
            <x-form.input col="col-sm-6" name="password" :label="$user->exists ? 'Nueva contraseña' : 'Contraseña'" type="password" :required="! $user->exists"
                          :help="$user->exists ? 'Déjela vacía para no cambiarla.' : 'Mínimo 8 caracteres con letras y números.'" />
            <x-form.input col="col-sm-6" name="password_confirmation" label="Confirmar contraseña" type="password" :required="! $user->exists" />
        </div>
        <x-form.active-switch :value="$user->is_active" help="Un usuario inactivo no puede iniciar sesión ni operar." />
    </x-form.section>
    <p class="required-note"><span class="text-danger">*</span> Campo obligatorio.</p>
    <x-form.actions :cancel="route('admin.users.index')" />
</form>
</div></div>
@endsection
