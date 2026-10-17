<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVenue
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isVenue()) {
            abort(403);
        }

        return $next($request);
    }
}
