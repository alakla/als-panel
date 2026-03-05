<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zeiterfassung extends Model
{
    protected $fillable = [
        'mitarbeiter_id', 'auftraggeber_id', 'datum', 'stunden',
        'beschreibung', 'status', 'freigegeben_von', 'freigegeben_am',
    ];

    protected $casts = [
        'datum'          => 'date',
        'freigegeben_am' => 'datetime',
    ];

    public function mitarbeiter()
    {
        return $this->belongsTo(Mitarbeiter::class);
    }

    public function auftraggeber()
    {
        return $this->belongsTo(Auftraggeber::class);
    }

    public function freigegebenVon()
    {
        return $this->belongsTo(User::class, 'freigegeben_von');
    }
}
