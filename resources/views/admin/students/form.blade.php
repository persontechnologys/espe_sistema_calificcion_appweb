@extends('layouts.app')
@section('title', $student->exists ? 'Editar estudiante' : 'Nuevo estudiante')
@section('content')
<h1 class="h3 mb-3">{{ $student->exists ? 'Editar estudiante' : 'Nuevo estudiante' }}</h1>
<div class="card shadow-sm" style="max-width: 40rem"><div class="card-body">
<form method="POST" action="{{ $student->exists ? route('admin.students.update', $student) : route('admin.students.store') }}">
    @csrf
    @if ($student->exists) @method('PUT') @endif
    @include('partials.input', ['name' => 'institutional_id', 'label' => 'ID institucional', 'value' => $student->institutional_id, 'required' => true, 'maxlength' => 20, 'placeholder' => 'L00000000', 'help' => 'Texto (conserva letras y ceros iniciales). Se guarda en mayúsculas.'])
    @include('partials.input', ['name' => 'last_names', 'label' => 'Apellidos', 'value' => $student->last_names, 'required' => true, 'maxlength' => 100])
    @include('partials.input', ['name' => 'first_names', 'label' => 'Nombres', 'value' => $student->first_names, 'required' => true, 'maxlength' => 100])
    @include('partials.input', ['name' => 'email', 'label' => 'Correo (opcional)', 'type' => 'email', 'value' => $student->email, 'maxlength' => 255])
    @include('partials.active-checkbox', ['value' => $student->is_active])
    @include('partials.form-actions', ['cancel' => route('admin.students.index')])
</form>
</div></div>
@endsection
