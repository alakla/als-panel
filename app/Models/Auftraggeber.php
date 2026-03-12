<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Auftraggeber-Model
 *
 * Repräsentiert ein Kundenunternehmen, an das Mitarbeitende vermittelt werden.
 * Der Stundensatz wird pro Tätigkeit festgelegt (nicht mehr beim Auftraggeber).
 *
 * Datenbankname wird explizit angegeben, da Laravel sonst 'auftraggebers' verwenden würde.
 */
class Auftraggeber extends Model
{
    // Expliziter Tabellenname
    protected $table = 'auftraggeber';

    // Felder, die per Massenverarbeitung befüllt werden dürfen
    protected $fillable = [
        'firmenname',
        'ansprechpartner',
        'adresse',
        'email',
        'telefon',
        'is_active',
    ];

    /**
     * Beziehung zu den Zeiterfassungen (1:n)
     * Ein Auftraggeber kann viele Zeiteinträge von verschiedenen Mitarbeitenden haben.
     */
    public function zeiterfassungen()
    {
        return $this->hasMany(Zeiterfassung::class);
    }

    /**
     * Beziehung zu den Rechnungen (1:n)
     * Ein Auftraggeber kann mehrere Rechnungen erhalten haben.
     */
    public function rechnungen()
    {
        return $this->hasMany(Rechnung::class);
    }
}
