@extends('layouts.app')
@section('title', $period->exists ? 'Editar período' : 'Nuevo período')
@section('content')
<h1 class="h3 mb-3">{{ $period->exists ? 'Editar período' : 'Nuevo período' }}</h1>
<div class="card shadow-sm" style="max-width: 40rem"><div class="card-body">
<form method="POST" action="{{ $period->exists ? route('admin.academic-periods.update', $period) : route('admin.academic-periods.store') }}">
    @csrf
    @if ($period->exists) @method('PUT') @endif
    @include('partials.input', ['name' => 'code', 'label' => 'Código', 'value' => $period->code, 'required' => true, 'maxlength' => 20, 'placeholder' => '202451'])
    @include('partials.input', ['name' => 'description', 'label' => 'Descripción', 'value' => $period->description, 'required' => true, 'maxlength' => 150, 'placeholder' => 'PREGRADO S-II OCT 24 - MAR 25'])
    <div class="row">
        @include('partials.input', ['class' => 'col-sm-6', 'name' => 'start_date', 'label' => 'Inicio', 'type' => 'date', 'value' => $period->start_date?->format('Y-m-d'), 'required' => true])
        @include('partials.input', ['class' => 'col-sm-6', 'name' => 'end_date', 'label' => 'Fin', 'type' => 'date', 'value' => $period->end_date?->format('Y-m-d'), 'required' => true])
    </div>
    <p class="text-muted small">
        @if ($period->exists)
            Estado actual: <span class="badge {{ $period->status->badgeClass() }}">{{ $period->status->label() }}</span>. Se cambia desde el listado (Abrir / Cerrar).
        @else
            El período se crea como <strong>Planificado</strong>.
        @endif
    </p>
    @include('partials.form-actions', ['cancel' => route('admin.academic-periods.index')])
</form>
</div></div>
@endsection
