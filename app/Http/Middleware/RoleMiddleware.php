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

        $has = $user->roles()->whereIn('name', $roles)->exists();
        if (!$has) abort(403, 'Forbidden');

        return $next($request);
    }
}
