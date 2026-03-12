<?php

/**
 * Migration: Erstellt die Tabelle lohnabrechnungen
 *
 * Speichert pro Mitarbeitenden und Monat eine Gehaltsabrechnung.
 * Wird angelegt, wenn der Admin das Gehalt als bezahlt markiert.
 *
 * Felder:
 *   - mitarbeiter_id: Verweis auf den Mitarbeitenden
 *   - monat:          Abrechnungsmonat im Format YYYY-MM (z. B. 2026-03)
 *   - stunden:        Freigegebene Stunden im Monat (zum Zeitpunkt der Auszahlung)
 *   - betrag:         Ausgezahlter Betrag in Euro
 *   - bezahlt_am:     Zeitpunkt der Zahlung
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lohnabrechnungen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitarbeiter_id')->constrained('mitarbeiter')->cascadeOnDelete();
            // Monat im Format YYYY-MM – eindeutig pro Mitarbeitenden
            $table->char('monat', 7);
            // Stunden und Betrag zum Zeitpunkt der Auszahlung festhalten
            $table->decimal('stunden', 8, 2)->default(0);
            $table->decimal('betrag',  10, 2)->default(0);
            $table->timestamp('bezahlt_am')->useCurrent();
            $table->timestamps();

            // Pro Mitarbeitenden nur eine Abrechnung pro Monat erlaubt
            $table->unique(['mitarbeiter_id', 'monat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lohnabrechnungen');
    }
};
