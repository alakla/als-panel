<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMitarbeiter
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'mitarbeiter') {
            abort(403, 'Zugriff verweigert.');
        }

        return $next($request);
    }
}
