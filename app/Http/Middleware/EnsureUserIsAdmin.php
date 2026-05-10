<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || strtolower($user->role?->name ?? '') !== 'admin') {
            abort(403, 'You are not allowed to access this page.');
        }

        return $next($request);
    }
}
