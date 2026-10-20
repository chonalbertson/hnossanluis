<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Su cuenta no cuenta con acceso activo al sistema.');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        foreach ($permissions as $permissionGroup) {
            foreach (preg_split('/[|,]/', $permissionGroup) ?: [] as $permission) {
                $trimmed = trim($permission);
                if ($trimmed !== '' && $user->hasPermission($trimmed)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'No cuenta con los permisos necesarios para acceder a este módulo.');
    }
}
