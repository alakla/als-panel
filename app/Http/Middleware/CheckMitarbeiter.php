<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMitarbeiter
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Nur angemeldete Mitarbeitende mit aktiver Rolle haben Zugriff
        if (!$user || $user->role !== 'mitarbeiter') {
            abort(403, 'Zugriff verweigert.');
        }

        // Deaktivierte Mitarbeitende werden ausgesperrt und abgemeldet
        if (!$user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withErrors(['email' => 'Ihr Konto wurde deaktiviert. Bitte wenden Sie sich an den Administrator.']);
        }

        return $next($request);
    }
}
