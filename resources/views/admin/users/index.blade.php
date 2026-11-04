@extends('layouts.app')
@section('title', 'Usuarios y docentes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Usuarios y docentes</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create', ['role' => 'teacher']) }}" class="btn btn-success">Nuevo docente</a>
        <a href="{{ route('admin.users.create', ['role' => 'admin']) }}" class="btn btn-outline-success">Nuevo administrador</a>
    </div>
</div>
@component('partials.search', ['placeholder' => 'Nombre, correo o ID'])
    @slot('slot')
        <select name="role" class="form-select" style="max-width: 12rem">
            <option value="">Todos los roles</option>
            @foreach (\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
    @endslot
@endcomponent
<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th class="text-end">Ofertas</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @forelse ($users as $user)
        <tr>
            <td>{{ $user->institutional_id ?? '—' }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role->label() }}</td>
            <td class="text-end">{{ $user->isTeacher() ? $user->course_offerings_count : '' }}</td>
            <td>@include('partials.active-badge', ['active' => $user->is_active])</td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">Editar</a>
                @unless ($user->is(auth()->user()))
                    @include('partials.delete-button', ['action' => route('admin.users.destroy', $user)])
                @endunless
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Sin usuarios.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
