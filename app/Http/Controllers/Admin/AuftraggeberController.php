<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuftraggeberRequest;
use App\Models\Auftraggeber;
use App\Models\Auftrag;
use App\Models\Rechnung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AuftraggeberController – Verwaltung der Auftraggeber (CRUD)
 *
 * Verwaltet alle Kundenunternehmen, an die Mitarbeitende vermittelt werden.
 * Der Stundensatz wird pro Tätigkeit verwaltet (nicht mehr beim Auftraggeber).
 *
 * Zugriff: Nur Administratoren (Middleware: auth + admin)
 */
class AuftraggeberController extends Controller
{
    /**
     * Zeigt eine Liste aller Auftraggeber an.
     *
     * Unterstützt optionale Suche nach Firmenname oder Ansprechpartner.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Suchanfrage aus dem Request holen (optional)
        $suche = request('suche');

        // Auftraggeber laden, optional gefiltert, paginiert
        $auftraggeber = Auftraggeber::when($suche, function ($query) use ($suche) {
                $query->where('firmenname', 'like', "%{$suche}%")
                      ->orWhere('ansprechpartner', 'like', "%{$suche}%")
                      ->orWhere('email', 'like', "%{$suche}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.auftraggeber.index', compact('auftraggeber', 'suche'));
    }

    /**
     * Zeigt das Formular zum Anlegen eines neuen Auftraggebers.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('admin.auftraggeber.create');
    }

    /**
     * Speichert einen neuen Auftraggeber in der Datenbank.
     *
     * @param  \App\Http\Requests\AuftraggeberRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AuftraggeberRequest $request): RedirectResponse
    {
        // Auftraggeber mit allen validierten Feldern erstellen
        Auftraggeber::create([
            'firmenname'      => $request->firmenname,
            'ansprechpartner' => $request->ansprechpartner,
            'adresse'         => $request->adresse,
            'email'           => $request->email,
            'telefon'         => $request->telefon,
            'is_active'       => true,
        ]);

        return redirect()
            ->route('admin.auftraggeber.index')
            ->with('success', 'Auftraggeber wurde erfolgreich angelegt.');
    }

    /**
     * Zeigt die Detailseite eines Auftraggebers.
     *
     * Zeigt alle Aufträge für diesen Auftraggeber mit Filter nach Status und Monat/Jahr.
     * Identischer Filter wie auf der Mitarbeiter-Detailseite.
     *
     * @param  Request               $request      HTTP-Anfrage mit optionalen Filterparametern
     * @param  Auftraggeber          $auftraggeber Der anzuzeigende Auftraggeber
     * @return \Illuminate\View\View
     */
    public function show(Request $request, Auftraggeber $auftraggeber): View
    {
        // Statusfilter (wie Aufträge-Seite)
        $filterStatus = $request->input('status', 'alle');
        if (!in_array($filterStatus, ['alle', 'gesendet', 'bestaetigt', 'freigegeben', 'abgelehnt'])) {
            $filterStatus = 'alle';
        }

        // Monat und Jahr aus getrennten Feldern lesen
        $filterJahr  = (int) $request->input('jahr',     now()->year);
        $filterMonat = (int) $request->input('monat_nr', now()->month);
        $monat = sprintf('%04d-%02d', $filterJahr, $filterMonat);

        // Monatsnamen auf Deutsch
        $monatsnamen = [1=>'Januar',2=>'Februar',3=>'März',4=>'April',5=>'Mai',6=>'Juni',
                        7=>'Juli',8=>'August',9=>'September',10=>'Oktober',11=>'November',12=>'Dezember'];
        $filterMonatLabel = $monatsnamen[$filterMonat] . ' ' . $filterJahr;

        // Verfügbare Jahre: vom ältesten Auftrag dieses Auftraggebers bis nächstes Jahr
        $aeltestesJahr = Auftrag::where('auftraggeber_id', $auftraggeber->id)
            ->selectRaw('YEAR(MIN(datum)) as jahr')
            ->value('jahr') ?? now()->year;
        $jahre = range(now()->year + 1, $aeltestesJahr);

        // Aufträge laden (gefiltert)
        $auftraege = Auftrag::with(['mitarbeiter.user', 'taetigkeit'])
            ->where('auftraggeber_id', $auftraggeber->id)
            ->when($filterStatus !== 'alle', fn($q) => $q->where('status', $filterStatus))
            ->whereYear('datum', $filterJahr)
            ->whereMonth('datum', $filterMonat)
            ->orderByDesc('datum')
            ->orderByDesc('von')
            ->get();

        // Anzahl pro Status für den aktuellen Monat (unabhängig vom Statusfilter)
        $statusCounts = Auftrag::where('auftraggeber_id', $auftraggeber->id)
            ->whereYear('datum', $filterJahr)
            ->whereMonth('datum', $filterMonat)
            ->selectRaw('status, COUNT(*) as anzahl')
            ->groupBy('status')
            ->pluck('anzahl', 'status');

        // ── Rechnungen-Filter (separate Parameter mit Prefix "r_") ──────────────
        $rFilterStatus = $request->input('r_status', 'alle');
        if (!in_array($rFilterStatus, ['alle', 'offen', 'bezahlt', 'storniert'])) {
            $rFilterStatus = 'alle';
        }
        $rFilterJahr  = (int) $request->input('r_jahr',     now()->year);
        $rFilterMonat = (int) $request->input('r_monat_nr', now()->month);
        $rMonat = sprintf('%04d-%02d', $rFilterJahr, $rFilterMonat);

        // Verfügbare Jahre für Rechnungen
        $rAeltestesJahr = Rechnung::where('auftraggeber_id', $auftraggeber->id)
            ->selectRaw('YEAR(MIN(rechnungsdatum)) as jahr')
            ->value('jahr') ?? now()->year;
        $rJahre = range(now()->year + 1, $rAeltestesJahr);

        // Rechnungen gefiltert laden
        $rechnungen = Rechnung::where('auftraggeber_id', $auftraggeber->id)
            ->when($rFilterStatus !== 'alle', fn($q) => $q->where('status', $rFilterStatus))
            ->whereYear('rechnungsdatum', $rFilterJahr)
            ->whereMonth('rechnungsdatum', $rFilterMonat)
            ->orderByDesc('rechnungsdatum')
            ->get();

        // Anzahl pro Status für Rechnungen (unabhängig vom Statusfilter)
        $rStatusCounts = Rechnung::where('auftraggeber_id', $auftraggeber->id)
            ->whereYear('rechnungsdatum', $rFilterJahr)
            ->whereMonth('rechnungsdatum', $rFilterMonat)
            ->selectRaw('status, COUNT(*) as anzahl')
            ->groupBy('status')
            ->pluck('anzahl', 'status');

        return view('admin.auftraggeber.show', compact(
            'auftraggeber',
            'auftraege',
            'filterStatus',
            'monat',
            'jahre',
            'filterMonatLabel',
            'statusCounts',
            'rechnungen',
            'rFilterStatus',
            'rMonat',
            'rJahre',
            'rStatusCounts'
        ));
    }

