@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-sm-10 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Sistema de Gestión Académica</h1>
                <p class="text-muted small mb-4">Demostración técnica · Universidad de las Fuerzas Armadas - ESPE</p>

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                        @include('partials.field-error', ['name' => 'email'])
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input id="password" type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                        @include('partials.field-error', ['name' => 'password'])
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Ingresar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
