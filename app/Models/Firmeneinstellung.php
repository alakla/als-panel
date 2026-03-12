<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Firmeneinstellung
 *
 * Repräsentiert die Firmen-Stammdaten die in Rechnungen erscheinen.
 * Es existiert immer genau eine Zeile (id=1) in der Tabelle.
 * Änderungen des Admins werden direkt in dieser Zeile gespeichert.
 *
 * Felder:
 *   absender       - Absenderzeile unter der blauen Linie
 *   footer_firma   - Firmeninfo im Footer (mehrzeilig)
 *   footer_kontakt - Kontaktdaten im Footer (mehrzeilig)
 *   footer_bank    - Bankverbindung im Footer (mehrzeilig)
 *   zahlungstext   - Standard-Zahlungstext für Rechnungen
 *   gruss          - Standard-Grussformel
 */
class Firmeneinstellung extends Model
{
    /** Tabellenname explizit angeben (Laravel würde sonst englische Form verwenden) */
    protected $table = 'firmeneinstellungen';

    /** Alle Felder sind mass-assignable */
    protected $fillable = [
        'absender',
        'footer_firma',
        'footer_kontakt',
        'footer_bank',
        'zahlungstext',
        'gruss',
        'anrede',
        'einleitung',
    ];

    /**
     * Gibt die einzige Einstellungszeile zurück (id=1).
     * Existiert sie nicht, wird sie mit Standardwerten angelegt.
     *
     * @return static
     */
    public static function laden(): static
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'absender'       => 'ALS Dienstleistungen – Frankfurter Landstraße.91a, 64291 Darmstadt',
                'footer_firma'   => "Frankfurter Landstraße.91a\n64291 Darmstadt\nSteuer Nr.: 00780160575\nWebseite: www.als-dl.de",
                'footer_kontakt' => "Hasan Aljasem\nTel: 017670549424\nE-Mail: info@als-dl.de",
                'footer_bank'    => "Sparkasse Darmstadt\nIBAN: DE94 5085 0150 0080 0254 91\nBIC: HELADEFIDAS",
                'zahlungstext'   => "Bitte überweisen Sie den Betrag innerhalb von 14 Tagen auf unser unten genanntes Konto, und geben Sie bitte die Rechnungsnummer als Verwendungszweck.",
                'gruss'          => "Mit freundlichen Grüßen\nALS Dienstleistungen",
                'anrede'         => 'Sehr geehrte Damen und Herren,',
                'einleitung'     => 'Hiermit stellen wir Ihnen folgende Leistungen in Rechnung:',
            ]
        );
    }
}