    /**
     * Zeigt das Bearbeitungsformular eines Auftraggebers.
     *
     * @param  \App\Models\Auftraggeber  $auftraggeber
     * @return \Illuminate\View\View
     */
    public function edit(Auftraggeber $auftraggeber): View
    {
        return view('admin.auftraggeber.edit', compact('auftraggeber'));
    }

    /**
     * Aktualisiert die Daten eines vorhandenen Auftraggebers.
     *
     * @param  \App\Http\Requests\AuftraggeberRequest  $request
     * @param  \App\Models\Auftraggeber  $auftraggeber
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AuftraggeberRequest $request, Auftraggeber $auftraggeber): RedirectResponse
    {
        // Validierte Daten speichern
        $auftraggeber->update($request->validated());

        return redirect()
            ->route('admin.auftraggeber.index')
            ->with('success', 'Auftraggeber wurde erfolgreich aktualisiert.');
    }

    /**
     * Deaktiviert oder reaktiviert einen Auftraggeber.
     *
     * Statt Löschen wird der is_active-Status umgeschaltet,
     * damit historische Daten (Zeiterfassungen, Rechnungen) erhalten bleiben.
     *
     * @param  \App\Models\Auftraggeber  $auftraggeber
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Auftraggeber $auftraggeber): RedirectResponse
    {
        // Status umschalten: aktiv <-> inaktiv
        $auftraggeber->update(['is_active' => !$auftraggeber->is_active]);

        $aktion = $auftraggeber->is_active ? 'reaktiviert' : 'deaktiviert';

        return redirect()
            ->route('admin.auftraggeber.index')
            ->with('success', "Auftraggeber wurde erfolgreich {$aktion}.");
    }
}
