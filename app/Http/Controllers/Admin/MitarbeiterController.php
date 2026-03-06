<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MitarbeiterRequest;
use App\Models\Mitarbeiter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * MitarbeiterController – Verwaltung der Mitarbeitenden (CRUD)
 *
 * Dieser Controller stellt alle Funktionen zur Verwaltung
 * von Mitarbeitenden bereit:
 * - Anzeige aller Mitarbeitenden (mit Suche)
 * - Detailansicht eines Mitarbeitenden
 * - Anlegen neuer Mitarbeitender (inkl. Benutzerkonto)
 * - Bearbeiten vorhandener Mitarbeitender
 * - Deaktivieren und Reaktivieren von Mitarbeitenden
 *
 * Zugriff: Nur Administratoren (Middleware: auth + admin)
 *
 * Hinweis: Beim Anlegen eines Mitarbeitenden wird automatisch
 * ein Benutzerkonto (role='mitarbeiter') in der users-Tabelle erstellt.
 */
class MitarbeiterController extends Controller
{
    /**
     * Zeigt eine Liste aller Mitarbeitenden an.
     *
     * Unterstuetzt optionale Suche nach Name, E-Mail oder Personalnummer.
     * Die Ergebnisse werden paginiert (15 pro Seite).
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Suchanfrage aus dem Request holen (optional)
        $suche = request('suche');

        // Mitarbeitende mit zugehoerigem User laden, optional gefiltert
        $mitarbeiter = Mitarbeiter::with('user')
            ->when($suche, function ($query) use ($suche) {
                // Suche in Name, E-Mail (via User) und Personalnummer
                $query->whereHas('user', function ($q) use ($suche) {
                    $q->where('name', 'like', "%{$suche}%")
                      ->orWhere('email', 'like', "%{$suche}%");
                })->orWhere('personalnummer', 'like', "%{$suche}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.mitarbeiter.index', compact('mitarbeiter', 'suche'));
    }

    /**
     * Zeigt das Formular zum Anlegen eines neuen Mitarbeitenden.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('admin.mitarbeiter.create');
    }

    /**
     * Speichert einen neuen Mitarbeitenden in der Datenbank.
     *
     * Ablauf (in einer Transaktion):
     * 1. Benutzerkonto (User) mit Rolle 'mitarbeiter' erstellen
     * 2. Mitarbeiter-Datensatz mit Verweis auf den User erstellen
     *
     * Bei einem Fehler wird die gesamte Transaktion zurueckgerollt.
     *
     * @param  \App\Http\Requests\MitarbeiterRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(MitarbeiterRequest $request): RedirectResponse
    {
        // Datenbankoperation in einer Transaktion ausfuehren (Datenkonsistenz)
        DB::transaction(function () use ($request) {
            // Schritt 1: Benutzerkonto erstellen
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'mitarbeiter',
                'is_active' => true,
            ]);

            // Schritt 2: Mitarbeiter-Datensatz mit Verweis auf den User anlegen
            Mitarbeiter::create([
                'user_id'           => $user->id,
                'personalnummer'    => $request->personalnummer,
                'einstellungsdatum' => $request->einstellungsdatum,
                'stundenlohn'       => $request->stundenlohn,
                'status'            => 'aktiv',
            ]);
        });

        return redirect()
            ->route('admin.mitarbeiter.index')
            ->with('success', 'Mitarbeiter wurde erfolgreich angelegt.');
    }

    /**
     * Zeigt die Detailseite eines Mitarbeitenden.
     *
     * Laedt alle zugehoerigen Zeiterfassungen fuer die Uebersicht.
     *
     * @param  \App\Models\Mitarbeiter  $mitarbeiter
     * @return \Illuminate\View\View
     */
    public function show(Mitarbeiter $mitarbeiter): View
    {
        // Benutzerdaten und letzte 10 Zeiterfassungen laden
        $mitarbeiter->load(['user', 'zeiterfassungen.auftraggeber']);

        return view('admin.mitarbeiter.show', compact('mitarbeiter'));
    }

    /**
     * Zeigt das Bearbeitungsformular eines Mitarbeitenden.
     *
     * @param  \App\Models\Mitarbeiter  $mitarbeiter
     * @return \Illuminate\View\View
     */
    public function edit(Mitarbeiter $mitarbeiter): View
    {
        // User-Daten fuer das Formular mitladen
        $mitarbeiter->load('user');

        return view('admin.mitarbeiter.edit', compact('mitarbeiter'));
    }

    /**
     * Aktualisiert die Daten eines vorhandenen Mitarbeitenden.
     *
     * Aktualisiert sowohl den User (Name, E-Mail) als auch
     * den Mitarbeiter-Datensatz (Personalnummer, Stundenlohn, etc.).
     * Das Passwort wird nur geaendert, wenn ein neues angegeben wurde.
     *
     * @param  \App\Http\Requests\MitarbeiterRequest  $request
     * @param  \App\Models\Mitarbeiter  $mitarbeiter
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(MitarbeiterRequest $request, Mitarbeiter $mitarbeiter): RedirectResponse
    {
        DB::transaction(function () use ($request, $mitarbeiter) {
            // Benutzerdaten aktualisieren
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            // Passwort nur aktualisieren, wenn ein neues eingegeben wurde
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $mitarbeiter->user->update($userData);

            // Mitarbeiter-Stammdaten aktualisieren
            $mitarbeiter->update([
                'personalnummer'    => $request->personalnummer,
                'einstellungsdatum' => $request->einstellungsdatum,
                'stundenlohn'       => $request->stundenlohn,
            ]);
        });

        return redirect()
            ->route('admin.mitarbeiter.index')
            ->with('success', 'Mitarbeiter wurde erfolgreich aktualisiert.');
    }

    /**
     * Deaktiviert oder reaktiviert einen Mitarbeitenden.
     *
     * Statt den Datensatz zu loeschen, wird der Status umgeschaltet.
     * Deaktivierte Mitarbeitende koennen sich nicht mehr anmelden
     * (is_active = false im User-Konto).
     *
     * @param  \App\Models\Mitarbeiter  $mitarbeiter
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Mitarbeiter $mitarbeiter): RedirectResponse
    {
        DB::transaction(function () use ($mitarbeiter) {
            // Status umschalten: aktiv <-> inaktiv
            $neuerStatus = $mitarbeiter->status === 'aktiv' ? 'inaktiv' : 'aktiv';
            $mitarbeiter->update(['status' => $neuerStatus]);

            // Benutzerkonto entsprechend aktivieren/deaktivieren
            $mitarbeiter->user->update(['is_active' => $neuerStatus === 'aktiv']);
        });

        $aktion = $mitarbeiter->status === 'aktiv' ? 'deaktiviert' : 'reaktiviert';

        return redirect()
            ->route('admin.mitarbeiter.index')
            ->with('success', "Mitarbeiter wurde erfolgreich {$aktion}.");
    }
}
