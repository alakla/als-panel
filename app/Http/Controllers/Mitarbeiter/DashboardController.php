<?php

namespace App\Http\Controllers\Mitarbeiter;

use App\Http\Controllers\Controller;
use App\Models\Auftrag;
use App\Models\Zeiterfassung;
use Illuminate\View\View;

/**
 * DashboardController (Mitarbeiter-Bereich)
 *
 * Stellt die Startseite für angemeldete Mitarbeitende bereit.
 * Seit Einführung des Auftrags-Systems werden Aufträge als einheitliche
 * Datenquelle verwendet. Zeiterfassungen entstehen automatisch bei Bestätigung.
 *
 * Zugriff: Nur Mitarbeitende (Middleware: auth + mitarbeiter)
 */
class DashboardController extends Controller
{
    /**
     * Zeigt das Mitarbeiter-Dashboard mit persönlichen Kennzahlen.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $mitarbeiter = auth()->user()->mitarbeiter;

        // Freigegebene Stunden des aktuellen Monats (nur Status "freigegeben")
        $stundenMonat = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->where('status', 'freigegeben')
            ->whereYear('datum', now()->year)
            ->whereMonth('datum', now()->month)
            ->sum('stunden');

        // Ausstehende Aufträge: noch nicht bestätigt (gesendet, aber noch nicht ausgeführt)
        $ausstehend = Auftrag::where('mitarbeiter_id', $mitarbeiter->id)
            ->where('status', 'gesendet')
            ->count();

        // Freigegebene Zeiteinträge diesen Monat (nach Admin-Genehmigung)
        $freigegebeneEintraege = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->whereYear('datum', now()->year)
            ->whereMonth('datum', now()->month)
            ->where('status', 'freigegeben')
            ->count();

        // Die 8 neuesten Aufträge für die Dashboard-Tabelle
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
