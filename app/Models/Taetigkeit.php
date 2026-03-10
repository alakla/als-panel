<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Taetigkeit-Model
 *
 * Repraesentiert eine vordefinierte Taetigkeitsbeschreibung,
 * die Mitarbeitende bei der Zeiterfassung auswaehlen koennen.
 * Administratoren koennen die Liste im Admin-Bereich verwalten.
 */
class Taetigkeit extends Model
{
    protected $table = 'taetigkeiten';

    protected $fillable = [
        'name',           // Bezeichnung der Taetigkeit (z.B. "Unterhaltsreinigung")
        'reihenfolge',    // Sortierposition in der Auswahlliste (aufsteigend)
        'stundensatz',    // Abrechnungsbetrag: Stundensatz (€/Std.) oder Pauschalbetrag (€)
        'abrechnungsart', // 'stundensatz' = Satz × Stunden | 'pauschal' = Einmalbetrag
    ];
}
