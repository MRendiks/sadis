<?php // app/Http/Middleware/EnsureSuperAdmin.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->hasRole('super_admin')) {
            abort(403,'Only super admin.');
        }
        return $next($request);
    }
}
