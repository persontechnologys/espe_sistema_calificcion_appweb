@extends('layouts.app')
@section('title', $period->exists ? 'Editar período' : 'Nuevo período')
@section('content')
<x-ui.page-header :title="$period->exists ? 'Editar período '.$period->code : 'Nuevo período'" />
<div class="card form-narrow"><div class="card-body p-4">
<form method="POST" action="{{ $period->exists ? route('admin.academic-periods.update', $period) : route('admin.academic-periods.store') }}" novalidate data-validate>
    @csrf
    @if ($period->exists) @method('PUT') @endif
    <x-form.section title="Identificación del período" icon="bi-calendar3">
        <div class="row">
            <x-form.input col="col-sm-4" name="code" label="Código" :value="$period->code" required maxlength="20" placeholder="202451" help="Único. Se guarda en mayúsculas." />
            <x-form.input col="col-sm-8" name="description" label="Descripción" :value="$period->description" required maxlength="150" placeholder="PREGRADO S-II OCT 24 - MAR 25" />
        </div>
    </x-form.section>
    <x-form.section title="Vigencia" icon="bi-clock">
        <div class="row">
            <x-form.input col="col-sm-6" name="start_date" label="Inicio" type="date" :value="$period->start_date?->format('Y-m-d')" required />
            <x-form.input col="col-sm-6" name="end_date" label="Fin" type="date" :value="$period->end_date?->format('Y-m-d')" required />
        </div>
        <p class="text-muted small mb-0">
            @if ($period->exists)
                Estado actual: <x-ui.status-badge :status="$period->status" />. Se cambia desde el listado (Abrir / Cerrar).
            @else
                El período se crea como <strong>Planificado</strong>.
            @endif
        </p>
    </x-form.section>
    <p class="required-note"><span class="text-danger">*</span> Campo obligatorio.</p>
    <x-form.actions :cancel="route('admin.academic-periods.index')" />
</form>
</div></div>
@endsection
