@extends('layouts.app')
@section('title', 'Ofertas académicas (NRC)')
@section('content')
<x-ui.page-header title="Ofertas académicas (NRC)" subtitle="Cada oferta une una asignatura, un período y un docente. El NRC es único dentro del período.">
    <x-slot:actions>
        <a href="{{ route('admin.course-offerings.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nueva oferta</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="card">
    <x-ui.filter-bar placeholder="NRC" search-label="Buscar por NRC">
        <select name="academic_period_id" class="form-select" aria-label="Filtrar por período">
            <option value="">Todos los períodos</option>
            @foreach ($periods as $p)
                <option value="{{ $p->id }}" @selected((string) request('academic_period_id') === (string) $p->id)>{{ $p->code }}</option>
            @endforeach
        </select>
        <select name="subject_id" class="form-select" aria-label="Filtrar por asignatura">
            <option value="">Todas las asignaturas</option>
            @foreach ($subjects as $s)
                <option value="{{ $s->id }}" @selected((string) request('subject_id') === (string) $s->id)>{{ $s->code }} · {{ $s->name }}</option>
            @endforeach
        </select>
    </x-ui.filter-bar>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr>
                <th>NRC</th><th>Asignatura</th><th class="d-none d-md-table-cell">Docente</th><th class="d-none d-sm-table-cell">Período</th>
                <th class="d-none d-xl-table-cell">Aula / Campus</th><th class="num">Matriculados</th><th class="text-end"><span class="visually-hidden">Acciones</span></th>
            </tr></thead>
            <tbody>
            @forelse ($offerings as $o)
                <tr>
                    <td class="cell-strong tabular">{{ $o->nrc }}</td>
                    <td>{{ $o->subject->name }}<span class="cell-sub">{{ $o->subject->code }}</span></td>
                    <td class="d-none d-md-table-cell">{{ $o->teacher->name }}<span class="cell-sub">{{ $o->teacher->institutional_id }}</span></td>
                    <td class="d-none d-sm-table-cell text-nowrap">{{ $o->academicPeriod->code }} <x-ui.status-badge :status="$o->academicPeriod->status" /></td>
                    <td class="d-none d-xl-table-cell">{{ $o->classroom ?? '—' }} / {{ $o->campus ?? '—' }}</td>
                    <td class="num">{{ $o->enrollments_count }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            <x-ui.icon-link :href="route('admin.course-offerings.show', $o)" icon="bi-eye" :label="'Ver oferta NRC '.$o->nrc" />
                            <x-ui.icon-link :href="route('admin.course-offerings.edit', $o)" icon="bi-pencil" :label="'Editar oferta NRC '.$o->nrc" />
                            <x-ui.delete-button :action="route('admin.course-offerings.destroy', $o)" :item="'NRC '.$o->nrc.' · '.$o->subject->name.' ('.$o->academicPeriod->code.')'" />
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="7" icon="bi-collection" title="Sin ofertas" message="{{ request()->query() ? 'Ninguna oferta coincide con el filtro.' : 'Cree la primera oferta académica.' }}">
                    @unless (request()->query())
                        <a href="{{ route('admin.course-offerings.create') }}" class="btn btn-sm btn-primary">Nueva oferta</a>
                    @endunless
                </x-ui.empty-row>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer-pagination">{{ $offerings->links() }}</div>
</div>
@endsection
