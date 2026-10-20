<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Su cuenta no cuenta con acceso activo al sistema.');
        }

        $parsedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (preg_split('/[|,]/', $roleGroup) ?: [] as $role) {
                $trimmed = trim($role);
                if ($trimmed !== '') {
                    $parsedRoles[] = $trimmed;
                }
            }
        }

        if ($user->isAdmin() || $user->hasRole($parsedRoles)) {
            return $next($request);
        }

        abort(403, 'No tiene el rol requerido para realizar esta acción.');
    }
}
