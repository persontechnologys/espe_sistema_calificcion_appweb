<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uso: ->middleware('role:admin'). La autorización por registro se hace en Policies.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user()?->role;

        abort_unless($role instanceof UserRole && in_array($role->value, $roles, true), 403);

        return $next($request);
    }
}
