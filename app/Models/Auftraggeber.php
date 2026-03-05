<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auftraggeber extends Model
{
    protected $fillable = [
        'firmenname', 'ansprechpartner', 'adresse', 'email', 'telefon', 'stundensatz', 'is_active',
    ];

    public function zeiterfassungen()
    {
        return $this->hasMany(Zeiterfassung::class);
    }

    public function rechnungen()
    {
        return $this->hasMany(Rechnung::class);
    }
}
