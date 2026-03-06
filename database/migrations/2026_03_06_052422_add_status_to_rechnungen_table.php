<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Fehlende Felder zur Rechnungstabelle hinzufuegen
 *
 * Fuegt rechnungsdatum und status hinzu, die fuer die
 * Rechnungsverwaltung und Statusverfolgung benoetigt werden.
 */
return new class extends Migration
{
    /**
     * Felder hinzufuegen.
     */
    public function up(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            // Rechnungsdatum: offizielles Datum der Rechnung
            $table->date('rechnungsdatum')->after('zeitraum_bis')->nullable();

            // Status: Bezahlungsstatus der Rechnung
            $table->enum('status', ['offen', 'bezahlt', 'storniert'])
                  ->default('offen')
                  ->after('rechnungsdatum');
        });
    }

    /**
     * Aenderungen rueckgaengig machen.
     */
    public function down(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->dropColumn(['rechnungsdatum', 'status']);
        });
    }
};
