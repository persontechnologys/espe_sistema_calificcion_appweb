@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')
<div class="auth-grid">
    <section class="auth-hero" aria-label="Sistema de Gestión Académica">
        <div class="auth-brand">
            <x-ui.brand-mark size="lg" />
            <span class="auth-brand-name">Sistema de Gestión<br>Académica</span>
        </div>
        <div class="auth-hero-copy">
            <h2>Calificaciones claras, trazables y a tiempo.</h2>
            <p>Gestione períodos, ofertas por NRC, matrículas y los tres parciales de cada estudiante, con cálculo y auditoría en el servidor.</p>
            <ul class="auth-hero-list">
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i>Registro de 1P, 2P y 3P con validación de rango</li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i>Reportes imprimibles por período y NRC</li>
                <li><i class="bi bi-check2-circle" aria-hidden="true"></i>Historial de cambios de cada calificación</li>
            </ul>
        </div>
        <div class="auth-hero-note d-none d-lg-block">Gestión de calificaciones por período académico y NRC.</div>
    </section>

    <div class="auth-main">
        <main class="auth-form-wrap">
            <div class="auth-form">
                <h1>Bienvenido</h1>
                <p class="text-muted mb-4">Ingresa tus credenciales para continuar.</p>

                {{-- Credenciales inválidas, usuario inactivo o demasiados intentos (clave "email"). --}}
                @error('email')
                    <div id="email-error" class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                        <i class="bi bi-exclamation-octagon-fill mt-1" aria-hidden="true"></i>
                        <div>{{ $message }}</div>
                    </div>
                @enderror

                <form method="POST" action="{{ route('login') }}" novalidate data-validate>
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255"
                                   class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username"
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-lock" aria-hidden="true"></i></span>
                            <input id="password" type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" data-password-toggle="password"
                                    aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="password">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <x-form.error name="password" />
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>Iniciar sesión
                    </button>
                </form>
            </div>
        </main>
        <footer class="auth-footer">
            Sistema de Gestión Académica · <x-ui.attribution />
        </footer>
    </div>
</div>
@endsection
