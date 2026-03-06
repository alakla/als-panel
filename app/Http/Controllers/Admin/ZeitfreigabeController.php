<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auftraggeber;
use App\Models\Mitarbeiter;
use App\Models\Zeiterfassung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ZeitfreigabeController (Admin-Bereich)
 *
 * Ermoeglicht Administratoren das Freigeben oder Ablehnen von
 * Zeiteintraegen der Mitarbeitenden.
 *
 * Workflow:
 * - Mitarbeiter erstellt Zeiteintrag (Status: 'offen')
 * - Admin sieht alle offenen Eintraege in dieser Uebersicht
 * - Admin gibt frei (Status: 'freigegeben') oder lehnt ab (Status: 'abgelehnt')
 * - Freigegebene Eintraege werden spaeter fuer die Rechnungsstellung verwendet
 *
 * Zugriff: Nur Administratoren (Middleware: auth + admin)
 */
class ZeitfreigabeController extends Controller
{
    /**
     * Zeigt alle Zeiteintraege zur Freigabe.
     *
     * Unterstuetzt Filterung nach Status, Mitarbeiter und Auftraggeber.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Filter-Parameter aus der URL lesen.
        // Standard: 'offen' beim ersten Besuch.
        // 'alle' = kein Statusfilter (alle Eintraege anzeigen).
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

        // Dropdown-Daten fuer die Filter laden
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
        // Nur offene Eintraege koennen freigegeben werden
        if ($zeiterfassung->status !== 'offen') {
            return back()->with('error', 'Dieser Eintrag wurde bereits bearbeitet.');
        }

        // Freigabe speichern: Status, freigebender Admin und Zeitstempel
        $zeiterfassung->update([
            'status'         => 'freigegeben',
            'freigegeben_von' => auth()->id(),
            'freigegeben_am'  => now(),
        ]);

        return back()->with('success', 'Zeiteintrag wurde freigegeben.');
    }

    /**
     * Lehnt einen Zeiteintrag ab.
     *
     * Setzt den Status auf 'abgelehnt'. Der Mitarbeitende wird ueber
     * den Status informiert und kann einen neuen Eintrag erstellen.
     *
     * @param  \App\Models\Zeiterfassung  $zeiterfassung
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ablehnen(Zeiterfassung $zeiterfassung): RedirectResponse
    {
        // Nur offene Eintraege koennen abgelehnt werden
        if ($zeiterfassung->status !== 'offen') {
            return back()->with('error', 'Dieser Eintrag wurde bereits bearbeitet.');
        }

        // Ablehnung speichern
        $zeiterfassung->update([
            'status'          => 'abgelehnt',
            'freigegeben_von' => auth()->id(),
            'freigegeben_am'  => now(),
        ]);

        return back()->with('success', 'Zeiteintrag wurde abgelehnt.');
    }

    /**
     * Gibt mehrere Zeiteintraege auf einmal frei (Massenfreigabe).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function massenfreigabe(Request $request): RedirectResponse
    {
        // IDs der ausgewaehlten Eintraege validieren
        $request->validate([
            'eintraege'   => ['required', 'array', 'min:1'],
            'eintraege.*' => ['integer', 'exists:zeiterfassungen,id'],
        ]);

        // Alle ausgewaehlten offenen Eintraege auf einmal freigeben
        $anzahl = Zeiterfassung::whereIn('id', $request->eintraege)
            ->where('status', 'offen')
            ->update([
                'status'          => 'freigegeben',
                'freigegeben_von' => auth()->id(),
                'freigegeben_am'  => now(),
            ]);

        return back()->with('success', "{$anzahl} Zeiteintrag/Eintraege wurden freigegeben.");
    }
}
