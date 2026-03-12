<?php

/**
 * Controller: Mitarbeiter\AuftragController
 *
 * Ermöglicht Mitarbeitenden, ihre zugewiesenen Aufträge einzusehen
 * und nach Ausführung zu bestätigen.
 *
 * Ablauf für Mitarbeitende:
 *   1. Auftrag erscheint mit Status "gesendet" in der eigenen Liste
 *   2. Nach Ausführung des Auftrags: "Bestätigen"-Button klicken
 *   3. System erstellt automatisch einen Zeiteintrag (status = offen)
 *   4. Auftrag wechselt zu Status "bestätigt"
 *   5. Admin sieht den Zeiteintrag in der Zeitfreigabe
 *
 * Zugriff: Nur Mitarbeitende (Middleware: mitarbeiter)
 */

namespace App\Http\Controllers\Mitarbeiter;

use App\Http\Controllers\Controller;
use App\Models\Auftrag;
use App\Models\Zeiterfassung;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuftragController extends Controller
{
    /**
     * Zeigt alle eigenen Aufträge des angemeldeten Mitarbeitenden.
     *
     * Zeigt neueste Aufträge zuerst.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mitarbeitenden-Datensatz des angemeldeten Benutzers holen
        $mitarbeiter = auth()->user()->mitarbeiter;

        $query = Auftrag::with(['auftraggeber', 'taetigkeit'])
            ->where('mitarbeiter_id', $mitarbeiter->id)
            ->orderByDesc('datum')
            ->orderByDesc('von');

        // Statusfilter: gesendet | bestätigt | alle (Standard: alle)
        $status = $request->get('status', 'alle');
        if ($status !== 'alle') {
            $query->where('status', $status);
        }

        // Monatsfilter: Jahr und Monat aus separaten Select-Feldern lesen
        $filterJahr  = (int) $request->get('jahr',     now()->year);
        $filterMonat = (int) $request->get('monat_nr', now()->month);
        // Kompaktes Format für Zurücksetzen-Vergleich in der View
        $monat = sprintf('%04d-%02d', $filterJahr, $filterMonat);
        $query->whereYear('datum', $filterJahr)->whereMonth('datum', $filterMonat);

        // Verfügbare Jahre: vom ältesten eigenen Auftrag bis zum nächsten Jahr
        $aeltestesJahr = Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->selectRaw('YEAR(MIN(datum)) as jahr')
            ->value('jahr') ?? now()->year;
        $jahre = range(now()->year + 1, $aeltestesJahr);

        // Freigegebene Stunden für den gewählten Monat (unabhängig vom Statusfilter)
        $gesamtStunden = Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->where('status', 'freigegeben')
            ->whereYear('datum', $filterJahr)
            ->whereMonth('datum', $filterMonat)
            ->get(['von', 'bis', 'pause'])
            ->sum(fn($a) => $a->berechneteStunden());

        $auftraege = $query->paginate(20);

        // Anzahl pro Status für den aktuellen Monat (unabhängig vom Statusfilter)
        $statusCounts = Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->whereYear('datum', $filterJahr)
            ->whereMonth('datum', $filterMonat)
            ->selectRaw('status, COUNT(*) as anzahl')
            ->groupBy('status')
            ->pluck('anzahl', 'status');

        return view('mitarbeiter.auftraege.index', compact('auftraege', 'status', 'monat', 'jahre', 'gesamtStunden', 'statusCounts'));
    }

    /**
     * Bestätigt einen Auftrag nach dessen Ausführung.
     *
     * Aktionen:
     *   - Prüft ob der Auftrag noch existiert (Race-Condition: Admin könnte ihn zwischenzeitlich storniert haben)
     *   - Prüft ob Auftrag dem Mitarbeitenden gehört (Sicherheit)
     *   - Prüft ob Status noch "gesendet" ist
     *   - Berechnet Arbeitsstunden (Von/Bis minus eventuelle Pause)
     *   - Erstellt automatisch einen Zeiteintrag mit Status "offen"
     *   - Setzt Auftrag-Status auf "bestätigt"
     *
     * @param int $auftrag Die ID des zu bestätigenden Auftrags (als Integer, nicht als Model –
     *                     damit bei gelöschtem Auftrag kein 404 geworfen wird)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bestaetigen(int $auftrag)
    {
        $mitarbeiter = auth()->user()->mitarbeiter;

        // Auftrag manuell suchen (nicht per Route-Model-Binding), damit ein gelöschter
        // Auftrag (storniert vom Admin) einen freundlichen Fehler statt einem 404 liefert
        $eintrag = Auftrag::find($auftrag);

        if (! $eintrag) {
            return back()->with('error', 'Dieser Auftrag wurde vom Administrator storniert und ist nicht mehr verfügbar.');
        }

        // Sicherheitsprüfung: Nur eigene Aufträge dürfen bestätigt werden
        if ($eintrag->mitarbeiter_id !== $mitarbeiter->id) {
            abort(403, 'Zugriff verweigert.');
        }

        // Auftrag muss noch im Status "gesendet" sein
        if ($eintrag->status !== 'gesendet') {
            return back()->with('error', 'Dieser Auftrag kann nicht mehr bestätigt werden (Status: ' . $eintrag->status . ').');
        }

        // Sicherheitsprüfung: Bestätigung erst nach Arbeitsende erlaubt.
        // Vergleich erfolgt als Strings (HH:MM) im App-Zeitzone, um UTC-Versatz zu vermeiden.
        // Für vergangene Daten entfällt die Zeitprüfung.
        $heute      = now()->format('Y-m-d');
        $auftragTag = $eintrag->datum->format('Y-m-d');
        if ($auftragTag === $heute && now()->format('H:i') < $eintrag->bisFormatiert()) {
            return back()->with('error',
                'Der Auftrag kann erst nach Arbeitsende (' . $eintrag->bisFormatiert() . ' Uhr) bestätigt werden.'
            );
        }

        // Eingaben des Mitarbeitenden validieren (Von/Bis können geändert worden sein)
        $request  = request();
        $validiert = $request->validate([
            'von'   => ['required', 'date_format:H:i'],
            'bis'   => ['required', 'date_format:H:i', 'after:von'],
            'pause' => ['nullable', 'boolean'],
        ], [
            'von.required'   => 'Startzeit ist erforderlich.',
            'bis.required'   => 'Endzeit ist erforderlich.',
            'bis.after'      => 'Die Endzeit muss nach der Startzeit liegen.',
        ]);
        $validiert['pause'] = $request->boolean('pause');

        // Originale Zeiten merken (für Änderungsvergleich)
        $originalVon   = substr($eintrag->von,  0, 5);
        $originalBis   = substr($eintrag->bis,  0, 5);
        $originalPause = $eintrag->pause;

        // Erkennung: Hat der Mitarbeitende die Zeiten abgeändert?
        $zeitGeaendert = $validiert['von']   !== $originalVon
                      || $validiert['bis']   !== $originalBis
                      || $validiert['pause'] !== $originalPause;

        // Lesbare Beschreibung der Änderungen aufbauen (für Admin-Tooltip)
        $aenderungInfo = null;
        if ($zeitGeaendert) {
            $teile = [];
            if ($validiert['von'] !== $originalVon) {
                $teile[] = 'Von: ' . $originalVon . ' → ' . $validiert['von'];
            }
            if ($validiert['bis'] !== $originalBis) {
                $teile[] = 'Bis: ' . $originalBis . ' → ' . $validiert['bis'];
            }
            if ($validiert['pause'] !== $originalPause) {
                $teile[] = 'Pause: ' . ($originalPause ? 'Ja' : 'Nein')
                         . ' → ' . ($validiert['pause'] ? 'Ja' : 'Nein');
            }
            $aenderungInfo = implode(' | ', $teile);
        }

        // Auftrag mit den (möglicherweise geänderten) Zeiten und Status "bestätigt" speichern
        $eintrag->update([
            'von'                 => $validiert['von'],
            'bis'                 => $validiert['bis'],
            'pause'               => $validiert['pause'],
            'zeit_geaendert'      => $zeitGeaendert,
            'zeit_aenderung_info' => $aenderungInfo,
            'status'              => 'bestaetigt',
        ]);

        // Stunden nach den aktualisierten Zeiten berechnen
        $stunden = $eintrag->fresh()->berechneteStunden();

        // Beschreibung: bei Änderung Original-Zeiten als Hinweis für den Admin vermerken
        $beschreibung = $eintrag->taetigkeit->name
                        . ' (' . $validiert['von'] . ' – ' . $validiert['bis'] . ')';
        if ($zeitGeaendert) {
            $beschreibung .= ' [Original: ' . $originalVon . ' – ' . $originalBis
                           . ($originalPause ? ', Pause 30 Min.' : '') . ']';
        }

        // Automatisch Zeiteintrag erstellen (erscheint in Aufträge-Ansicht des Admins)
        Zeiterfassung::create([
            'mitarbeiter_id'  => $mitarbeiter->id,
            'auftraggeber_id' => $eintrag->auftraggeber_id,
            'taetigkeit_id'   => $eintrag->taetigkeit_id,
            'datum'           => $eintrag->datum,
            'stunden'         => $stunden,
            'beschreibung'    => $beschreibung,
            'status'          => 'offen',
        ]);

        return back()->with('success', 'Auftrag bestätigt. Zeiteintrag wurde erstellt und wartet auf Freigabe.');
    }
}
