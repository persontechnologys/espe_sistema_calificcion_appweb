@extends('layouts.app')
@section('title', 'Ofertas académicas (NRC)')
@section('content')
@include('partials.page-header', ['title' => 'Ofertas académicas (NRC)', 'createRoute' => route('admin.course-offerings.create'), 'createLabel' => 'Nueva oferta'])
@component('partials.search', ['placeholder' => 'NRC'])
    @slot('slot')
        <select name="academic_period_id" class="form-select" style="max-width: 16rem">
            <option value="">Todos los períodos</option>
            @foreach ($periods as $p)
                <option value="{{ $p->id }}" @selected((string) request('academic_period_id') === (string) $p->id)>{{ $p->code }}</option>
            @endforeach
        </select>
        <select name="subject_id" class="form-select" style="max-width: 18rem">
            <option value="">Todas las asignaturas</option>
            @foreach ($subjects as $s)
                <option value="{{ $s->id }}" @selected((string) request('subject_id') === (string) $s->id)>{{ $s->code }} · {{ $s->name }}</option>
            @endforeach
        </select>
    @endslot
@endcomponent
<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Período</th><th>NRC</th><th>Asignatura</th><th>Docente</th><th>Aula / Campus</th><th class="text-end">Matriculados</th><th></th></tr></thead>
    <tbody>
    @forelse ($offerings as $o)
        <tr>
            <td>{{ $o->academicPeriod->code }} <span class="badge {{ $o->academicPeriod->status->badgeClass() }}">{{ $o->academicPeriod->status->label() }}</span></td>
            <td><strong>{{ $o->nrc }}</strong></td>
            <td>{{ $o->subject->code }} · {{ $o->subject->name }}</td>
            <td>{{ $o->teacher->name }} <span class="text-muted small">({{ $o->teacher->institutional_id }})</span></td>
            <td>{{ $o->classroom ?? '—' }} / {{ $o->campus ?? '—' }}</td>
            <td class="text-end">{{ $o->enrollments_count }}</td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-success" href="{{ route('admin.course-offerings.show', $o) }}">Ver</a>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.course-offerings.edit', $o) }}">Editar</a>
                @include('partials.delete-button', ['action' => route('admin.course-offerings.destroy', $o)])
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Sin ofertas.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $offerings->links() }}</div>
@endsection
