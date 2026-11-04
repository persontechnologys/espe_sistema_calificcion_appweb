@extends('layouts.app')
@section('title', 'Reportes')
@section('content')
<h1 class="h3 mb-3">Reporte de calificaciones</h1>
<form method="GET" class="row g-2 align-items-end mb-3">
    <div class="col-md-3">
        <label class="form-label small" for="academic_period_id">Período</label>
        <select id="academic_period_id" name="academic_period_id" class="form-select">
            <option value="">Todos</option>
            @foreach ($periods as $p)
                <option value="{{ $p->id }}" @selected((string) request('academic_period_id') === (string) $p->id)>{{ $p->label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small" for="subject_id">Asignatura</label>
        <select id="subject_id" name="subject_id" class="form-select">
            <option value="">Todas</option>
            @foreach ($subjects as $s)
                <option value="{{ $s->id }}" @selected((string) request('subject_id') === (string) $s->id)>{{ $s->code }} · {{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small" for="nrc">NRC</label>
        <input id="nrc" name="nrc" value="{{ request('nrc') }}" class="form-control">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-success" type="submit">Filtrar</button>
        @if (request()->query())<a class="btn btn-link" href="{{ route('reports.index') }}">Limpiar</a>@endif
    </div>
</form>
<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Período</th><th>NRC</th><th>Asignatura</th><th>Docente</th><th class="text-end">Matriculados</th><th></th></tr></thead>
    <tbody>
    @forelse ($offerings as $o)
        <tr>
            <td>{{ $o->academicPeriod->code }} <span class="badge {{ $o->academicPeriod->status->badgeClass() }}">{{ $o->academicPeriod->status->label() }}</span></td>
            <td><strong>{{ $o->nrc }}</strong></td>
            <td>{{ $o->subject->code }} · {{ $o->subject->name }}</td>
            <td>{{ $o->teacher->name }}</td>
            <td class="text-end">{{ $o->enrollments_count }}</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-success" href="{{ route('reports.show', $o) }}">Ver reporte</a></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No hay ofertas que coincidan con el filtro.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $offerings->links() }}</div>
@endsection
