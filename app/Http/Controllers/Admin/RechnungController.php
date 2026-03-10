<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auftrag;
use App\Models\Auftraggeber;
use App\Models\Rechnung;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * RechnungController (Admin-Bereich)
 *
 * Verwaltet die Rechnungserstellung und -verwaltung.
 *
 * Prozess der Rechnungserstellung (neu, mit editierbarer Vorschau):
 * 1. Admin waehlt einen Auftraggeber und einen Abrechnungszeitraum (create)
 * 2. Vorschau (vorschau): Das System berechnet Positionen aus freigegebenen Auftraegen
 *    und zeigt eine editierbare Papier-Vorschau an.
 * 3. Admin kann alle Felder (Positionen, Texte, Adressen, Footer) direkt anpassen.
 * 4. Speichern (store): Die editierten Daten werden validiert, Rechnung in DB gespeichert
 *    und PDF mit den benutzerdefinierten Inhalten generiert.
 *
 * PDF-Generierung: Alle Inhalte werden direkt aus dem Formular uebernommen.
 * Es gibt keine zweite Berechnung aus der Datenbank – was der Admin sieht, wird gedruckt.
 *
 * Zugriff: Nur Administratoren (Middleware: auth + admin)
 */
class RechnungController extends Controller
{
    /** MwSt-Satz: 19% (gesetzlich vorgeschrieben) */
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
     * Zeigt das Formular zum Auswaehlen von Auftraggeber und Zeitraum.
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
     * Berechnet eine editierbare Vorabschau der Rechnung.
     *
     * Schritt 2 im Rechnungserstellungsprozess:
     * - Freigegebene Auftraege des Zeitraums werden geladen
     * - Positionen werden nach Taetigkeit gruppiert und berechnet
     * - Die Vorschau-Seite zeigt ein editierbares "Papier-Dokument"
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

        // Auftraggeber laden
        $auftraggeber = Auftraggeber::findOrFail($request->auftraggeber_id);

        // Alle freigegebenen Auftraege im gewaehlten Zeitraum laden
        // Nur Auftraege mit status='freigegeben' und datum im Zeitraum werden beruecksichtigt
        $auftraege = Auftrag::with(['mitarbeiter.user', 'taetigkeit'])
            ->where('auftraggeber_id', $auftraggeber->id)
            ->where('status', 'freigegeben')
            ->whereBetween('datum', [$request->zeitraum_von, $request->zeitraum_bis])
            ->orderBy('datum')
            ->get();

        // Positionen aus Auftraegen berechnen (fuer Vorschau-Anzeige)
        // Auftraege werden nach Taetigkeit gruppiert: Eine Taetigkeit = Eine Rechnungsposition
        $positionen = $auftraege
            ->groupBy('taetigkeit_id')
            ->values()
            ->map(function ($gruppe) {
                $taetigkeit  = $gruppe->first()->taetigkeit;
                // Abrechnungsart bestimmen: pauschal oder stundensatz
                $istPauschal = $taetigkeit?->abrechnungsart === 'pauschal';
                // Gesamtstunden der Gruppe berechnen (Summe aller Auftraege dieser Taetigkeit)
                $stunden     = $gruppe->sum(fn($a) => $a->berechneteStunden());
                $einzelpreis = $taetigkeit?->stundensatz ?? 0;
                return [
                    'name'        => $taetigkeit?->name ?? '–',
                    // Zeitraum wird in der Vorschau als editierbares Feld angezeigt
                    'zeitraum'    => '',
                    'einheit'     => $istPauschal ? 'Pauschal' : 'Std.',
                    // Bei Pauschal: Menge immer 1; bei Stunden: echte Stundenzahl
                    'menge'       => $istPauschal ? 1 : $stunden,
                    'einzelpreis' => $einzelpreis,
                    // Bei Pauschal: Einzelpreis = Gesamtpreis; bei Stunden: Stunden * Satz
                    'gesamtpreis' => $istPauschal ? $einzelpreis : ($stunden * $einzelpreis),
                ];
            })->toArray();

        // Zeitraum fuer die Anzeige in der Positionstabelle formatieren
        $vonCarbon = \Carbon\Carbon::parse($request->zeitraum_von);
        $bisCarbon = \Carbon\Carbon::parse($request->zeitraum_bis);

        $zeitraumVon = $vonCarbon->format('d.m.y');
        $zeitraumBis = $bisCarbon->format('d.m.y');

