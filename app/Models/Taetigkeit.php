<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Taetigkeit-Model
 *
 * Repräsentiert eine vordefinierte Tätigkeitsbeschreibung,
 * die Mitarbeitende bei der Zeiterfassung auswählen können.
 * Administratoren können die Liste im Admin-Bereich verwalten.
 */
class Taetigkeit extends Model
{
    protected $table = 'taetigkeiten';

    protected $fillable = [
        'name',           // Bezeichnung der Tätigkeit (z.B. "Unterhaltsreinigung")
        'reihenfolge',    // Sortierposition in der Auswahlliste (aufsteigend)
        'stundensatz',    // Abrechnungsbetrag: Stundensatz (€/Std.) oder Pauschalbetrag (€)
        'abrechnungsart', // 'stundensatz' = Satz × Stunden | 'pauschal' = Einmalbetrag
    ];
}
