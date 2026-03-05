<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitarbeiter extends Model
{
    protected $fillable = [
        'user_id', 'personalnummer', 'einstellungsdatum', 'stundenlohn', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zeiterfassungen()
    {
        return $this->hasMany(Zeiterfassung::class);
    }
}
