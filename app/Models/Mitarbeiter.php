<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mitarbeiter-Model
 *
 * Repräsentiert einen Mitarbeitenden der Personaldienstleistungsfirma.
 * Jeder Mitarbeiter ist mit einem Benutzerkonto (User) verknüpft.
 *
 * Datenbankname wird explizit angegeben, da Laravel sonst 'mitarbeiters' verwenden würde.
 */
class Mitarbeiter extends Model
{
    // Expliziter Tabellenname, da die deutsche Mehrzahl nicht dem Laravel-Standard entspricht
    protected $table = 'mitarbeiter';

    // Felder, die per Massenverarbeitung befüllt werden dürfen
    protected $fillable = [
        'user_id',
        'personalnummer',
        'telefon',
        'einstellungsdatum',
        'stundenlohn',
        'status',
    ];

    // Automatische Typumwandlung: einstellungsdatum wird als Carbon-Datum behandelt
    protected $casts = [
        'einstellungsdatum' => 'date',
    ];

    /**
     * Beziehung zum Benutzerkonto (1:1)
     * Jeder Mitarbeiter hat genau ein Login-Konto.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Beziehung zu den Zeiterfassungen (1:n)
     * Ein Mitarbeiter kann viele Zeiteinträge haben.
     */
    public function zeiterfassungen()
    {
        return $this->hasMany(Zeiterfassung::class);
    }

    /**
     * Beziehung zu den Arbeitsaufträgen (1:n)
     * Ein Mitarbeiter kann viele zugewiesene Aufträge haben.
     */
    public function auftraege()
    {
        return $this->hasMany(Auftrag::class);
    }
}
