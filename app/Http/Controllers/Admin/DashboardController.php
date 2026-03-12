<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auftraggeber;
use App\Models\Mitarbeiter;
use App\Models\Zeiterfassung;
use App\Models\Rechnung;

/**
 * DashboardController – Verwaltung des Admin-Dashboards
 *
 * Dieser Controller ist verantwortlich für die Anzeige des Admin-Dashboards.
 * Er sammelt alle relevanten Kennzahlen (KPIs) und stellt sie der Ansicht bereit.
 * Zugriff nur für Benutzer mit der Rolle 'admin' (gesichert durch CheckAdmin-Middleware).
 */
class DashboardController extends Controller
{
    /**
     * Zeigt das Admin-Dashboard mit aktuellen Kennzahlen an.
     *
     * Folgende Daten werden an die Ansicht übergeben:
     * - Anzahl aktiver Mitarbeitender
     * - Anzahl aktiver Auftraggeber
     * - Anzahl offener (nicht freigegebener) Zeiteinträge
     * - Anzahl der Rechnungen im aktuellen Monat
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Anzahl der aktiven Mitarbeitenden zählen
        $mitarbeiterCount = Mitarbeiter::where('status', 'aktiv')->count();

        // Anzahl der aktiven Auftraggeber zählen
        $auftraggeberCount = Auftraggeber::where('is_active', true)->count();

        // Offene Zeiteinträge zählen (noch nicht freigegeben oder abgelehnt)
        $offeneZeiteintraege = Zeiterfassung::where('status', 'offen')->count();

        // Rechnungen des aktuellen Monats zaehlen
        $rechnungenMonat = Rechnung::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Daten an die Admin-Dashboard-Ansicht übergeben
        return view('admin.dashboard', compact(
            'mitarbeiterCount',
            'auftraggeberCount',
            'offeneZeiteintraege',
            'rechnungenMonat'
        ));
    }
}
