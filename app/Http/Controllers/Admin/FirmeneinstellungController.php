<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firmeneinstellung;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * FirmeneinstellungController
 *
 * Verwaltet die Firmen-Stammdaten, die in Rechnungen erscheinen:
 * - Absenderzeile
 * - Footer: Firmeninfo, Kontakt, Bankverbindung
 * - Standard-Zahlungstext und Grussformel
 *
 * Es gibt immer genau eine Zeile in der Tabelle (id=1).
 * Zugriff: Nur Administratoren
 */
class FirmeneinstellungController extends Controller
{
    /**
     * Zeigt das Bearbeitungsformular für die Firmeneinstellungen.
     *
     * @return \Illuminate\View\View
     */
    public function edit(): View
    {
        // Einstellungen laden (oder mit Standardwerten anlegen)
        $einstellung = Firmeneinstellung::laden();

        // Standardwerte – können jederzeit wiederhergestellt werden
        $standardwerte = [
            'absender'       => 'ALS Dienstleistungen – Frankfurter Landstraße.91a, 64291 Darmstadt',
            'footer_firma'   => "Frankfurter Landstraße.91a\n64291 Darmstadt\nSteuer Nr.: 00780160575\nWebseite: www.als-dl.de",
            'footer_kontakt' => "Hasan Aljasem\nTel: 017670549424\nE-Mail: info@als-dl.de",
            'footer_bank'    => "Sparkasse Darmstadt\nIBAN: DE94 5085 0150 0080 0254 91\nBIC: HELADEFIDAS",
            'zahlungstext'   => "Bitte überweisen Sie den Betrag innerhalb von 14 Tagen auf unser unten genanntes Konto, und geben Sie bitte die Rechnungsnummer als Verwendungszweck.",
            'gruss'          => "Mit freundlichen Grüßen\nALS Dienstleistungen",
            'anrede'         => 'Sehr geehrte Damen und Herren,',
            'einleitung'     => 'Hiermit stellen wir Ihnen folgende Leistungen in Rechnung:',
        ];

        return view('admin.einstellungen.edit', compact('einstellung', 'standardwerte'));
    }

    /**
     * Speichert die aktualisierten Firmeneinstellungen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        // Eingaben validieren
        $daten = $request->validate([
            'absender'       => ['required', 'string', 'max:255'],
            'footer_firma'   => ['required', 'string'],
            'footer_kontakt' => ['required', 'string'],
            'footer_bank'    => ['required', 'string'],
            'zahlungstext'   => ['required', 'string'],
            'gruss'          => ['required', 'string'],
            'anrede'         => ['required', 'string', 'max:255'],
            'einleitung'     => ['required', 'string', 'max:500'],
        ]);

        // Einstellungen laden und aktualisieren
        $einstellung = Firmeneinstellung::laden();
        $einstellung->update($daten);

        return redirect()
            ->route('admin.einstellungen.edit')
            ->with('success', 'Firmeneinstellungen wurden erfolgreich gespeichert.');
    }
}
