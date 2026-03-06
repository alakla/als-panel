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
use App\Http\Controllers\Admin\MitarbeiterController;
use App\Http\Controllers\Admin\AuftraggeberController;
use App\Http\Controllers\Admin\ZeitfreigabeController;
use App\Http\Controllers\Admin\RechnungController;
use App\Http\Controllers\Mitarbeiter\DashboardController as MitarbeiterDashboard;
use App\Http\Controllers\Mitarbeiter\ZeiterfassungController;
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
| - Admin       -> /admin/dashboard
| - Mitarbeiter -> /mitarbeiter/dashboard
*/
Route::get('/dashboard', function () {
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
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin-Dashboard: Uebersicht mit Kennzahlen
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Mitarbeiterverwaltung: Anlegen, Bearbeiten, Deaktivieren
    Route::resource('mitarbeiter', MitarbeiterController::class);

    // Mitarbeiter deaktivieren/reaktivieren (toggle)
    Route::patch('/mitarbeiter/{mitarbeiter}/toggle', [MitarbeiterController::class, 'destroy'])
        ->name('mitarbeiter.toggle');

    // Auftraggeberverwaltung: Anlegen, Bearbeiten, Deaktivieren
    Route::resource('auftraggeber', AuftraggeberController::class);

    // Auftraggeber deaktivieren/reaktivieren (toggle)
    Route::patch('/auftraggeber/{auftraggeber}/toggle', [AuftraggeberController::class, 'destroy'])
        ->name('auftraggeber.toggle');

    // Zeitfreigabe: Admin-Bereich fuer Genehmigung/Ablehnung von Zeiteintraegen
    Route::get('/zeitfreigabe', [ZeitfreigabeController::class, 'index'])->name('zeitfreigabe.index');
    Route::post('/zeitfreigabe/{zeiterfassung}/freigeben', [ZeitfreigabeController::class, 'freigeben'])
        ->name('zeitfreigabe.freigeben');
    Route::post('/zeitfreigabe/{zeiterfassung}/ablehnen', [ZeitfreigabeController::class, 'ablehnen'])
        ->name('zeitfreigabe.ablehnen');
    Route::post('/zeitfreigabe/massenfreigabe', [ZeitfreigabeController::class, 'massenfreigabe'])
        ->name('zeitfreigabe.massenfreigabe');

    // Rechnungsverwaltung: Erstellen, Anzeigen, PDF herunterladen
    Route::resource('rechnungen', RechnungController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['rechnungen' => 'rechnung']);
    Route::post('/rechnungen/vorschau', [RechnungController::class, 'vorschau'])->name('rechnungen.vorschau');
    Route::get('/rechnungen/{rechnung}/download', [RechnungController::class, 'download'])->name('rechnungen.download');
    Route::post('/rechnungen/{rechnung}/bezahlt', [RechnungController::class, 'alsBezahlt'])->name('rechnungen.bezahlt');

});

/*
|--------------------------------------------------------------------------
| Mitarbeiter-Bereich (/mitarbeiter/*)
|--------------------------------------------------------------------------
| Alle Routen in dieser Gruppe sind nur fuer Mitarbeitende zugaenglich.
*/
Route::middleware(['auth', 'mitarbeiter'])->prefix('mitarbeiter')->name('mitarbeiter.')->group(function () {

    // Mitarbeiter-Dashboard: Eigene Kennzahlen und letzte Eintraege
    Route::get('/dashboard', [MitarbeiterDashboard::class, 'index'])->name('dashboard');

    // Zeiterfassung: Mitarbeitende erfassen ihre eigenen Arbeitszeiten
    Route::resource('zeiterfassung', ZeiterfassungController::class)->except(['show']);

});

/*
|--------------------------------------------------------------------------
| Profilverwaltung (/profile)
|--------------------------------------------------------------------------
| Fuer alle angemeldeten Benutzer (Admin und Mitarbeiter).
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
*/
require __DIR__.'/auth.php';
