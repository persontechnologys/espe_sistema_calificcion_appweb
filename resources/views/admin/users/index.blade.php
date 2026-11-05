@extends('layouts.app')
@section('title', 'Usuarios y docentes')
@section('content')
<x-ui.page-header title="Usuarios y docentes" subtitle="Los docentes son usuarios con rol Docente e ID institucional.">
    <x-slot:actions>
        <a href="{{ route('admin.users.create', ['role' => 'teacher']) }}" class="btn btn-primary"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Nuevo docente</a>
        <a href="{{ route('admin.users.create', ['role' => 'admin']) }}" class="btn btn-outline-primary"><i class="bi bi-shield-plus me-1" aria-hidden="true"></i>Nuevo administrador</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="card">
    <x-ui.filter-bar placeholder="Nombre, correo o ID" search-label="Buscar usuario">
        <select name="role" class="form-select" aria-label="Filtrar por rol">
            <option value="">Todos los roles</option>
            @foreach (\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
    </x-ui.filter-bar>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr>
                <th class="d-none d-sm-table-cell">ID</th><th>Nombre</th><th class="d-none d-lg-table-cell">Correo</th><th class="d-none d-md-table-cell">Rol</th>
                <th class="num d-none d-md-table-cell">Ofertas</th><th>Estado</th><th class="text-end"><span class="visually-hidden">Acciones</span></th>
            </tr></thead>
            <tbody>
            @forelse ($users as $user)
                <tr>
                    <td class="d-none d-sm-table-cell cell-strong text-nowrap">{{ $user->institutional_id ?? '—' }}</td>
                    <td>{{ $user->name }}<span class="cell-sub d-lg-none">{{ $user->email }}</span></td>
                    <td class="d-none d-lg-table-cell">{{ $user->email }}</td>
                    <td class="d-none d-md-table-cell">{{ $user->role->label() }}</td>
                    <td class="num d-none d-md-table-cell">{{ $user->isTeacher() ? $user->course_offerings_count : '' }}</td>
                    <td><x-ui.status-badge :active="$user->is_active" /></td>
                    <td class="text-end">
                        <div class="table-actions">
                            <x-ui.icon-link :href="route('admin.users.edit', $user)" icon="bi-pencil" :label="'Editar usuario '.$user->name" />
                            @unless ($user->is(auth()->user()))
                                <x-ui.delete-button :action="route('admin.users.destroy', $user)" :item="$user->name.' ('.$user->email.')'" />
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="7" icon="bi-person-badge" title="Sin usuarios" message="Ningún usuario coincide con el filtro." />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer-pagination">{{ $users->links() }}</div>
</div>
@endsection
