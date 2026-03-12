<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Zeiterfassung-Model
 *
 * Repräsentiert einen täglichen Arbeitszeiteintrags eines Mitarbeitenden.
 * Der Eintrag durchläuft einen Freigabe-Workflow: offen -> freigegeben/abgelehnt.
 *
 * Datenbankname wird explizit angegeben.
 */
class Zeiterfassung extends Model
{
    // Expliziter Tabellenname
    protected $table = 'zeiterfassungen';

    // Felder, die per Massenverarbeitung befüllt werden dürfen
    protected $fillable = [
        'mitarbeiter_id',
        'auftraggeber_id',
        'taetigkeit_id',    // Referenz zur Tätigkeit (für Stundensatz bei Rechnung)
        'datum',
        'stunden',
        'beschreibung',
        'status',
        'freigegeben_von',
        'freigegeben_am',
    ];

    // Automatische Typumwandlung für Datums- und Zeitfelder
    protected $casts = [
        'datum'          => 'date',
        'freigegeben_am' => 'datetime',
    ];

    /**
     * Beziehung zum Mitarbeitenden (n:1)
     * Jeder Zeiteintrag gehört zu genau einem Mitarbeitenden.
     */
    public function mitarbeiter()
    {
        return $this->belongsTo(Mitarbeiter::class);
    }

    /**
     * Beziehung zum Auftraggeber (n:1)
     * Jeder Zeiteintrag gehört zu genau einem Auftraggeber.
     */
    public function auftraggeber()
    {
        return $this->belongsTo(Auftraggeber::class);
    }

    /**
     * Beziehung zur Tätigkeit (n:1)
     * Gibt die Tätigkeit zurück – wird für den Stundensatz in der Rechnung benötigt.
     */
    public function taetigkeit()
    {
        return $this->belongsTo(Taetigkeit::class);
    }

    /**
     * Beziehung zum freigebenden Administrator (n:1)
     * Gibt den Admin zurück, der den Eintrag freigegeben hat.
     */
    public function freigegebenVon()
    {
        return $this->belongsTo(User::class, 'freigegeben_von');
    }
}
