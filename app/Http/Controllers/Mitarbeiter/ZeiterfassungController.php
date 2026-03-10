<?php

namespace App\Http\Controllers\Mitarbeiter;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZeiterfassungRequest;
use App\Models\Auftraggeber;
use App\Models\Taetigkeit;
use App\Models\Zeiterfassung;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * ZeiterfassungController (Mitarbeiter-Bereich)
 *
 * Ermoeglicht Mitarbeitenden das Erfassen, Bearbeiten und Loeschen
 * ihrer eigenen taeglichen Arbeitszeiteintraege.
 *
 * Wichtig: Nur eigene Eintraege sind sichtbar und bearbeitbar.
 * Nur Eintraege mit Status 'offen' koennen bearbeitet oder geloescht werden,
 * da freigegebene/abgelehnte Eintraege bereits im Workflow sind.
 *
 * Zugriff: Nur Mitarbeitende (Middleware: auth + mitarbeiter)
 */
class ZeiterfassungController extends Controller
{
    /**
     * Zeigt alle Zeiteintraege des angemeldeten Mitarbeitenden.
     *
     * Unterstuetzt optionale Filterung nach Monat und Auftraggeber.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Mitarbeiter-Datensatz des aktuell angemeldeten Benutzers laden
        $mitarbeiter = auth()->user()->mitarbeiter;

        // Filter-Parameter aus der URL lesen
        $monat       = request('monat', now()->format('Y-m'));
        $auftraggeberId = request('auftraggeber_id');

        // Zeiteintraege des Mitarbeitenden laden, gefiltert und paginiert
        $zeiterfassungen = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->when($monat, function ($query) use ($monat) {
                // Nur Eintraege des gewaehlten Monats anzeigen
                $query->whereYear('datum', substr($monat, 0, 4))
                      ->whereMonth('datum', substr($monat, 5, 2));
            })
            ->when($auftraggeberId, function ($query) use ($auftraggeberId) {
                // Nur Eintraege fuer den gewaehlten Auftraggeber anzeigen
                $query->where('auftraggeber_id', $auftraggeberId);
            })
            ->with('auftraggeber')
            ->orderByDesc('datum')
            ->paginate(20);

        // Gesamtstunden des aktuellen Filters berechnen
        $gesamtstunden = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->when($monat, function ($query) use ($monat) {
                $query->whereYear('datum', substr($monat, 0, 4))
                      ->whereMonth('datum', substr($monat, 5, 2));
            })
            ->when($auftraggeberId, function ($query) use ($auftraggeberId) {
                $query->where('auftraggeber_id', $auftraggeberId);
            })
            ->sum('stunden');

        // Alle aktiven Auftraggeber fuer das Filter-Dropdown laden
        $auftraggeber = Auftraggeber::where('is_active', true)->orderBy('firmenname')->get();

        return view('mitarbeiter.zeiterfassung.index', compact(
            'zeiterfassungen', 'auftraggeber', 'monat', 'auftraggeberId', 'gesamtstunden'
        ));
    }

    /**
     * Zeigt das Formular zum Anlegen eines neuen Zeiteintrags.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        // Nur aktive Auftraggeber anzeigen (inaktive koennen keine Stunden buchen)
        $auftraggeber = Auftraggeber::where('is_active', true)->orderBy('firmenname')->get();

        // Vordefinierte Taetigkeiten aus der Datenbank laden (vom Admin verwaltbar)
        $taetigkeiten = Taetigkeit::orderBy('reihenfolge')->orderBy('name')->get();

        return view('mitarbeiter.zeiterfassung.create', compact('auftraggeber', 'taetigkeiten'));
    }

    /**
     * Speichert einen neuen Zeiteintrag in der Datenbank.
     *
     * Der Eintrag wird automatisch dem angemeldeten Mitarbeitenden zugeordnet
     * und erhaelt den Status 'offen' (noch nicht freigegeben).
     *
     * @param  \App\Http\Requests\ZeiterfassungRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ZeiterfassungRequest $request): RedirectResponse
    {
        // Mitarbeiter-Datensatz des aktuell angemeldeten Benutzers
        $mitarbeiter = auth()->user()->mitarbeiter;

        // Zeiteintrag anlegen und dem Mitarbeitenden zuordnen
        Zeiterfassung::create([
            'mitarbeiter_id'  => $mitarbeiter->id,
            'auftraggeber_id' => $request->auftraggeber_id,
            'datum'           => $request->datum,
            'stunden'         => $request->stunden,
            'beschreibung'    => $request->beschreibung,
            'status'          => 'offen', // Neu erstellte Eintraege sind immer 'offen'
        ]);

        return redirect()
            ->route('mitarbeiter.zeiterfassung.index')
            ->with('success', 'Zeiteintrag wurde erfolgreich gespeichert.');
    }

    /**
     * Zeigt das Bearbeitungsformular eines vorhandenen Zeiteintrags.
     *
     * Nur der Mitarbeitende, dem der Eintrag gehoert, darf ihn bearbeiten.
     * Freigegebene oder abgelehnte Eintraege koennen nicht mehr bearbeitet werden.
     *
     * @param  \App\Models\Zeiterfassung  $zeiterfassung
     * @return \Illuminate\View\View
     */
    public function edit(Zeiterfassung $zeiterfassung): View
    {
        // Sicherheitspruefung: Gehoert dieser Eintrag dem angemeldeten Mitarbeitenden?
        $this->authorizeEntry($zeiterfassung);

        // Nur offene Eintraege koennen bearbeitet werden
        if ($zeiterfassung->status !== 'offen') {
            abort(403, 'Freigegebene oder abgelehnte Eintraege koennen nicht mehr bearbeitet werden.');
        }

        // Aktive Auftraggeber fuer das Dropdown laden
        $auftraggeber = Auftraggeber::where('is_active', true)->orderBy('firmenname')->get();

        // Vordefinierte Taetigkeiten aus der Datenbank laden
        $taetigkeiten = Taetigkeit::orderBy('reihenfolge')->orderBy('name')->get();

        return view('mitarbeiter.zeiterfassung.edit', compact('zeiterfassung', 'auftraggeber', 'taetigkeiten'));
    }

