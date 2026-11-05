@extends('layouts.app')
@section('title', 'Auditoría')
@section('content')
<x-ui.page-header title="Auditoría de calificaciones" subtitle="Registro de solo inserción: cada guardado o corrección de notas, con valores anteriores y nuevos." />

<div class="card">
    <x-ui.filter-bar search="student" placeholder="Estudiante (ID o nombre)" search-label="Buscar estudiante">
        <select id="operation" name="operation" class="form-select" aria-label="Filtrar por operación">
            <option value="">Todas las operaciones</option>
            @foreach (\App\Enums\AuditOperation::cases() as $op)
                <option value="{{ $op->value }}" @selected(request('operation') === $op->value)>{{ $op->label() }}</option>
            @endforeach
        </select>
        <input id="nrc" name="nrc" value="{{ request('nrc') }}" class="form-control" placeholder="NRC" aria-label="NRC" data-width="sm">
    </x-ui.filter-bar>
    @include('partials.audit-table', ['logs' => $logs])
    <div class="card-footer-pagination">{{ $logs->links() }}</div>
</div>
@endsection
