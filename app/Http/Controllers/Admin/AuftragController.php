<?php

/**
 * Controller: Admin\AuftragController
 *
 * Verwaltet die Zuweisung von Arbeitsauftraegen durch den Administrator.
 *
 * Ablauf:
 *   1. Admin waehlt Datum (muss heute oder in der Zukunft liegen)
 *   2. Seite laedt neu und zeigt verfuegbare Mitarbeitende fuer dieses Datum
 *   3. Admin waehlt Mitarbeitenden, Auftraggeber, Zeiten, Pause und Taetigkeit
 *   4. Nach dem Speichern erscheint der Auftrag mit Status "gesendet"
 *   5. Wenn Mitarbeitender bestaetigt -> Status "bestaetigt" + Zeiteintrag erstellt
 *
 * Zugriff: Nur Admin (Middleware: admin)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auftrag;
use App\Models\Auftraggeber;
use App\Models\Mitarbeiter;
use App\Models\Taetigkeit;
use App\Models\Zeiterfassung;
use Illuminate\Http\Request;

class AuftragController extends Controller
{
    /**
     * Zeigt alle Auftraege mit Filtermoeglickeiten.
     *
     * Filter: Status, Mitarbeitender, Auftraggeber, Monat/Jahr
     * Freigeben/Ablehnen-Aktionen sind ebenfalls hier integriert.
     *
     * @param Request $request HTTP-Anfrage mit optionalen Filterparametern
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Auftrag::with(['mitarbeiter.user', 'auftraggeber', 'taetigkeit'])
            ->orderByDesc('datum')
            ->orderByDesc('von');

        // Statusfilter: gesendet | bestaetigt | freigegeben | abgelehnt | alle
        $status = $request->get('status', 'alle');
        if ($status !== 'alle') {
            $query->where('status', $status);
        }

        // Mitarbeiterfilter
        $mitarbeiterId = $request->get('mitarbeiter_id');
        if ($mitarbeiterId) {
            $query->where('mitarbeiter_id', $mitarbeiterId);
        }

        // Auftraggeberfilter
        $auftraggeberId = $request->get('auftraggeber_id');
        if ($auftraggeberId) {
            $query->where('auftraggeber_id', $auftraggeberId);
        }

        // Monatsfilter aus getrennten Feldern (monat_nr + jahr)
        $filterJahr  = (int) $request->get('jahr',     now()->year);
        $filterMonat = (int) $request->get('monat_nr', now()->month);
        $monat = sprintf('%04d-%02d', $filterJahr, $filterMonat);
        $query->whereYear('datum', $filterJahr)->whereMonth('datum', $filterMonat);

        // Dynamische Jahresspanne: vom aeltesten Auftrag bis naechstes Jahr
        $aeltestesJahr = Auftrag::selectRaw('YEAR(MIN(datum)) as jahr')->value('jahr') ?? now()->year;
        $jahre = range(now()->year + 1, $aeltestesJahr);

        $auftraege    = $query->paginate(20);
        $mitarbeiter  = Mitarbeiter::with('user')->where('status', 'aktiv')->get();
        $auftraggeber = Auftraggeber::where('is_active', true)->orderBy('firmenname')->get();

        return view('admin.auftraege.index', compact(
            'auftraege', 'mitarbeiter', 'auftraggeber', 'status', 'monat',
            'mitarbeiterId', 'auftraggeberId', 'jahre'
        ));
    }

    /**
     * Gibt einen bestaetgten Auftrag frei.
     *
     * Aendert den Auftrag-Status auf "freigegeben" und aktualisiert
     * die zugehoerige Zeiterfassung ebenfalls auf "freigegeben".
     *
     * @param Auftrag $auftrag Der freizugebende Auftrag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function freigeben(Auftrag $auftrag)
    {
        // Auftrag-Status aktualisieren
        $auftrag->update(['status' => 'freigegeben']);

        // Passende Zeiterfassung (gleicher Mitarbeitender + Datum) ebenfalls freigeben
        Zeiterfassung::where('mitarbeiter_id', $auftrag->mitarbeiter_id)
            ->whereDate('datum', $auftrag->datum)
            ->where('status', 'offen')
            ->update(['status' => 'freigegeben']);

        return back()->with('success', 'Auftrag wurde freigegeben.');
    }

    /**
     * Lehnt einen bestaetgten Auftrag ab.
     *
     * Aendert den Auftrag-Status auf "abgelehnt" und aktualisiert
     * die zugehoerige Zeiterfassung ebenfalls auf "abgelehnt".
     *
     * @param Auftrag $auftrag Der abzulehnende Auftrag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ablehnen(Auftrag $auftrag)
    {
        // Auftrag-Status aktualisieren
        $auftrag->update(['status' => 'abgelehnt']);

        // Passende Zeiterfassung (gleicher Mitarbeitender + Datum) ebenfalls ablehnen
        Zeiterfassung::where('mitarbeiter_id', $auftrag->mitarbeiter_id)
            ->whereDate('datum', $auftrag->datum)
            ->where('status', 'offen')
            ->update(['status' => 'abgelehnt']);

        return back()->with('success', 'Auftrag wurde abgelehnt.');
    }

    /**
     * Gibt mehrere bestaetgte Auftraege auf einmal frei (Massenfreigabe).
     *
     * Erwartet ein Array von Auftrag-IDs im Request-Feld "eintraege[]".
     * Nur Auftraege mit Status "bestaetigt" werden verarbeitet.
     *
     * @param Request $request HTTP-Anfrage mit eintraege[]-Array
     * @return \Illuminate\Http\RedirectResponse
     */
    public function massenfreigabe(Request $request)
    {
        $ids = $request->input('eintraege', []);

        if (empty($ids)) {
            return back()->with('error', 'Keine Auftraege ausgewaehlt.');
        }

        // Nur bestaetgte Auftraege laden (Sicherheitspruefung)
        $auftraege = Auftrag::whereIn('id', $ids)
            ->where('status', 'bestaetigt')
            ->get();

        foreach ($auftraege as $auftrag) {
            // Auftrag freigeben
            $auftrag->update(['status' => 'freigegeben']);

            // Passende Zeiterfassung ebenfalls freigeben
            Zeiterfassung::where('mitarbeiter_id', $auftrag->mitarbeiter_id)
                ->whereDate('datum', $auftrag->datum)
                ->where('status', 'offen')
                ->update(['status' => 'freigegeben']);
        }

        return back()->with('success', $auftraege->count() . ' Auftrag/Auftraege freigegeben.');
    }

    /**
     * Zeigt das Formular zum Erstellen eines neuen Auftrags.
     *
     * Wenn ein Datum per GET-Parameter uebergeben wird, werden nur die
     * Mitarbeitenden angezeigt, die an diesem Tag noch keinen offenen
     * (gesendeten) Auftrag haben.
     *
     * @param Request $request HTTP-Anfrage (GET-Parameter: datum)
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Datum aus GET-Parameter oder heutiges Datum als Standardwert
        $datum = $request->get('datum', now()->format('Y-m-d'));

        // Sicherheitspruefung: Datum darf nicht in der Vergangenheit liegen
        if ($datum < now()->format('Y-m-d')) {
            $datum = now()->format('Y-m-d');
        }

        // Taetigkeiten und Auftraggeber laden
        $taetigkeiten = Taetigkeit::orderBy('reihenfolge')->orderBy('name')->get();
        $auftraggeber = Auftraggeber::where('is_active', true)->orderBy('firmenname')->get();

        // Mitarbeitende die an diesem Datum bereits einen gesendeten Auftrag haben
        $belegteIds = Auftrag::where('datum', $datum)
            ->where('status', 'gesendet')
            ->pluck('mitarbeiter_id');

        // Verfuegbare Mitarbeitende: aktiv und an diesem Tag noch nicht belegt
        $mitarbeiter = Mitarbeiter::with('user')
            ->where('status', 'aktiv')
            ->whereNotIn('id', $belegteIds)
            ->get();

        $today = now()->format('Y-m-d');

        return view('admin.auftraege.create', compact(
            'datum', 'taetigkeiten', 'auftraggeber', 'mitarbeiter', 'today'
        ));
    }

    /**
     * Speichert einen neuen Auftrag in der Datenbank.
     *
     * Validierungsregeln:
     * - Datum: Pflicht, gueltiges Datum, nicht in der Vergangenheit
     * - Mitarbeitender: Pflicht, muss existieren
     * - Auftraggeber: Pflicht, muss existieren
     * - Taetigkeit: Pflicht, muss existieren
     * - Von/Bis: Pflicht, gueltiges Zeitformat, Bis muss nach Von liegen
     * - Pause: Optional, Boolean
     *
     * @param Request $request HTTP-Anfrage mit Formulardaten
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Stunde und Minute aus getrennten Feldern zusammensetzen (Format: HH:MM)
        $von = $request->input('von_h', '00') . ':' . $request->input('von_m', '00');
        $bis = $request->input('bis_h', '00') . ':' . $request->input('bis_m', '00');

        // Temporaer in den Request einfuegen fuer Validierung
        $request->merge(['von' => $von, 'bis' => $bis]);

        $validated = $request->validate([
            'datum'           => ['required', 'date', 'after_or_equal:today'],
            'mitarbeiter_id'  => ['required', 'exists:mitarbeiter,id'],
            'auftraggeber_id' => ['required', 'exists:auftraggeber,id'],
            'taetigkeit_id'   => ['required', 'exists:taetigkeiten,id'],
            'von'             => ['required', 'date_format:H:i'],
            'bis'             => ['required', 'date_format:H:i', 'after:von'],
            'pause'           => ['nullable', 'boolean'],
        ], [
            'datum.after_or_equal'  => 'Das Datum darf nicht in der Vergangenheit liegen.',
            'bis.after'             => 'Die Endzeit muss nach der Startzeit liegen.',
            'mitarbeiter_id.exists' => 'Der ausgewaehlte Mitarbeitende existiert nicht.',
            'auftraggeber_id.exists'=> 'Der ausgewaehlte Auftraggeber existiert nicht.',
            'taetigkeit_id.exists'  => 'Die ausgewaehlte Taetigkeit existiert nicht.',
        ]);

        // Pause: Checkbox liefert keinen Wert wenn nicht angehaekt
        $validated['pause']  = $request->boolean('pause');
        $validated['status'] = 'gesendet';

        Auftrag::create($validated);

        return redirect()->route('admin.auftraege.index')
            ->with('success', 'Auftrag wurde erfolgreich gesendet.');
    }

    /**
     * Storniert einen noch nicht bestaetigen Auftrag (loescht ihn).
     *
     * Nur Auftraege mit Status "gesendet" koennen storniert werden.
     *
     * @param Auftrag $auftrag Der zu stornierende Auftrag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Auftrag $auftrag)
    {
        // Bereits bestaetgte Auftraege koennen nicht mehr storniert werden
        if ($auftrag->status !== 'gesendet') {
            return back()->with('error', 'Bereits bestaetgte Auftraege koennen nicht storniert werden.');
        }

        $auftrag->delete();

        return back()->with('success', 'Auftrag wurde storniert.');
    }
}
