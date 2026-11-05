@extends('layouts.app')
@section('title', 'Asignaturas')
@section('content')
<x-ui.page-header title="Asignaturas">
    <x-slot:actions>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nueva asignatura</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="card">
    <x-ui.filter-bar placeholder="Código o nombre" search-label="Buscar asignatura" />
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr>
                <th>Código</th><th>Nombre</th><th class="num d-none d-sm-table-cell">Ofertas</th><th>Estado</th>
                <th class="text-end"><span class="visually-hidden">Acciones</span></th>
            </tr></thead>
            <tbody>
            @forelse ($subjects as $subject)
                <tr>
                    <td class="cell-strong text-nowrap">{{ $subject->code }}</td>
                    <td>{{ $subject->name }}</td>
                    <td class="num d-none d-sm-table-cell">{{ $subject->course_offerings_count }}</td>
                    <td><x-ui.status-badge :active="$subject->is_active" /></td>
                    <td class="text-end">
                        <div class="table-actions">
                            <x-ui.icon-link :href="route('admin.subjects.edit', $subject)" icon="bi-pencil" :label="'Editar asignatura '.$subject->code" />
                            <x-ui.delete-button :action="route('admin.subjects.destroy', $subject)" :item="$subject->code.' · '.$subject->name" />
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="5" icon="bi-journal-bookmark" title="Sin asignaturas" message="{{ request()->query() ? 'Ninguna asignatura coincide con la búsqueda.' : 'Registre la primera asignatura.' }}">
                    @unless (request()->query())
                        <a href="{{ route('admin.subjects.create') }}" class="btn btn-sm btn-primary">Nueva asignatura</a>
                    @endunless
                </x-ui.empty-row>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer-pagination">{{ $subjects->links() }}</div>
</div>
@endsection
