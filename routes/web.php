<?php

/**
 * Web-Routen des ALS Panels
 *
 * Hier werden alle HTTP-Routen der Webanwendung definiert.
 * Die Routen sind nach Benutzerrollen gruppiert:
 *
 * - Oeffentliche Routen: Startseite, Login
 * - Admin-Bereich (/admin/*): Nur fuer Benutzer mit Rolle 'admin'
 * - Mitarbeiter-Bereich (/mitarbeiter/*): Nur fuer Benutzer mit Rolle 'mitarbeiter'
 * - Profil (/profile): Fuer alle angemeldeten Benutzer
 *
 * Sicherheit:
 * - 'auth': Nur angemeldete Benutzer haben Zugriff
 * - 'admin': Zusaetzliche Pruefung: Benutzer muss Admin-Rolle haben
 * - 'mitarbeiter': Zusaetzliche Pruefung: Benutzer muss Mitarbeiter-Rolle haben
 */

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Startseite
|--------------------------------------------------------------------------
| Die Startseite leitet direkt zur Login-Seite weiter.
| Nicht angemeldete Benutzer sehen nie eine leere Seite.
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Dashboard-Weiterleitung (rollenabhaengig)
|--------------------------------------------------------------------------
| Nach dem Login landet der Benutzer auf /dashboard.
| Von hier wird er automatisch zum rollenspezifischen Dashboard weitergeleitet:
| - Admin    -> /admin/dashboard
| - Mitarbeiter -> /mitarbeiter/dashboard
*/
Route::get('/dashboard', function () {
    // Rollenbasierte Weiterleitung nach dem Login
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('mitarbeiter.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin-Bereich (/admin/*)
|--------------------------------------------------------------------------
| Alle Routen in dieser Gruppe sind nur fuer Administratoren zugaenglich.
| Middleware 'admin' prueft die Rolle des angemeldeten Benutzers.
|
| Hier werden spaeter folgende Bereiche hinzugefuegt:
| - Mitarbeiterverwaltung
| - Auftraggeberverwaltung
| - Zeitfreigabe
| - Rechnungsverwaltung
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin-Dashboard: Uebersicht mit Kennzahlen
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| Mitarbeiter-Bereich (/mitarbeiter/*)
|--------------------------------------------------------------------------
| Alle Routen in dieser Gruppe sind nur fuer Mitarbeitende zugaenglich.
| Middleware 'mitarbeiter' prueft die Rolle des angemeldeten Benutzers.
|
| Hier werden spaeter folgende Bereiche hinzugefuegt:
| - Eigene Zeiterfassung (Anzeige und Erfassung)
*/
Route::middleware(['auth', 'mitarbeiter'])->prefix('mitarbeiter')->name('mitarbeiter.')->group(function () {

    // Mitarbeiter-Dashboard: Eigene Zeiteintraege und Status
    Route::get('/dashboard', function () {
        return view('mitarbeiter.dashboard');
    })->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| Profilverwaltung (/profile)
|--------------------------------------------------------------------------
| Fuer alle angemeldeten Benutzer (Admin und Mitarbeiter).
| Ermoeglicht das Bearbeiten und Loeschen des eigenen Kontos.
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Authentifizierungsrouten (von Laravel Breeze generiert)
|--------------------------------------------------------------------------
| Enthaelt: Login, Logout, Registrierung, Passwort-Reset, E-Mail-Verifizierung
*/
require __DIR__.'/auth.php';
