<?php

namespace App\Http\Controllers\Mitarbeiter;

use App\Http\Controllers\Controller;
use App\Models\Auftrag;
use App\Models\Zeiterfassung;
use Illuminate\View\View;

/**
 * DashboardController (Mitarbeiter-Bereich)
 *
 * Stellt die Startseite fuer angemeldete Mitarbeitende bereit.
 * Seit Einfuehrung des Auftrags-Systems werden Auftraege als einheitliche
 * Datenquelle verwendet. Zeiterfassungen entstehen automatisch bei Bestaetigung.
 *
 * Zugriff: Nur Mitarbeitende (Middleware: auth + mitarbeiter)
 */
class DashboardController extends Controller
{
    /**
     * Zeigt das Mitarbeiter-Dashboard mit persoenlichen Kennzahlen.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $mitarbeiter = auth()->user()->mitarbeiter;

        // Gesamtstunden des aktuellen Monats (aus auto-erstellten Zeiterfassungen)
        $stundenMonat = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->whereYear('datum', now()->year)
            ->whereMonth('datum', now()->month)
            ->sum('stunden');

        // Ausstehende Auftraege: noch nicht bestaetigt (gesendet, aber noch nicht ausgefuehrt)
        $ausstehend = Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->where('status', 'gesendet')
            ->count();

        // Freigegebene Zeiteintraege diesen Monat (nach Admin-Genehmigung)
        $freigegebeneEintraege = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->whereYear('datum', now()->year)
            ->whereMonth('datum', now()->month)
            ->where('status', 'freigegeben')
            ->count();

        // Die 8 neuesten Auftraege fuer die Dashboard-Tabelle
        $letzteAuftraege = Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->with(['auftraggeber', 'taetigkeit'])
            ->orderByDesc('datum')
            ->limit(8)
            ->get();

        return view('mitarbeiter.dashboard', compact(
            'stundenMonat',
            'ausstehend',
            'freigegebeneEintraege',
            'letzteAuftraege'
        ));
    }
}
