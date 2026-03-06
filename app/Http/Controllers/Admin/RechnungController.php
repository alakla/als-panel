<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auftraggeber;
use App\Models\Rechnung;
use App\Models\Zeiterfassung;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * RechnungController (Admin-Bereich)
 *
 * Verwaltet die Rechnungserstellung und -verwaltung.
 *
 * Prozess der Rechnungserstellung:
 * 1. Admin waehlt einen Auftraggeber und einen Abrechnungszeitraum
 * 2. Das System berechnet automatisch:
 *    - Alle freigegebenen Zeiteintraege des Zeitraums
 *    - Gesamtstunden (Summe der freigegebenen Stunden)
 *    - Nettobetrag (Stunden * Stundensatz des Auftraggebers)
 *    - MwSt-Betrag (19%)
 *    - Gesamtbetrag (Netto + MwSt)
 * 3. Eine eindeutige Rechnungsnummer wird generiert
 * 4. Eine PDF-Datei wird erstellt und gespeichert
 *
 * Zugriff: Nur Administratoren (Middleware: auth + admin)
 */
class RechnungController extends Controller
{
    /** MwSt-Satz: 19% */
    private const MWST_SATZ = 0.19;

    /**
     * Zeigt eine Liste aller erstellten Rechnungen.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Alle Rechnungen laden, neueste zuerst, mit Auftraggeber
        $rechnungen = Rechnung::with('auftraggeber')
            ->latest()
            ->paginate(15);

        return view('admin.rechnungen.index', compact('rechnungen'));
    }

    /**
     * Zeigt das Formular zum Erstellen einer neuen Rechnung.
     *
     * Laed alle aktiven Auftraggeber fuer die Auswahl.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        // Nur aktive Auftraggeber koennen abgerechnet werden
        $auftraggeber = Auftraggeber::where('is_active', true)
            ->orderBy('firmenname')
            ->get();

        return view('admin.rechnungen.create', compact('auftraggeber'));
    }

    /**
     * Berechnet eine Vorabschau der Rechnung (AJAX/POST).
     *
     * Zeigt dem Admin, welche Zeiteintraege im gewaehlten Zeitraum
     * vorhanden sind und wie hoch der Rechnungsbetrag sein wuerde.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function vorschau(Request $request): View
    {
        // Eingaben validieren
        $request->validate([
            'auftraggeber_id' => ['required', 'exists:auftraggeber,id'],
            'zeitraum_von'    => ['required', 'date'],
            'zeitraum_bis'    => ['required', 'date', 'after_or_equal:zeitraum_von'],
        ]);

        // Auftraggeber mit Stundensatz laden
        $auftraggeber = Auftraggeber::findOrFail($request->auftraggeber_id);

        // Alle freigegebenen Zeiteintraege im gewaehlten Zeitraum
        $zeiterfassungen = Zeiterfassung::with('mitarbeiter.user')
            ->where('auftraggeber_id', $auftraggeber->id)
            ->where('status', 'freigegeben')
            ->whereBetween('datum', [$request->zeitraum_von, $request->zeitraum_bis])
            ->orderBy('datum')
            ->get();

        // Gesamtstunden und Betraege berechnen
        $gesamtstunden = $zeiterfassungen->sum('stunden');
        $nettobetrag   = $gesamtstunden * $auftraggeber->stundensatz;
        $mwstBetrag    = $nettobetrag * self::MWST_SATZ;
        $gesamtbetrag  = $nettobetrag + $mwstBetrag;

        return view('admin.rechnungen.vorschau', compact(
            'auftraggeber',
            'zeiterfassungen',
            'gesamtstunden',
            'nettobetrag',
            'mwstBetrag',
            'gesamtbetrag',
            'request'
        ));
    }

    /**
     * Erstellt eine neue Rechnung und generiert die PDF-Datei.
     *
     * Alle freigegebenen Zeiteintraege des Zeitraums werden bei
     * der Rechnungserstellung als 'abgerechnet' markiert (durch
     * die Verknuepfung mit der Rechnung, nicht durch Statusaenderung).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Eingaben validieren
        $request->validate([
            'auftraggeber_id' => ['required', 'exists:auftraggeber,id'],
            'zeitraum_von'    => ['required', 'date'],
            'zeitraum_bis'    => ['required', 'date', 'after_or_equal:zeitraum_von'],
        ]);

        $auftraggeber = Auftraggeber::findOrFail($request->auftraggeber_id);

        // Freigegebene Zeiteintraege des Zeitraums laden
        $zeiterfassungen = Zeiterfassung::with('mitarbeiter.user')
            ->where('auftraggeber_id', $auftraggeber->id)
            ->where('status', 'freigegeben')
            ->whereBetween('datum', [$request->zeitraum_von, $request->zeitraum_bis])
            ->orderBy('datum')
            ->get();

        // Pruefung: Es muss mindestens ein Zeiteintrag vorhanden sein
        if ($zeiterfassungen->isEmpty()) {
            return back()->with('error', 'Keine freigegebenen Zeiteintraege im gewaehlten Zeitraum gefunden.');
        }

        // Betraege berechnen
        $gesamtstunden = $zeiterfassungen->sum('stunden');
        $nettobetrag   = round($gesamtstunden * $auftraggeber->stundensatz, 2);
        $mwstBetrag    = round($nettobetrag * self::MWST_SATZ, 2);
        $gesamtbetrag  = round($nettobetrag + $mwstBetrag, 2);

        // Rechnung und PDF in einer Datenbanktransaktion erstellen
        $rechnung = DB::transaction(function () use (
            $auftraggeber, $request, $gesamtstunden,
            $nettobetrag, $mwstBetrag, $gesamtbetrag, $zeiterfassungen
        ) {
            // Eindeutige Rechnungsnummer generieren: RE-JJJJ-MMDD-XXXX
            $rechnungsnummer = $this->generiereRechnungsnummer();

            // Rechnung in der Datenbank speichern
            $rechnung = Rechnung::create([
                'rechnungsnummer' => $rechnungsnummer,
                'auftraggeber_id' => $auftraggeber->id,
                'zeitraum_von'    => $request->zeitraum_von,
                'zeitraum_bis'    => $request->zeitraum_bis,
                'rechnungsdatum'  => now()->toDateString(),
                'nettobetrag'     => $nettobetrag,
                'mwst_betrag'     => $mwstBetrag,
                'gesamtbetrag'    => $gesamtbetrag,
                'status'          => 'offen',
            ]);

            // PDF-Datei generieren und speichern
            $pdfPfad = $this->generierePdf($rechnung, $auftraggeber, $zeiterfassungen, $gesamtstunden);
            $rechnung->update(['pdf_pfad' => $pdfPfad]);

            return $rechnung;
        });

        return redirect()
            ->route('admin.rechnungen.show', $rechnung)
            ->with('success', "Rechnung {$rechnung->rechnungsnummer} wurde erfolgreich erstellt.");
    }

    /**
     * Zeigt die Detailansicht einer Rechnung.
     *
     * @param  \App\Models\Rechnung  $rechnung
     * @return \Illuminate\View\View
     */
    public function show(Rechnung $rechnung): View
    {
        $rechnung->load('auftraggeber');

        return view('admin.rechnungen.show', compact('rechnung'));
    }

