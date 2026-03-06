<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Zeiterfassung-Model
 *
 * Repraesentiert einen taeglichen Arbeitszeitein trag eines Mitarbeitenden.
 * Der Eintrag durchlaeuft einen Freigabe-Workflow: offen -> freigegeben/abgelehnt.
 *
 * Datenbankname wird explizit angegeben.
 */
class Zeiterfassung extends Model
{
    // Expliziter Tabellenname
    protected $table = 'zeiterfassungen';

    // Felder, die per Massenverarbeitung befuellt werden duerfen
    protected $fillable = [
        'mitarbeiter_id',
        'auftraggeber_id',
        'datum',
        'stunden',
        'beschreibung',
        'status',
        'freigegeben_von',
        'freigegeben_am',
    ];

    // Automatische Typumwandlung fuer Datums- und Zeitfelder
    protected $casts = [
        'datum'          => 'date',
        'freigegeben_am' => 'datetime',
    ];

    /**
     * Beziehung zum Mitarbeitenden (n:1)
     * Jeder Zeiteintrag gehoert zu genau einem Mitarbeitenden.
     */
    public function mitarbeiter()
    {
        return $this->belongsTo(Mitarbeiter::class);
    }

    /**
     * Beziehung zum Auftraggeber (n:1)
     * Jeder Zeiteintrag gehoert zu genau einem Auftraggeber.
     */
    public function auftraggeber()
    {
        return $this->belongsTo(Auftraggeber::class);
    }

    /**
     * Beziehung zum freigebenden Administrator (n:1)
     * Gibt den Admin zurueck, der den Eintrag freigegeben hat.
     */
    public function freigegebenVon()
    {
        return $this->belongsTo(User::class, 'freigegeben_von');
    }
}
