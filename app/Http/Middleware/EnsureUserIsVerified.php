<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->verified_at === null) {
            abort(403, 'Akun Anda belum diverifikasi admin mitra.');
        }

        return $next($request);
    }
}