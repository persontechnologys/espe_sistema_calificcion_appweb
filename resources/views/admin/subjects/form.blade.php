@extends('layouts.app')
@section('title', $subject->exists ? 'Editar asignatura' : 'Nueva asignatura')
@section('content')
<h1 class="h3 mb-3">{{ $subject->exists ? 'Editar asignatura' : 'Nueva asignatura' }}</h1>
<div class="card shadow-sm" style="max-width: 40rem"><div class="card-body">
<form method="POST" action="{{ $subject->exists ? route('admin.subjects.update', $subject) : route('admin.subjects.store') }}">
    @csrf
    @if ($subject->exists) @method('PUT') @endif
    @include('partials.input', ['name' => 'code', 'label' => 'Código', 'value' => $subject->code, 'required' => true, 'maxlength' => 20, 'placeholder' => 'COMP-A0G11', 'help' => 'Se guarda en mayúsculas.'])
    @include('partials.input', ['name' => 'name', 'label' => 'Nombre', 'value' => $subject->name, 'required' => true, 'maxlength' => 150])
    @include('partials.active-checkbox', ['value' => $subject->is_active])
    @include('partials.form-actions', ['cancel' => route('admin.subjects.index')])
</form>
</div></div>
@endsection
