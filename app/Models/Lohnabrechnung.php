<?php

/**
 * Model: Lohnabrechnung
 *
 * Repräsentiert eine abgeschlossene Gehaltsauszahlung für einen Mitarbeitenden
 * in einem bestimmten Monat.
 *
 * Beziehungen:
 *   - gehört zu: Mitarbeiter
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lohnabrechnung extends Model
{
    /** Tabellenname (deutschsprachig, daher explizit angegeben) */
    protected $table = 'lohnabrechnungen';

    /** Massenzuweisbare Felder */
    protected $fillable = [
        'mitarbeiter_id',
        'monat',
        'stunden',
        'betrag',
        'bezahlt_am',
    ];

    /**
     * Typ-Umwandlungen für Attribute
     */
    protected function casts(): array
    {
        return [
            'bezahlt_am' => 'datetime',
            'stunden'    => 'float',
            'betrag'     => 'float',
        ];
    }

    /**
     * Zugehöriger Mitarbeitender
     */
    public function mitarbeiter()
    {
        return $this->belongsTo(Mitarbeiter::class);
    }
}
