@extends('layouts.app')
@section('title', 'Asignaturas')
@section('content')
@include('partials.page-header', ['title' => 'Asignaturas', 'createRoute' => route('admin.subjects.create'), 'createLabel' => 'Nueva asignatura'])
@include('partials.search', ['placeholder' => 'Código o nombre'])
<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Código</th><th>Nombre</th><th class="text-end">Ofertas</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @forelse ($subjects as $subject)
        <tr>
            <td><strong>{{ $subject->code }}</strong></td>
            <td>{{ $subject->name }}</td>
            <td class="text-end">{{ $subject->course_offerings_count }}</td>
            <td>@include('partials.active-badge', ['active' => $subject->is_active])</td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.subjects.edit', $subject) }}">Editar</a>
                @include('partials.delete-button', ['action' => route('admin.subjects.destroy', $subject)])
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Sin asignaturas.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $subjects->links() }}</div>
@endsection
