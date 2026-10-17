<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlayerEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'player' && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('error', 'Verifikasi email dulu sebelum bisa bertanding. Cek kotak masuk email kamu.');
        }

        return $next($request);
    }
}
