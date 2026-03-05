<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rechnung extends Model
{
    protected $fillable = [
        'rechnungsnummer', 'auftraggeber_id', 'zeitraum_von', 'zeitraum_bis',
        'nettobetrag', 'mwst_betrag', 'gesamtbetrag', 'pdf_pfad',
    ];

    protected $casts = [
        'zeitraum_von' => 'date',
        'zeitraum_bis' => 'date',
    ];

    public function auftraggeber()
    {
        return $this->belongsTo(Auftraggeber::class);
    }
}
