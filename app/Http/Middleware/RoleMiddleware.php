<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) abort(401);

        // dukung "role:admin_arsip,super_admin"
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $roles = array_map('trim', explode(',', $roles[0]));
        }

        if (method_exists($user, 'hasAnyRole')) {
            if (!$user->hasAnyRole($roles)) abort(403, 'Forbidden');
        } else {
            $ok = false;
            foreach ($roles as $r) {
                if (method_exists($user,'hasRole') && $user->hasRole($r)) { $ok = true; break; }
            }
            if (!$ok) abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
