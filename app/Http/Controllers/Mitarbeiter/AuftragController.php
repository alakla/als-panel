<?php

/**
 * Controller: Mitarbeiter\AuftragController
 *
 * Ermoeglicht Mitarbeitenden, ihre zugewiesenen Auftraege einzusehen
 * und nach Ausfuehrung zu bestaetigen.
 *
 * Ablauf fuer Mitarbeitende:
 *   1. Auftrag erscheint mit Status "gesendet" in der eigenen Liste
 *   2. Nach Ausfuehrung des Auftrags: "Bestaetigen"-Button klicken
 *   3. System erstellt automatisch einen Zeiteintrag (status = offen)
 *   4. Auftrag wechselt zu Status "bestaetigt"
 *   5. Admin sieht den Zeiteintrag in der Zeitfreigabe
 *
 * Zugriff: Nur Mitarbeitende (Middleware: mitarbeiter)
 */

namespace App\Http\Controllers\Mitarbeiter;

use App\Http\Controllers\Controller;
use App\Models\Auftrag;
use App\Models\Zeiterfassung;
use Illuminate\Http\Request;

class AuftragController extends Controller
{
    /**
     * Zeigt alle eigenen Auftraege des angemeldeten Mitarbeitenden.
     *
     * Zeigt neueste Auftraege zuerst.
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

        // Statusfilter: gesendet | bestaetigt | alle (Standard: alle)
        $status = $request->get('status', 'alle');
        if ($status !== 'alle') {
            $query->where('status', $status);
        }

        // Monatsfilter: Jahr und Monat aus separaten Select-Feldern lesen
        $filterJahr  = (int) $request->get('jahr',     now()->year);
        $filterMonat = (int) $request->get('monat_nr', now()->month);
        // Kompaktes Format fuer Zuruecksetzen-Vergleich in der View
        $monat = sprintf('%04d-%02d', $filterJahr, $filterMonat);
        $query->whereYear('datum', $filterJahr)->whereMonth('datum', $filterMonat);

        // Verfuegbare Jahre: vom aeltesten eigenen Auftrag bis zum naechsten Jahr
        $aeltestesJahr = Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->selectRaw('YEAR(MIN(datum)) as jahr')
            ->value('jahr') ?? now()->year;
        $jahre = range(now()->year + 1, $aeltestesJahr);

        $auftraege = $query->paginate(20);

        return view('mitarbeiter.auftraege.index', compact('auftraege', 'status', 'monat', 'jahre'));
    }

    /**
     * Bestaetigt einen Auftrag nach dessen Ausfuehrung.
     *
     * Aktionen:
     *   - Prueft ob Auftrag dem Mitarbeitenden gehoert (Sicherheit)
     *   - Prueft ob Status noch "gesendet" ist
     *   - Berechnet Arbeitsstunden (Von/Bis minus eventuelle Pause)
     *   - Erstellt automatisch einen Zeiteintrag mit Status "offen"
     *   - Setzt Auftrag-Status auf "bestaetigt"
     *
     * @param Auftrag $auftrag Der zu bestaetgende Auftrag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bestaetigen(Auftrag $auftrag)
    {
        $mitarbeiter = auth()->user()->mitarbeiter;

        // Sicherheitspruefung: Nur eigene Auftraege duerfen bestaetigt werden
        if ($auftrag->mitarbeiter_id !== $mitarbeiter->id) {
            abort(403, 'Zugriff verweigert.');
        }

        // Auftrag muss noch im Status "gesendet" sein
        if ($auftrag->status !== 'gesendet') {
            return back()->with('error', 'Dieser Auftrag wurde bereits bestaetigt.');
        }

        // Arbeitsstunden berechnen (mit optionalem Pausenabzug von 30 Min.)
        $stunden = $auftrag->berechneteStunden();

        // Automatisch Zeiteintrag erstellen (erscheint in Auftraege-Ansicht des Admins)
        // taetigkeit_id wird gespeichert, damit der Stundensatz bei der Rechnungsstellung
        // aus der Taetigkeit gelesen werden kann
        Zeiterfassung::create([
            'mitarbeiter_id'  => $mitarbeiter->id,
            'auftraggeber_id' => $auftrag->auftraggeber_id,
            'taetigkeit_id'   => $auftrag->taetigkeit_id,
            'datum'           => $auftrag->datum,
            'stunden'         => $stunden,
            // Beschreibung: Taetigkeitsname + Uhrzeit-Bereich
            'beschreibung'    => $auftrag->taetigkeit->name
                                 . ' (' . $auftrag->vonFormatiert()
                                 . ' – ' . $auftrag->bisFormatiert() . ')',
            'status'          => 'offen',
        ]);

        // Auftrag auf "bestaetigt" setzen
        $auftrag->update(['status' => 'bestaetigt']);

        return back()->with('success', 'Auftrag bestaetigt. Zeiteintrag wurde erstellt und wartet auf Freigabe.');
    }
}
