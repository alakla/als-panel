<?php

/**
 * Model: Auftrag
 *
 * Repräsentiert einen Arbeitsauftrag, den der Admin einem Mitarbeitenden zuweist.
 *
 * Status-Ablauf:
 *   gesendet   -> Admin hat den Auftrag versendet, Mitarbeitender sieht ihn
 *   bestätigt  -> Mitarbeitender hat den Auftrag nach Ausführung bestätigt;
 *                 dabei wird automatisch ein Zeiteintrag (status=offen) erstellt
 *
 * Beziehungen:
 *   - gehört zu: Mitarbeiter, Auftraggeber, Tätigkeit
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Auftrag extends Model
{
    /** Tabellenname (deutschsprachig, daher explizit angegeben) */
    protected $table = 'auftraege';

    /** Massenzuweisbare Felder */
    protected $fillable = [
        'mitarbeiter_id',
        'auftraggeber_id',
        'taetigkeit_id',
        'datum',
        'von',
        'bis',
        'pause',
        'status',
        'zeit_geaendert',
        'zeit_aenderung_info',
    ];

    /**
     * Typ-Umwandlungen für Attribute
     */
    protected function casts(): array
    {
        return [
            'datum'          => 'date',
            'pause'          => 'boolean',
            'zeit_geaendert' => 'boolean',
        ];
    }

    /* ----------------------------------------------------------------
     * Beziehungen
     * ---------------------------------------------------------------- */

    /**
     * Zugewiesener Mitarbeitender
     */
    public function mitarbeiter()
    {
        return $this->belongsTo(Mitarbeiter::class);
    }

    /**
     * Einsatzfirma (Auftraggeber)
     */
    public function auftraggeber()
    {
        return $this->belongsTo(Auftraggeber::class);
    }

    /**
     * Art der Tätigkeit
     */
    public function taetigkeit()
    {
        return $this->belongsTo(Taetigkeit::class);
    }

    /* ----------------------------------------------------------------
     * Hilfsmethoden
     * ---------------------------------------------------------------- */

    /**
     * Berechnet die Arbeitsstunden aus Von/Bis-Zeiten.
     * Wenn Pause = true, werden 30 Minuten abgezogen.
     *
     * @return float Gerundete Arbeitsstunden
     */
    public function berechneteStunden(): float
    {
        // Zeiten parsen (Format H:i:s aus der Datenbank)
        $von     = Carbon::createFromFormat('H:i:s', $this->von);
        $bis     = Carbon::createFromFormat('H:i:s', $this->bis);
        $minuten = $von->diffInMinutes($bis);

        // 30 Minuten Pause abziehen falls angegeben
        if ($this->pause) {
            $minuten -= 30;
        }

        // Auf 2 Nachkommastellen runden
        return round(max($minuten, 0) / 60, 2);
    }

    /**
     * Gibt die Von-Zeit im Format HH:MM zurück
     */
    public function vonFormatiert(): string
    {
        return substr($this->von, 0, 5);
    }

    /**
     * Gibt die Bis-Zeit im Format HH:MM zurück
     */
    public function bisFormatiert(): string
    {
        return substr($this->bis, 0, 5);
    }
}
