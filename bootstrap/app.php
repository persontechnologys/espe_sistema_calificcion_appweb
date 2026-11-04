<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
        ]);
        $middleware->web(append: [SecurityHeaders::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Respaldo ante carreras: si dos solicitudes pasan la validación "unique" a la vez,
        // la restricción UNIQUE de la base de datos rechaza la segunda. Mismo manejo en
        // PostgreSQL y MySQL (Laravel lanza la misma excepción en ambos).
        $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return back()->withInput($request->except(['password', 'password_confirmation']))->withErrors([
                'general' => 'El registro ya existe: uno de sus códigos únicos está duplicado.',
            ]);
        });
    })->create();