    /**
     * Aktualisiert einen vorhandenen Zeiteintrag in der Datenbank.
     *
     * @param  \App\Http\Requests\ZeiterfassungRequest  $request
     * @param  \App\Models\Zeiterfassung                $zeiterfassung
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ZeiterfassungRequest $request, Zeiterfassung $zeiterfassung): RedirectResponse
    {
        // Sicherheitspruefung: Nur eigene offene Eintraege duerfen aktualisiert werden
        $this->authorizeEntry($zeiterfassung);

        if ($zeiterfassung->status !== 'offen') {
            abort(403, 'Dieser Eintrag kann nicht mehr bearbeitet werden.');
        }

        // Validierte Daten speichern
        $zeiterfassung->update($request->validated());

        return redirect()
            ->route('mitarbeiter.zeiterfassung.index')
            ->with('success', 'Zeiteintrag wurde erfolgreich aktualisiert.');
    }

    /**
     * Loescht einen Zeiteintrag aus der Datenbank.
     *
     * Nur offene Eintraege koennen geloescht werden.
     *
     * @param  \App\Models\Zeiterfassung  $zeiterfassung
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Zeiterfassung $zeiterfassung): RedirectResponse
    {
        // Sicherheitspruefung: Nur eigene offene Eintraege duerfen geloescht werden
        $this->authorizeEntry($zeiterfassung);

        if ($zeiterfassung->status !== 'offen') {
            abort(403, 'Freigegebene oder abgelehnte Eintraege koennen nicht geloescht werden.');
        }

        $zeiterfassung->delete();

        return redirect()
            ->route('mitarbeiter.zeiterfassung.index')
            ->with('success', 'Zeiteintrag wurde erfolgreich geloescht.');
    }

    /**
     * Hilfsmethode: Prueft, ob der angemeldete Mitarbeitende Eigentuemer des Eintrags ist.
     *
     * Wird in edit(), update() und destroy() aufgerufen, um sicherzustellen,
     * dass kein Mitarbeitender die Eintraege eines anderen einsehen oder veraendern kann.
     * Bricht mit HTTP 403 (Forbidden) ab, falls der Eintrag nicht dem Eingeloggten gehoert.
     *
     * @param  \App\Models\Zeiterfassung  $zeiterfassung  Der zu pruefende Eintrag
     */
    private function authorizeEntry(Zeiterfassung $zeiterfassung): void
    {
        $mitarbeiter = auth()->user()->mitarbeiter;

        if ($zeiterfassung->mitarbeiter_id !== $mitarbeiter->id) {
            abort(403, 'Zugriff verweigert.');
        }
    }
}
