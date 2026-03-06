<?php

namespace App\Http\Controllers\Mitarbeiter;

use App\Http\Controllers\Controller;
use App\Models\Zeiterfassung;
use Illuminate\View\View;

/**
 * DashboardController (Mitarbeiter-Bereich)
 *
 * Stellt die Startseite fuer angemeldete Mitarbeitende bereit.
 * Zeigt persoenliche Kennzahlen und die letzten Zeiteintraege.
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
        // Mitarbeiter-Datensatz des aktuell angemeldeten Benutzers laden
        $mitarbeiter = auth()->user()->mitarbeiter;

        // Gesamtstunden des aktuellen Monats berechnen
        $stundenMonat = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->whereYear('datum', now()->year)
            ->whereMonth('datum', now()->month)
            ->sum('stunden');

        // Anzahl offener Eintraege (noch nicht freigegeben)
        $offeneEintraege = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->where('status', 'offen')
            ->count();

        // Anzahl freigegebener Eintraege diesen Monat
        $freigegebeneEintraege = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->whereYear('datum', now()->year)
            ->whereMonth('datum', now()->month)
            ->where('status', 'freigegeben')
            ->count();

        // Die 5 neuesten Zeiteintraege (fuer die Tabelle im Dashboard)
        $letzteEintraege = Zeiterfassung::where('mitarbeiter_id', $mitarbeiter->id)
            ->with('auftraggeber')
            ->orderByDesc('datum')
            ->limit(5)
            ->get();

        return view('mitarbeiter.dashboard', compact(
            'stundenMonat',
            'offeneEintraege',
            'freigegebeneEintraege',
            'letzteEintraege'
        ));
    }
}
