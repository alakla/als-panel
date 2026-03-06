<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuftraggeberRequest;
use App\Models\Auftraggeber;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * AuftraggeberController – Verwaltung der Auftraggeber (CRUD)
 *
 * Verwaltet alle Kundenunternehmen, an die Mitarbeitende vermittelt werden.
 * Der Stundensatz jedes Auftraggebers wird spaeter fuer die
 * automatisierte Rechnungsstellung verwendet.
 *
 * Zugriff: Nur Administratoren (Middleware: auth + admin)
 */
class AuftraggeberController extends Controller
{
    /**
     * Zeigt eine Liste aller Auftraggeber an.
     *
     * Unterstuetzt optionale Suche nach Firmenname oder Ansprechpartner.
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
            'stundensatz'     => $request->stundensatz,
            'is_active'       => true,
        ]);

        return redirect()
            ->route('admin.auftraggeber.index')
            ->with('success', 'Auftraggeber wurde erfolgreich angelegt.');
    }

    /**
     * Zeigt die Detailseite eines Auftraggebers.
     *
     * Laedt alle zugehoerigen Zeiterfassungen und Rechnungen.
     *
     * @param  \App\Models\Auftraggeber  $auftraggeber
     * @return \Illuminate\View\View
     */
    public function show(Auftraggeber $auftraggeber): View
    {
        // Zeiterfassungen und Rechnungen mitladen
        $auftraggeber->load(['zeiterfassungen.mitarbeiter.user', 'rechnungen']);

        return view('admin.auftraggeber.show', compact('auftraggeber'));
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
     * Statt Loeschen wird der is_active-Status umgeschaltet,
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
