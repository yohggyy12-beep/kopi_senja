<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->wajib_ganti_kata_sandi) {
            return response()->json([
                'message' => 'Anda harus mengganti kata sandi Anda sebelum melanjutkan.',
                'code' => 'password_change_required',
            ], 403);
        }
        return $next($request);
    }
}
