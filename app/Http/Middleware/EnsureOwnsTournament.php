<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnsTournament
{
    public function handle(Request $request, Closure $next): Response
    {
        $tournament = $request->route('tournament');

        abort_unless(
            $tournament && (int) $tournament->created_by === (int) $request->user()?->id,
            403,
            'Kamu hanya bisa mengelola turnamen yang kamu buat sendiri.'
        );

        return $next($request);
    }
}
