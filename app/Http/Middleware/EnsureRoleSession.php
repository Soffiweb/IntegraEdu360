<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleSession
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $sessionUser = $request->session()->get('auth_user');

        if (! is_array($sessionUser)) {
            $targetRole = $allowedRoles[0] ?? null;

            return $targetRole && isset(integraEduRoles()[$targetRole])
                ? redirect()->route('roles.access', $targetRole)->with('error', 'Debe iniciar sesión para continuar.')
                : redirect()->route('auth.form')->with('error', 'Debe iniciar sesión para continuar.');
        }

        if (! in_array($sessionUser['role'] ?? null, $allowedRoles, true)) {
            $currentRole = $sessionUser['role'] ?? null;

            return $currentRole && isset(integraEduRoles()[$currentRole])
                ? redirect()->route('roles.dashboard', $currentRole)->with('error', 'Su sesión no tiene permiso para acceder a esta sección.')
                : redirect()->route('auth.form')->with('error', 'Su sesión no tiene permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
