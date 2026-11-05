@extends('layouts.app')
@section('title', 'Reportes')
@section('content')
<x-ui.page-header title="Reporte de calificaciones" subtitle="Filtre por período, asignatura o NRC y abra el reporte imprimible de cada oferta." />

<div class="card">
    <x-ui.filter-bar search="nrc" placeholder="NRC" search-label="Buscar por NRC">
        <select id="academic_period_id" name="academic_period_id" class="form-select" aria-label="Filtrar por período">
            <option value="">Todos los períodos</option>
            @foreach ($periods as $p)
                <option value="{{ $p->id }}" @selected((string) request('academic_period_id') === (string) $p->id)>{{ $p->label }}</option>
            @endforeach
        </select>
        <select id="subject_id" name="subject_id" class="form-select" aria-label="Filtrar por asignatura">
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
                <th class="num">Matriculados</th><th class="text-end"><span class="visually-hidden">Acciones</span></th>
            </tr></thead>
            <tbody>
            @forelse ($offerings as $o)
                <tr>
                    <td class="cell-strong tabular">{{ $o->nrc }}</td>
                    <td>{{ $o->subject->name }}<span class="cell-sub">{{ $o->subject->code }}</span></td>
                    <td class="d-none d-md-table-cell">{{ $o->teacher->name }}</td>
                    <td class="d-none d-sm-table-cell text-nowrap">{{ $o->academicPeriod->code }} <x-ui.status-badge :status="$o->academicPeriod->status" /></td>
                    <td class="num">{{ $o->enrollments_count }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary text-nowrap" href="{{ route('reports.show', $o) }}"><i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Ver reporte</a>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="6" icon="bi-file-earmark-text" title="Sin resultados" message="No hay ofertas que coincidan con el filtro." />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer-pagination">{{ $offerings->links() }}</div>
</div>
@endsection
