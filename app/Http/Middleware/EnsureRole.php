<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if (!$user || $user->peran !== $role){
            abort(403, 'mohon maaf anda tidak memiliki akses ke halaman ini');
        }
        return $next($request);
    }
}
