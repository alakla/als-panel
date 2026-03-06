<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rechnung-Model
 *
 * Repraesentiert eine erstellte Rechnung fuer einen Auftraggeber.
 * Die Rechnung enthaelt Nettobetrag, MwSt und Gesamtbetrag
 * sowie den Pfad zur generierten PDF-Datei.
 *
 * Datenbankname wird explizit angegeben.
 */
class Rechnung extends Model
{
    // Expliziter Tabellenname
    protected $table = 'rechnungen';

    // Felder, die per Massenverarbeitung befuellt werden duerfen
    protected $fillable = [
        'rechnungsnummer',
        'auftraggeber_id',
        'zeitraum_von',
        'zeitraum_bis',
        'nettobetrag',
        'mwst_betrag',
        'gesamtbetrag',
        'pdf_pfad',
    ];

    // Automatische Typumwandlung fuer Datumsfelder
    protected $casts = [
        'zeitraum_von' => 'date',
        'zeitraum_bis' => 'date',
    ];

    /**
     * Beziehung zum Auftraggeber (n:1)
     * Jede Rechnung gehoert zu genau einem Auftraggeber.
     */
    public function auftraggeber()
    {
        return $this->belongsTo(Auftraggeber::class);
    }
}
