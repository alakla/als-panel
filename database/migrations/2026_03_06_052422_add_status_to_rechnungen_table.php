<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Fehlende Felder zur Rechnungstabelle hinzufuegen
 *
 * Ergaenzung der rechnungen-Tabelle um zwei fehlende Felder:
 *   - rechnungsdatum: Offizielles Datum der Rechnung (nicht zwingend = Erstellungsdatum)
 *   - status:         Zahlungsstatus der Rechnung (offen / bezahlt / storniert)
 *
 * Hintergrund: Diese Felder wurden erst nach der initialen Rechnungsmigration
 * benoetigt, als die Bezahlverfolgung und das Rechnungsdatum ergaenzt wurden.
 *
 * Ausfuehren mit: php artisan migrate
 * Rueckgaengig:   php artisan migrate:rollback
 */
return new class extends Migration
{
    /**
     * Felder hinzufuegen (up = Vorwaertsmigration).
     *
     * Die Felder werden nach 'zeitraum_bis' eingefuegt,
     * um die logische Reihenfolge in der Tabelle beizubehalten.
     */
    public function up(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            // rechnungsdatum: offizielles Rechnungsdatum, nullable fuer Altdaten
            $table->date('rechnungsdatum')->after('zeitraum_bis')->nullable();

            // status: Enum-Feld mit drei moeglichen Werten
            // 'offen'     = neu erstellt, Zahlung ausstehend (Standard)
            // 'bezahlt'   = Zahlung eingegangen, vom Admin bestaetigt
            // 'storniert' = Rechnung wurde storniert (fuer kuenftige Erweiterung)
            $table->enum('status', ['offen', 'bezahlt', 'storniert'])
                  ->default('offen')
                  ->after('rechnungsdatum');
        });
    }

    /**
     * Aenderungen rueckgaengig machen (down = Rollback).
     *
     * Entfernt beide Felder wieder aus der Tabelle.
     * ACHTUNG: Dabei gehen alle gespeicherten Status- und Datumswerte verloren!
     */
    public function down(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->dropColumn(['rechnungsdatum', 'status']);
        });
    }
};
