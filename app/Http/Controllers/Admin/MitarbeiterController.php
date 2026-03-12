<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MitarbeiterRequest;
use App\Models\Lohnabrechnung;
use App\Models\Mitarbeiter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Unterstützt optionale Suche nach Name, E-Mail oder Personalnummer.
     * Die Ergebnisse werden paginiert (15 pro Seite).
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Suchanfrage aus dem Request holen (optional)
        $suche = request('suche');

        // Mitarbeitende mit zugehörigem User laden, optional gefiltert
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
        // Nächste freie Personalnummer vorschlagen:
        // Höchste numerische Personalnummer ermitteln und um 1 erhöhen
        $letzteNr = Mitarbeiter::selectRaw('MAX(CAST(personalnummer AS UNSIGNED)) as max_nr')->value('max_nr');
        $vorschlagNr = str_pad(($letzteNr ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        return view('admin.mitarbeiter.create', compact('vorschlagNr'));
    }

    /**
     * Speichert einen neuen Mitarbeitenden in der Datenbank.
     *
     * Ablauf (in einer Transaktion):
     * 1. Benutzerkonto (User) mit Rolle 'mitarbeiter' erstellen
     * 2. Mitarbeiter-Datensatz mit Verweis auf den User erstellen
     *
     * Bei einem Fehler wird die gesamte Transaktion zurückgerollt.
     *
     * @param  \App\Http\Requests\MitarbeiterRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(MitarbeiterRequest $request): RedirectResponse
    {
        // Datenbankoperation in einer Transaktion ausführen (Datenkonsistenz)
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
                'telefon'           => $request->telefon,
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
     * Zeigt alle Zeiterfassungen mit optionaler Filterung nach Status und Monat.
     * Berechnet das monatliche Gehalt (nur freigegeben-Stunden × Stundenlohn).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mitarbeiter   $mitarbeiter
     * @return \Illuminate\View\View
     */
    public function show(Request $request, Mitarbeiter $mitarbeiter): View
    {
        // Benutzerdaten laden
        $mitarbeiter->load('user');

        // Filterparameter aus dem Request lesen (Standardwerte: aktueller Monat + alle)
        // Statuswerte entsprechen Auftrag-Statussen (wie Aufträge-Seite)
        $filterStatus = $request->input('status', 'alle');

        // Sicherstellen dass nur erlaubte Statuswerte akzeptiert werden
        if (!in_array($filterStatus, ['alle', 'gesendet', 'bestaetigt', 'freigegeben', 'abgelehnt'])) {
            $filterStatus = 'alle';
        }

        // Monat und Jahr aus getrennten Feldern lesen (wie Aufträge-Seite)
        $filterJahr  = (int) $request->input('jahr',     now()->year);
        $filterMonat = (int) $request->input('monat_nr', now()->month);
        // Kompaktes Format für Vergleiche und View
        $monat = sprintf('%04d-%02d', $filterJahr, $filterMonat);

        // Monatsnamen auf Deutsch
        $monatsnamen = [1=>'Januar',2=>'Februar',3=>'März',4=>'April',5=>'Mai',6=>'Juni',
                        7=>'Juli',8=>'August',9=>'September',10=>'Oktober',11=>'November',12=>'Dezember'];
        $filterMonatLabel = $monatsnamen[$filterMonat] . ' ' . $filterJahr;

        // Verfügbare Jahre: vom ältesten Auftrag des Mitarbeitenden bis nächstes Jahr
        $aeltestesJahr = \App\Models\Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->selectRaw('YEAR(MIN(datum)) as jahr')
            ->value('jahr') ?? now()->year;
        $jahre = range(now()->year + 1, $aeltestesJahr);

        // Freigegeben-Stunden aus Aufträgen berechnen (immer nur freigegeben, unabhängig vom Statusfilter)
        $freigegebeneStunden = \App\Models\Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->where('status', 'freigegeben')
            ->whereYear('datum', $filterJahr)
            ->whereMonth('datum', $filterMonat)
            ->get(['von', 'bis', 'pause'])
            ->sum(fn($a) => $a->berechneteStunden());

        $monatsgehalt = $freigegebeneStunden * $mitarbeiter->stundenlohn;

        // Aufträge laden (wie Aufträge-Seite, nur für diesen Mitarbeitenden)
        $auftraege = \App\Models\Auftrag::with(['auftraggeber', 'taetigkeit'])
            ->where('mitarbeiter_id', $mitarbeiter->id)
            ->when($filterStatus !== 'alle', fn($q) => $q->where('status', $filterStatus))
            ->whereYear('datum', $filterJahr)
            ->whereMonth('datum', $filterMonat)
            ->orderByDesc('datum')
            ->orderByDesc('von')
            ->get();

        // Prüfen ob das Gehalt für diesen Monat bereits als bezahlt markiert wurde
        $lohnabrechnung = Lohnabrechnung::where('mitarbeiter_id', $mitarbeiter->id)
            ->where('monat', $monat)
            ->first();

        return view('admin.mitarbeiter.show', compact(
            'mitarbeiter',
            'auftraege',
            'filterStatus',
            'monat',
            'jahre',
            'filterMonatLabel',
            'freigegebeneStunden',
            'monatsgehalt',
            'lohnabrechnung'
        ));
    }

    /**
     * Zeigt das Bearbeitungsformular eines Mitarbeitenden.
     *
     * @param  \App\Models\Mitarbeiter  $mitarbeiter
     * @return \Illuminate\View\View
     */
    public function edit(Mitarbeiter $mitarbeiter): View
    {
        // User-Daten für das Formular mitladen
        $mitarbeiter->load('user');

        return view('admin.mitarbeiter.edit', compact('mitarbeiter'));
    }

    /**
     * Aktualisiert die Daten eines vorhandenen Mitarbeitenden.
     *
     * Aktualisiert sowohl den User (Name, E-Mail) als auch
     * den Mitarbeiter-Datensatz (Personalnummer, Stundenlohn, etc.).
     * Das Passwort wird nur geändert, wenn ein neues angegeben wurde.
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
                'telefon'           => $request->telefon,
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
     * Statt den Datensatz zu löschen, wird der Status umgeschaltet.
     * Deaktivierte Mitarbeitende können sich nicht mehr anmelden
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

    /**
     * Markiert das Monatsgehalt eines Mitarbeitenden als bezahlt.
     *
     * Erstellt einen Lohnabrechnung-Datensatz für den angegebenen Monat.
     * Bereits bezahlte Monate werden nicht überschrieben.
     *
     * @param  Request               $request     HTTP-Anfrage (monat = YYYY-MM)
     * @param  Mitarbeiter           $mitarbeiter Der Mitarbeitende
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bezahlen(Request $request, Mitarbeiter $mitarbeiter): RedirectResponse
    {
        // Monat aus dem Formular lesen und validieren
        $monat = $request->input('monat');
        if (!$monat || !preg_match('/^\d{4}-\d{2}$/', $monat)) {
            return back()->with('error', 'Ungültiger Monat.');
        }

        // Bereits bezahlte Monate nicht erneut markieren
        if (Lohnabrechnung::where('mitarbeiter_id', $mitarbeiter->id)->where('monat', $monat)->exists()) {
            return back()->with('error', 'Dieses Gehalt wurde bereits als bezahlt markiert.');
        }

        // Freigegebene Stunden und Betrag zum Zeitpunkt der Zahlung berechnen
        [$jahr, $monatNr] = explode('-', $monat);
        $stunden = \App\Models\Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->where('status', 'freigegeben')
            ->whereYear('datum', (int) $jahr)
            ->whereMonth('datum', (int) $monatNr)
            ->get(['von', 'bis', 'pause'])
            ->sum(fn($a) => $a->berechneteStunden());

        $betrag = round($stunden * $mitarbeiter->stundenlohn, 2);

        // Lohnabrechnung speichern
        Lohnabrechnung::create([
            'mitarbeiter_id' => $mitarbeiter->id,
            'monat'          => $monat,
            'stunden'        => $stunden,
            'betrag'         => $betrag,
            'bezahlt_am'     => now(),
        ]);

        return back()->with('success',
            'Gehalt für ' . $monat . ' wurde als bezahlt markiert (' . number_format($betrag, 2, ',', '.') . ' €).'
        );
    }
}
