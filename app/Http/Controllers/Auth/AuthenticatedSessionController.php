<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * AuthenticatedSessionController – Verwaltung der Benutzeranmeldung
 *
 * Dieser Controller ist verantwortlich für:
 * - Anzeige des Login-Formulars
 * - Verarbeitung der Anmeldedaten und Authentifizierung
 * - Abmeldung (Logout) des Benutzers
 *
 * Nach erfolgreicher Anmeldung wird der Benutzer je nach Rolle
 * automatisch auf das Admin- oder Mitarbeiter-Dashboard weitergeleitet.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Zeigt das Login-Formular an.
     *
     * @return \Illuminate\View\View  Die Login-Ansicht
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Verarbeitet die eingereichten Anmeldedaten.
     *
     * Schritte:
     * 1. Validierung der Eingaben (E-Mail + Passwort) via LoginRequest
     * 2. Authentifizierung des Benutzers
     * 3. Session-Erneuerung zum Schutz vor Session-Fixation-Angriffen
     * 4. Weiterleitung zum Dashboard (rollenabhängig via route 'dashboard')
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request  Validierter Login-Request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Benutzer authentifizieren (prüft E-Mail und Passwort)
        $request->authenticate();

        // Session-ID erneuern zum Schutz vor Session-Fixation
        $request->session()->regenerate();

        // Weiterleitung zum ursprünglich angefragten Ziel oder zum Dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Meldet den aktuell angemeldeten Benutzer ab.
     *
     * Schritte:
     * 1. Benutzer aus der Web-Guard abmelden
     * 2. Session invalidieren (alle Session-Daten löschen)
     * 3. CSRF-Token erneuern
     * 4. Weiterleitung zur Startseite (Login)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Benutzer aus der Session abmelden
        Auth::guard('web')->logout();

        // Session komplett invalidieren
        $request->session()->invalidate();

        // Neuen CSRF-Token generieren
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
