<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taetigkeit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TaetigkeitController (Admin-Bereich)
 *
 * Ermoeglicht Administratoren das Verwalten der vordefinierten
 * Taetigkeitsbeschreibungen, die Mitarbeitende bei der Zeiterfassung
 * auswaehlen koennen (Hinzufuegen, Umbenennen, Loeschen, Sortieren).
 *
 * Zugriff: Nur Administratoren (Middleware: auth + admin)
 */
class TaetigkeitController extends Controller
{
    /**
     * Zeigt die Verwaltungsseite aller Taetigkeiten.
     */
    public function index(): View
    {
        // Sortiert nach Reihenfolge, dann alphabetisch
        $taetigkeiten = Taetigkeit::orderBy('reihenfolge')->orderBy('name')->get();

        return view('admin.taetigkeiten.index', compact('taetigkeiten'));
    }

    /**
     * Speichert eine neue Taetigkeit.
     * Die Reihenfolge wird automatisch ans Ende gesetzt.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:100', 'unique:taetigkeiten,name'],
            'stundensatz'    => ['required', 'numeric', 'min:0'],
            'abrechnungsart' => ['required', 'in:stundensatz,pauschal'],
        ], [
            'name.required'           => 'Die Bezeichnung ist erforderlich.',
            'name.unique'             => 'Diese Taetigkeit existiert bereits.',
            'name.max'                => 'Die Bezeichnung darf maximal 100 Zeichen lang sein.',
            'stundensatz.required'    => 'Der Betrag ist erforderlich.',
            'stundensatz.numeric'     => 'Der Betrag muss eine Zahl sein.',
            'stundensatz.min'         => 'Der Betrag darf nicht negativ sein.',
            'abrechnungsart.required' => 'Die Abrechnungsart ist erforderlich.',
            'abrechnungsart.in'       => 'Ungueltiger Wert fuer Abrechnungsart.',
        ]);

        // Naechste Reihenfolge-Position berechnen
        $naechstePosition = (Taetigkeit::max('reihenfolge') ?? 0) + 1;

        Taetigkeit::create([
            'name'           => $request->name,
            'stundensatz'    => $request->stundensatz,
            'abrechnungsart' => $request->abrechnungsart,
            'reihenfolge'    => $naechstePosition,
        ]);

        return back()->with('success', "Taetigkeit \"{$request->name}\" wurde hinzugefuegt.");
    }

    /**
     * Aktualisiert den Namen einer Taetigkeit.
     */
    public function update(Request $request, Taetigkeit $taetigkeit): RedirectResponse
    {
        $request->validate([
            // unique: diesen Datensatz selbst ausschliessen
            'name'           => ['required', 'string', 'max:100', 'unique:taetigkeiten,name,' . $taetigkeit->id],
            'stundensatz'    => ['required', 'numeric', 'min:0'],
            'abrechnungsart' => ['required', 'in:stundensatz,pauschal'],
        ], [
            'name.required'           => 'Die Bezeichnung ist erforderlich.',
            'name.unique'             => 'Diese Taetigkeit existiert bereits.',
            'name.max'                => 'Die Bezeichnung darf maximal 100 Zeichen lang sein.',
            'stundensatz.required'    => 'Der Betrag ist erforderlich.',
            'stundensatz.numeric'     => 'Der Betrag muss eine Zahl sein.',
            'stundensatz.min'         => 'Der Betrag darf nicht negativ sein.',
            'abrechnungsart.required' => 'Die Abrechnungsart ist erforderlich.',
            'abrechnungsart.in'       => 'Ungueltiger Wert fuer Abrechnungsart.',
        ]);

        $taetigkeit->update([
            'name'           => $request->name,
            'stundensatz'    => $request->stundensatz,
            'abrechnungsart' => $request->abrechnungsart,
        ]);

        return back()->with('success', "Taetigkeit wurde auf \"{$request->name}\" geaendert.");
    }

    /**
     * Loescht eine Taetigkeit endgueltig.
     */
    public function destroy(Taetigkeit $taetigkeit): RedirectResponse
    {
        $name = $taetigkeit->name;
        $taetigkeit->delete();

        return back()->with('success', "Taetigkeit \"{$name}\" wurde geloescht.");
    }
}
