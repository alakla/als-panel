<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auftraggeber;
use App\Models\Auftrag;
use App\Models\Mitarbeiter;
use App\Models\Zeiterfassung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ZeitfreigabeController (Admin-Bereich)
 *
 * Ermöglicht Administratoren das Freigeben oder Ablehnen von
 * Zeiteinträgen der Mitarbeitenden.
 *
 * Workflow:
 * - Mitarbeiter erstellt Zeiteintrag (Status: 'offen')
 * - Admin sieht alle offenen Einträge in dieser Übersicht
 * - Admin gibt frei (Status: 'freigegeben') oder lehnt ab (Status: 'abgelehnt')
 * - Freigegebene Einträge werden später für die Rechnungsstellung verwendet
 *
 * Zugriff: Nur Administratoren (Middleware: auth + admin)
 */
class ZeitfreigabeController extends Controller
{
    /**
     * Zeigt alle Zeiteinträge zur Freigabe.
     *
     * Unterstützt Filterung nach Status, Mitarbeiter und Auftraggeber.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Filter-Parameter aus der URL lesen.
        // Standard: 'offen' beim ersten Besuch.
        // 'alle' = kein Statusfilter (alle Einträge anzeigen).
        $status         = request('status', 'offen');
        $mitarbeiterId  = request('mitarbeiter_id');
        $auftraggeberId = request('auftraggeber_id');
        // Standard: aktueller Monat im Format Y-m (z.B. 2026-03)
        $monat          = request('monat', now()->format('Y-m'));

        $query = Zeiterfassung::with(['mitarbeiter.user', 'auftraggeber']);

        // Status-Filter: 'alle' bedeutet kein Filter – sonst nach Status filtern
        if ($status !== 'alle') {
            $query->where('status', $status);
        }

        // Mitarbeiter-Filter
        if ($mitarbeiterId) {
            $query->where('mitarbeiter_id', $mitarbeiterId);
        }

        // Auftraggeber-Filter
        if ($auftraggeberId) {
            $query->where('auftraggeber_id', $auftraggeberId);
        }

        // Monats-Filter
        if ($monat) {
            $query->whereYear('datum', substr($monat, 0, 4))
                  ->whereMonth('datum', substr($monat, 5, 2));
        }

        $zeiterfassungen = $query->orderByDesc('datum')->paginate(25);

        // Dropdown-Daten für die Filter laden
        $mitarbeiter  = Mitarbeiter::with('user')->orderBy('id')->get();
        $auftraggeber = Auftraggeber::where('is_active', true)->orderBy('firmenname')->get();

        return view('admin.zeitfreigabe.index', compact(
            'zeiterfassungen', 'mitarbeiter', 'auftraggeber',
            'status', 'mitarbeiterId', 'auftraggeberId', 'monat'
        ));
    }

    /**
     * Gibt einen Zeiteintrag frei.
     *
     * Setzt den Status auf 'freigegeben' und speichert, wer und wann
     * die Freigabe erteilt wurde.
     *
     * @param  \App\Models\Zeiterfassung  $zeiterfassung
     * @return \Illuminate\Http\RedirectResponse
     */
    public function freigeben(Zeiterfassung $zeiterfassung): RedirectResponse
    {
        // Nur offene Einträge können freigegeben werden
        if ($zeiterfassung->status !== 'offen') {
            return back()->with('error', 'Dieser Eintrag wurde bereits bearbeitet.');
        }

        // Freigabe speichern: Status, freigebender Admin und Zeitstempel
        $zeiterfassung->update([
            'status'          => 'freigegeben',
            'freigegeben_von' => auth()->id(),
            'freigegeben_am'  => now(),
        ]);

        // Dazugehörigen Auftrag ebenfalls auf 'freigegeben' setzen
        Auftrag::where('mitarbeiter_id', $zeiterfassung->mitarbeiter_id)
            ->whereDate('datum', $zeiterfassung->datum)
            ->where('status', 'bestaetigt')
            ->update(['status' => 'freigegeben']);

        return back()->with('success', 'Zeiteintrag wurde freigegeben.');
    }

    /**
     * Lehnt einen Zeiteintrag ab.
     *
     * Setzt den Status auf 'abgelehnt'. Der Mitarbeitende wird über
     * den Status informiert und kann einen neuen Eintrag erstellen.
     *
     * @param  \App\Models\Zeiterfassung  $zeiterfassung
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ablehnen(Zeiterfassung $zeiterfassung): RedirectResponse
    {
        // Nur offene Einträge können abgelehnt werden
        if ($zeiterfassung->status !== 'offen') {
            return back()->with('error', 'Dieser Eintrag wurde bereits bearbeitet.');
        }

        // Ablehnung speichern
        $zeiterfassung->update([
            'status'          => 'abgelehnt',
            'freigegeben_von' => auth()->id(),
            'freigegeben_am'  => now(),
        ]);

        // Dazugehörigen Auftrag ebenfalls auf 'abgelehnt' setzen
        Auftrag::where('mitarbeiter_id', $zeiterfassung->mitarbeiter_id)
            ->whereDate('datum', $zeiterfassung->datum)
            ->where('status', 'bestaetigt')
            ->update(['status' => 'abgelehnt']);

        return back()->with('success', 'Zeiteintrag wurde abgelehnt.');
    }

    /**
     * Gibt mehrere Zeiteinträge auf einmal frei (Massenfreigabe).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function massenfreigabe(Request $request): RedirectResponse
    {
        // IDs der ausgewählten Einträge validieren
        $request->validate([
            'eintraege'   => ['required', 'array', 'min:1'],
            'eintraege.*' => ['integer', 'exists:zeiterfassungen,id'],
        ]);

        // Betroffene Zeiterfassungen vorher laden (für Auftrag-Synchronisierung)
        $betroffene = Zeiterfassung::whereIn('id', $request->eintraege)
            ->where('status', 'offen')
            ->get(['mitarbeiter_id', 'datum']);

        // Alle ausgewählten offenen Einträge auf einmal freigeben
        $anzahl = Zeiterfassung::whereIn('id', $request->eintraege)
            ->where('status', 'offen')
            ->update([
                'status'          => 'freigegeben',
                'freigegeben_von' => auth()->id(),
                'freigegeben_am'  => now(),
            ]);

        // Dazugehörige Aufträge ebenfalls auf 'freigegeben' setzen
        foreach ($betroffene as $ze) {
            Auftrag::where('mitarbeiter_id', $ze->mitarbeiter_id)
                ->whereDate('datum', $ze->datum)
                ->where('status', 'bestaetigt')
                ->update(['status' => 'freigegeben']);
        }

        return back()->with('success', "{$anzahl} Zeiteintrag/Einträge wurden freigegeben.");
    }
}
