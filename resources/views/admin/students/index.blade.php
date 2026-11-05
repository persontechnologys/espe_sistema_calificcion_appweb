@extends('layouts.app')
@section('title', 'Estudiantes')
@section('content')
<x-ui.page-header title="Estudiantes">
    <x-slot:actions>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Nuevo estudiante</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="card">
    <x-ui.filter-bar placeholder="ID, nombres o apellidos" search-label="Buscar estudiante" />
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr>
                <th>ID</th><th>Apellidos y nombres</th><th class="d-none d-lg-table-cell">Correo</th>
                <th class="num d-none d-sm-table-cell">Matrículas</th><th>Estado</th><th class="text-end"><span class="visually-hidden">Acciones</span></th>
            </tr></thead>
            <tbody>
            @forelse ($students as $student)
                <tr>
                    <td class="cell-strong text-nowrap">{{ $student->institutional_id }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td class="d-none d-lg-table-cell">{{ $student->email ?? '—' }}</td>
                    <td class="num d-none d-sm-table-cell">{{ $student->enrollments_count }}</td>
                    <td><x-ui.status-badge :active="$student->is_active" /></td>
                    <td class="text-end">
                        <div class="table-actions">
                            <x-ui.icon-link :href="route('admin.students.show', $student)" icon="bi-card-checklist" :label="'Calificaciones de '.$student->institutional_id" />
                            <x-ui.icon-link :href="route('admin.students.edit', $student)" icon="bi-pencil" :label="'Editar estudiante '.$student->institutional_id" />
                            <x-ui.delete-button :action="route('admin.students.destroy', $student)" :item="$student->institutional_id.' · '.$student->full_name" />
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="6" icon="bi-mortarboard" title="Sin estudiantes" message="{{ request()->query() ? 'Ningún estudiante coincide con la búsqueda.' : 'Registre el primer estudiante.' }}">
                    @unless (request()->query())
                        <a href="{{ route('admin.students.create') }}" class="btn btn-sm btn-primary">Nuevo estudiante</a>
                    @endunless
                </x-ui.empty-row>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer-pagination">{{ $students->links() }}</div>
</div>
@endsection