    /**
     * Laed die PDF-Datei einer Rechnung herunter.
     *
     * @param  \App\Models\Rechnung  $rechnung
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function download(Rechnung $rechnung)
    {
        // Pruefung: PDF-Datei muss vorhanden sein
        if (!$rechnung->pdf_pfad || !file_exists(storage_path('app/' . $rechnung->pdf_pfad))) {
            return back()->with('error', 'PDF-Datei nicht gefunden.');
        }

        return response()->download(
            storage_path('app/' . $rechnung->pdf_pfad),
            $rechnung->rechnungsnummer . '.pdf'
        );
    }

    /**
     * Aendert den Bezahlstatus einer Rechnung auf 'bezahlt'.
     *
     * @param  \App\Models\Rechnung  $rechnung
     * @return \Illuminate\Http\RedirectResponse
     */
    public function alsBezahlt(Rechnung $rechnung): RedirectResponse
    {
        if ($rechnung->status !== 'offen') {
            return back()->with('error', 'Nur offene Rechnungen koennen als bezahlt markiert werden.');
        }

        $rechnung->update(['status' => 'bezahlt']);

        return back()->with('success', 'Rechnung wurde als bezahlt markiert.');
    }

    /**
     * Generiert eine eindeutige Rechnungsnummer.
     * Format: RE-JJJJ-NNNN (z.B. RE-2026-0001)
     *
     * @return string
     */
    private function generiereRechnungsnummer(): string
    {
        $jahr   = now()->year;
        // Letzte Rechnungsnummer des aktuellen Jahres ermitteln
        $letzte = Rechnung::where('rechnungsnummer', 'like', "RE-{$jahr}-%")
            ->orderByDesc('rechnungsnummer')
            ->value('rechnungsnummer');

        // Naechste Nummer berechnen
        $naechsteNummer = 1;
        if ($letzte) {
            // Nummer aus dem Format RE-JJJJ-NNNN extrahieren
            $teile          = explode('-', $letzte);
            $naechsteNummer = ((int) end($teile)) + 1;
        }

        return sprintf('RE-%d-%04d', $jahr, $naechsteNummer);
    }

    /**
     * Generiert die PDF-Rechnung und speichert sie im Storage.
     *
     * @param  \App\Models\Rechnung     $rechnung
     * @param  \App\Models\Auftraggeber $auftraggeber
     * @param  \Illuminate\Support\Collection $zeiterfassungen
     * @param  float                    $gesamtstunden
     * @return string  Relativer Pfad zur PDF-Datei
     */
    private function generierePdf(
        Rechnung $rechnung,
        Auftraggeber $auftraggeber,
        $zeiterfassungen,
        float $gesamtstunden
    ): string {
        // PDF aus dem Blade-Template rendern
        $pdf = Pdf::loadView('admin.rechnungen.pdf', compact(
            'rechnung', 'auftraggeber', 'zeiterfassungen', 'gesamtstunden'
        ));

        // Seitenformat und Ausrichtung setzen
        $pdf->setPaper('A4', 'portrait');

        // Speicherpfad erstellen (Ordner nach Jahr)
        $ordner = 'rechnungen/' . now()->year;
        if (!file_exists(storage_path('app/' . $ordner))) {
            mkdir(storage_path('app/' . $ordner), 0755, true);
        }

        // PDF-Datei speichern
        $pfad = "{$ordner}/{$rechnung->rechnungsnummer}.pdf";
        $pdf->save(storage_path('app/' . $pfad));

        return $pfad;
    }
}
