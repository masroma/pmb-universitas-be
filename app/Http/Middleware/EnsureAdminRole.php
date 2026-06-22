<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($roles === [] || in_array($user->role, $roles, true) || $user->role === 'super_admin') {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke menu ini.');
    }
}
