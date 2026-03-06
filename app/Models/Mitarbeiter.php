<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mitarbeiter-Model
 *
 * Repraesentiert einen Mitarbeitenden der Personaldienstleistungsfirma.
 * Jeder Mitarbeiter ist mit einem Benutzerkonto (User) verknuepft.
 *
 * Datenbankname wird explizit angegeben, da Laravel sonst 'mitarbeiters' verwenden wuerde.
 */
class Mitarbeiter extends Model
{
    // Expliziter Tabellenname, da die deutsche Mehrzahl nicht dem Laravel-Standard entspricht
    protected $table = 'mitarbeiter';

    // Felder, die per Massenverarbeitung befuellt werden duerfen
    protected $fillable = [
        'user_id',
        'personalnummer',
        'einstellungsdatum',
        'stundenlohn',
        'status',
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
     * Ein Mitarbeiter kann viele Zeiteintraege haben.
     */
    public function zeiterfassungen()
    {
        return $this->hasMany(Zeiterfassung::class);
    }
}
