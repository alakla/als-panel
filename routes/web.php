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
use App\Http\Controllers\Admin\RechnungController;
use App\Http\Controllers\Admin\TaetigkeitController;
use App\Http\Controllers\Admin\AuftragController;
use App\Http\Controllers\Mitarbeiter\DashboardController as MitarbeiterDashboard;
use App\Http\Controllers\Mitarbeiter\ZeiterfassungController;
use App\Http\Controllers\Mitarbeiter\AuftragController as MitarbeiterAuftragController;
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

    // Rechnungsverwaltung: Erstellen, Anzeigen, PDF herunterladen
    Route::resource('rechnungen', RechnungController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['rechnungen' => 'rechnung']);
    Route::post('/rechnungen/vorschau', [RechnungController::class, 'vorschau'])->name('rechnungen.vorschau');
    Route::get('/rechnungen/{rechnung}/download', [RechnungController::class, 'download'])->name('rechnungen.download');
    Route::post('/rechnungen/{rechnung}/bezahlt', [RechnungController::class, 'alsBezahlt'])->name('rechnungen.bezahlt');

    // Taetigkeitenverwaltung: Vordefinierte Beschreibungen fuer Zeiterfassung
    Route::resource('taetigkeiten', TaetigkeitController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['taetigkeiten' => 'taetigkeit']);

    // Auftragsverwaltung: Admin sendet, verwaltet und gibt Auftraege frei
    Route::get('/auftraege', [AuftragController::class, 'index'])->name('auftraege.index');
    Route::get('/auftraege/create', [AuftragController::class, 'create'])->name('auftraege.create');
    Route::post('/auftraege', [AuftragController::class, 'store'])->name('auftraege.store');
    Route::delete('/auftraege/{auftrag}', [AuftragController::class, 'destroy'])->name('auftraege.destroy');
    Route::post('/auftraege/{auftrag}/freigeben', [AuftragController::class, 'freigeben'])->name('auftraege.freigeben');
    Route::post('/auftraege/{auftrag}/ablehnen', [AuftragController::class, 'ablehnen'])->name('auftraege.ablehnen');
    Route::post('/auftraege/massenfreigabe', [AuftragController::class, 'massenfreigabe'])->name('auftraege.massenfreigabe');

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

    // Auftraege: Mitarbeitende sehen ihre Auftraege und koennen sie bestaetigen
    Route::get('/auftraege', [MitarbeiterAuftragController::class, 'index'])->name('auftraege.index');
    Route::patch('/auftraege/{auftrag}/bestaetigen', [MitarbeiterAuftragController::class, 'bestaetigen'])->name('auftraege.bestaetigen');

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