        // Pruefen ob voller Monat: 1. bis letzter Tag desselben Monats
        // Wenn ja: Kurzform "März 2026" anzeigen, sonst normale Datumsangabe
        $istVollerMonat = $vonCarbon->day === 1
            && $vonCarbon->isSameMonth($bisCarbon)
            && $bisCarbon->day === $bisCarbon->daysInMonth;

        if ($istVollerMonat) {
            $monatsnamen  = ['Januar','Februar','März','April','Mai','Juni',
                             'Juli','August','September','Oktober','November','Dezember'];
            $zeitraumAnzeige = $monatsnamen[$vonCarbon->month - 1] . ' ' . $vonCarbon->year;
        } else {
            $zeitraumAnzeige = $zeitraumVon . ' – ' . $zeitraumBis;
        }

        // Gesamtbetraege fuer die initiale Summenanzeige berechnen
        $nettobetrag  = array_sum(array_column($positionen, 'gesamtpreis'));
        $mwstBetrag   = $nettobetrag * self::MWST_SATZ;
        $gesamtbetrag = $nettobetrag + $mwstBetrag;

        // Naechste Rechnungsnummer zur Anzeige in der Vorschau vorberechnen (nicht reserviert)
        $vorschauNummer = $this->generiereRechnungsnummer();

