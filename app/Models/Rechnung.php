<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rechnung-Model
 *
 * Repräsentiert eine erstellte Rechnung für einen Auftraggeber.
 *
 * Rechnungserstellungsprozess:
 *  1. Admin wählt Auftraggeber und Abrechnungszeitraum
 *  2. Alle freigegebenen Zeiteintraege des Zeitraums werden summiert
 *  3. Betraege werden automatisch berechnet:
 *     - Nettobetrag  = Gesamtstunden * Stundensatz des Auftraggebers
 *     - mwst_betrag  = Nettobetrag * 0.19 (19% gesetzlicher MwSt-Satz)
 *     - Gesamtbetrag = Nettobetrag + mwst_betrag
 *  4. Eine PDF-Datei wird unter storage/app/rechnungen/JJJJ/ gespeichert
 *
 * Status-Werte:
 *  - 'offen'     = Rechnung erstellt, aber noch nicht bezahlt (Standardwert)
 *  - 'bezahlt'   = Zahlung eingegangen, vom Admin bestaetigt
 *  - 'storniert' = Rechnung wurde storniert (für künftige Erweiterung)
 *
 * Rechnungsnummer-Format: RE-JJJJ-NNNN (z.B. RE-2026-0001)
 *
 * Datenbankname wird explizit angegeben, da Laravel sonst 'rechnungs'
 * als Pluralform verwenden würde (fehlerhafte englische Ableitung).
 */
class Rechnung extends Model
{
    // Expliziter Tabellenname (deutsche Schreibweise beibehalten)
    protected $table = 'rechnungen';

    // Felder, die per Massenverarbeitung (create/update) befüllt werden dürfen
    protected $fillable = [
        'rechnungsnummer',   // Eindeutige Nummer im Format RE-JJJJ-NNNN
        'auftraggeber_id',   // Fremdschluessel zur auftraggeber-Tabelle
        'zeitraum_von',      // Startdatum des Abrechnungszeitraums
        'zeitraum_bis',      // Enddatum des Abrechnungszeitraums
        'rechnungsdatum',    // Offizielles Rechnungsdatum (i.d.R. Erstellungsdatum)
        'nettobetrag',       // Betrag ohne MwSt (in Euro)
        'mwst_betrag',       // MwSt-Anteil (19% des Nettobetrags)
        'gesamtbetrag',      // Bruttobetrag (Netto + MwSt)
        'status',            // Zahlungsstatus: offen / bezahlt / storniert
        'pdf_pfad',          // Relativer Pfad zur PDF-Datei in storage/app/
    ];

    // Automatische Typumwandlung: Datumsfelder werden als Carbon-Objekte behandelt,
    // sodass ->format() direkt aufgerufen werden kann
    protected $casts = [
        'zeitraum_von'   => 'date',
        'zeitraum_bis'   => 'date',
        'rechnungsdatum' => 'date', // nullable – kann leer sein bei alten Rechnungen
    ];

    /**
     * Beziehung zum Auftraggeber (n:1)
     *
     * Jede Rechnung gehört zu genau einem Auftraggeber.
     * Der Auftraggeber bestimmt den Stundensatz für die Berechnung.
     * Zugriff: $rechnung->auftraggeber->firmenname
     */
    public function auftraggeber()
    {
        return $this->belongsTo(Auftraggeber::class);
    }
}
