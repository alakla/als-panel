<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Auftraggeber-Model
 *
 * Repraesentiert ein Kundenunternehmen, an das Mitarbeitende vermittelt werden.
 * Der Stundensatz des Auftraggebers dient als Grundlage fuer die Rechnungsstellung.
 *
 * Datenbankname wird explizit angegeben, da Laravel sonst 'auftraggebers' verwenden wuerde.
 */
class Auftraggeber extends Model
{
    // Expliziter Tabellenname
    protected $table = 'auftraggeber';

    // Felder, die per Massenverarbeitung befuellt werden duerfen
    protected $fillable = [
        'firmenname',
        'ansprechpartner',
        'adresse',
        'email',
        'telefon',
        'stundensatz',
        'is_active',
    ];

    /**
     * Beziehung zu den Zeiterfassungen (1:n)
     * Ein Auftraggeber kann viele Zeiteintraege von verschiedenen Mitarbeitenden haben.
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