        return view('admin.rechnungen.vorschau', compact(
            'auftraggeber',
            'positionen',
            'nettobetrag',
            'mwstBetrag',
            'gesamtbetrag',
            'zeitraumVon',
            'zeitraumBis',
            'zeitraumAnzeige',
            'vorschauNummer',
            'request'
        ));
    }

    /**
     * Erstellt eine neue Rechnung aus den editierten Vorschau-Daten und generiert das PDF.
     *
     * Schritt 3 im Rechnungserstellungsprozess:
     * - Alle editierten Felder aus dem Formular werden uebernommen
     * - Positionen und Betraege werden aus den Formulardaten berechnet
     * - Rechnung wird in der Datenbank gespeichert
     * - PDF wird mit den benutzerdefinierten Inhalten generiert und gespeichert
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Pflichtfelder validieren
        $request->validate([
            'auftraggeber_id' => ['required', 'exists:auftraggeber,id'],
            'zeitraum_von'    => ['required', 'date'],
            'zeitraum_bis'    => ['required', 'date', 'after_or_equal:zeitraum_von'],
            'rechnungsdatum'  => ['required', 'date'],
            'positionen'      => ['required', 'array', 'min:1'],
        ]);

        // Positionen aus dem Formular sammeln und Zahlen konvertieren
        // Der Admin kann Positionen in der Vorschau veraendert haben
        $positionen = collect($request->positionen)->map(function ($p) {
            return [
                'name'        => trim($p['name'] ?? ''),
                // Zeitraum: vom Admin editierbar in der Vorschau
                'zeitraum'    => trim($p['zeitraum'] ?? ''),
                'menge'       => floatval($p['menge'] ?? 0),
                'einheit'     => $p['einheit'] ?? 'Std.',
                'einzelpreis' => floatval($p['einzelpreis'] ?? 0),
                // Gesamtpreis wird aus dem Formular uebernommen (per JS vorberechnet)
                'gesamtpreis' => floatval($p['gesamtpreis'] ?? 0),
            ];
        })->toArray();

        // Nettobetrag aus den (editierten) Gesamtpreisen der Positionen summieren
        $nettobetrag  = round(array_sum(array_column($positionen, 'gesamtpreis')), 2);
        $mwstBetrag   = round($nettobetrag * self::MWST_SATZ, 2);
        $gesamtbetrag = round($nettobetrag + $mwstBetrag, 2);

        // Alle benutzerdefinierten Textfelder aus dem Formular einlesen
        // Diese werden direkt in das PDF uebernommen – keine weitere Berechnung
        $absender      = $request->input('absender',
            'ALS Dienstleistungen – Frankfurter Landstraße.91a, 64291 Darmstadt');
        $empfaengerName = $request->input('empfaenger_name', '');
        // Adresszeilen als Array (je eine Zeile = ein Eingabefeld in der Vorschau)
        $adresseZeilen = array_filter(
            array_map('trim', $request->input('adresse_zeilen', []))
        );
        $anrede        = $request->input('anrede', 'Sehr geehrte Damen und Herren,');
        $einleitung    = $request->input('einleitung',
            'Hiermit stellen wir Ihnen folgende Leistungen in Rechnung:');
        $zahlungstext  = $request->input('zahlungstext',
            'Bitte überweisen Sie den Betrag innerhalb von 14 Tagen auf unser unten genanntes Konto, '
            . 'und geben Sie bitte die Rechnungsnummer als Verwendungszweck.');
        $gruss         = $request->input('gruss', "Mit freundlichen Grüßen\nALS Dienstleistungen");
        $footerFirma   = $request->input('footer_firma',
            "ALS Dienstleistungen\nFrankfurter Landstraße.91a\n64291 Darmstadt\nSteuer Nr.: 00780160575");
        $footerKontakt = $request->input('footer_kontakt',
            "Hasan Aljasem\nTel: 017670549424\nE-Mail: als.dienstleistungen@gmail.com");
        $footerBank    = $request->input('footer_bank',
            "Sparkasse Darmstadt\nIBAN: DE94 5085 0150 0080 0254 91\nBIC: HELADEFIDAS");

        // Rechnung und PDF in einer Datenbanktransaktion erstellen
        // Bei Fehler wird alles zurueckgerollt (keine halbfertigen Eintraege)
        $rechnung = DB::transaction(function () use (
            $request, $nettobetrag, $mwstBetrag, $gesamtbetrag,
            $positionen, $absender, $empfaengerName, $adresseZeilen,
            $anrede, $einleitung, $zahlungstext, $gruss,
            $footerFirma, $footerKontakt, $footerBank
        ) {
            // Eindeutige Rechnungsnummer generieren: RE-NNN/JJJJ
            $rechnungsnummer = $this->generiereRechnungsnummer();

            // Rechnung in der Datenbank speichern
            $rechnung = Rechnung::create([
                'rechnungsnummer' => $rechnungsnummer,
                'auftraggeber_id' => $request->auftraggeber_id,
                'zeitraum_von'    => $request->zeitraum_von,
                'zeitraum_bis'    => $request->zeitraum_bis,
                // Rechnungsdatum aus dem Formular (Admin kann es veraendert haben)
                'rechnungsdatum'  => $request->rechnungsdatum,
                'nettobetrag'     => $nettobetrag,
                'mwst_betrag'     => $mwstBetrag,
                'gesamtbetrag'    => $gesamtbetrag,
                'status'          => 'offen',
            ]);

            // Alle benutzerdefinierten PDF-Daten als Array zusammenfassen
            $pdfDaten = [
                'positionen'      => $positionen,
                'absender'        => $absender,
                'empfaenger_name' => $empfaengerName,
                'adresse_zeilen'  => array_values($adresseZeilen),
                'anrede'          => $anrede,
                'einleitung'      => $einleitung,
                'zahlungstext'    => $zahlungstext,
                'gruss'           => $gruss,
                'footer_firma'    => $footerFirma,
                'footer_kontakt'  => $footerKontakt,
                'footer_bank'     => $footerBank,
            ];

            // PDF-Datei generieren und Pfad in der Rechnung speichern
            $pdfPfad = $this->generierePdf($rechnung, $pdfDaten);
            $rechnung->update(['pdf_pfad' => $pdfPfad]);

            return $rechnung;
        });

        // Zur Show-Seite weiterleiten; die Seite loest den Download automatisch per JS aus
        return redirect()
            ->route('admin.rechnungen.show', $rechnung)
            ->with('auto_download', true);
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
     * Liefert die gespeicherte PDF-Datei als Download.
     *
     * Die PDF wird beim Erstellen der Rechnung generiert und im Storage gespeichert.
     * Hier wird die gespeicherte Datei abgerufen und an den Browser geschickt.
     *
     * @param  \App\Models\Rechnung  $rechnung
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function download(Rechnung $rechnung)
    {
        $rechnung->load('auftraggeber');

        // Pruefen, ob ein gespeicherter PDF-Pfad vorhanden ist
        if (!$rechnung->pdf_pfad) {
            return back()->with('error', 'Kein PDF fuer diese Rechnung vorhanden.');
        }

        // Vollstaendigen Dateipfad im Storage ermitteln
        $vollPfad = storage_path('app/' . $rechnung->pdf_pfad);

        // Pruefen, ob die Datei tatsaechlich existiert
        if (!file_exists($vollPfad)) {
            return back()->with('error', 'PDF-Datei nicht gefunden. Bitte Rechnung neu erstellen.');
        }

        // Dateiname fuer den Download (Schraegstrich ersetzen, z.B. RE-001/2026 -> RE-001-2026)
        $dateiname = str_replace('/', '-', $rechnung->rechnungsnummer) . '.pdf';

        return response()->download($vollPfad, $dateiname);
    }

    /**
     * Aendert den Bezahlstatus einer Rechnung auf 'bezahlt'.
     *
     * @param  \App\Models\Rechnung  $rechnung
     * @return \Illuminate\Http\RedirectResponse
     */
    public function alsBezahlt(Rechnung $rechnung): RedirectResponse
    {
        // Nur offene Rechnungen koennen als bezahlt markiert werden
        if ($rechnung->status !== 'offen') {
            return back()->with('error', 'Nur offene Rechnungen koennen als bezahlt markiert werden.');
        }

        $rechnung->update(['status' => 'bezahlt']);

        return back()->with('success', 'Rechnung wurde als bezahlt markiert.');
    }

    /**
     * Generiert eine eindeutige Rechnungsnummer.
     * Format: RE-NNN/JJJJ (z.B. RE-001/2026)
     *
     * @return string
     */
    private function generiereRechnungsnummer(): string
    {
        $jahr = now()->year;

        // Letzte Rechnungsnummer des aktuellen Jahres ermitteln
        $letzte = Rechnung::where('rechnungsnummer', 'like', "RE-%/{$jahr}")
            ->orderByDesc('id')
            ->value('rechnungsnummer');

        // Naechste laufende Nummer berechnen (beginnend bei 1)
        $naechsteNummer = 1;
        if ($letzte) {
            // Laufende Nummer aus dem Format RE-NNN/JJJJ extrahieren
            preg_match('/RE-(\d+)\/\d{4}/', $letzte, $treffer);
            $naechsteNummer = ((int) ($treffer[1] ?? 0)) + 1;
        }

        // Format: RE-001/2026
        return sprintf('RE-%03d/%d', $naechsteNummer, $jahr);
    }

    /**
     * Generiert die PDF-Rechnung aus benutzerdefinierten Daten und speichert sie.
     *
     * Die neue Signatur akzeptiert alle Custom-Daten als Array, da der Admin
     * saemtliche Inhalte in der Vorschau bearbeiten kann. Es findet keine
     * erneute Datenbankabfrage statt – die Daten kommen direkt aus dem Formular.
     *
     * @param  \App\Models\Rechnung  $rechnung   Die gespeicherte Rechnung (fuer Nummer, Datum, Zeitraum)
     * @param  array                 $pdfDaten   Alle benutzerdefinierten Felder aus dem Formular:
     *                                            - positionen:      array von Positionszeilen
     *                                            - absender:        string (Absenderzeile)
     *                                            - empfaenger_name: string (Empfaenger Firmenname)
     *                                            - adresse_zeilen:  array of strings (je eine Adresszeile)
     *                                            - anrede:          string
     *                                            - einleitung:      string
     *                                            - zahlungstext:    string
     *                                            - gruss:           string (kann \n enthalten)
     *                                            - footer_firma:    string (kann \n enthalten)
     *                                            - footer_kontakt:  string (kann \n enthalten)
     *                                            - footer_bank:     string (kann \n enthalten)
     * @return string  Relativer Pfad zur gespeicherten PDF-Datei (ab storage/app/)
     */
    private function generierePdf(
        Rechnung $rechnung,
        array $pdfDaten
    ): string {
        // PDF aus dem Blade-Template rendern, alle benutzerdefinierten Felder uebergeben
        $pdf = Pdf::loadView('admin.rechnungen.pdf', array_merge(
            compact('rechnung'),
            $pdfDaten
        ))->setPaper('A4', 'portrait');

        // Speicherordner nach Jahr anlegen, falls noch nicht vorhanden
        $ordner = 'rechnungen/' . now()->year;
        if (!file_exists(storage_path('app/' . $ordner))) {
            mkdir(storage_path('app/' . $ordner), 0755, true);
        }

        // PDF-Dateinamen erstellen (Schraegsttich ersetzen: RE-001/2026 -> RE-001-2026)
        $dateiname = str_replace('/', '-', $rechnung->rechnungsnummer);
        $pfad      = "{$ordner}/{$dateiname}.pdf";

        // PDF-Datei im Storage speichern
        $pdf->save(storage_path('app/' . $pfad));

        return $pfad;
    }
}
