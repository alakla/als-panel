<?php

/**
 * Migration: Auftraege-Tabelle erstellen
 *
 * Speichert Arbeitsauftraege, die vom Admin an Mitarbeitende gesendet werden.
 * Status-Ablauf: gesendet -> bestaetigt (nach Ausfuehrung durch Mitarbeitenden)
 * Nach Bestaetigung wird automatisch ein Zeiteintrag (offen) in Zeiterfassung erstellt.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabelle anlegen
     */
    public function up(): void
    {
        Schema::create('auftraege', function (Blueprint $table) {
            $table->id();

            // Zugewiesener Mitarbeitender
            $table->foreignId('mitarbeiter_id')
                  ->constrained('mitarbeiter')
                  ->cascadeOnDelete();

            // Einsatzfirma
            $table->foreignId('auftraggeber_id')
                  ->constrained('auftraggeber')
                  ->cascadeOnDelete();

            // Art der Taetigkeit (aus Taetigkeiten-Liste)
            $table->foreignId('taetigkeit_id')
                  ->constrained('taetigkeiten')
                  ->cascadeOnDelete();

            // Datum des Einsatzes (darf nicht in der Vergangenheit liegen)
            $table->date('datum');

            // Arbeitszeit: von X bis Y
            $table->time('von');
            $table->time('bis');

            // Gibt es eine 30-minuetige Pause? (wird bei Stundenberechnung abgezogen)
            $table->boolean('pause')->default(false);

            // Status: gesendet (Admin hat gesendet) | bestaetigt (Mitarbeitender hat bestaetigt)
            $table->enum('status', ['gesendet', 'bestaetigt'])->default('gesendet');

            $table->timestamps();
        });
    }

    /**
     * Tabelle entfernen (Rollback)
     */
    public function down(): void
    {
        Schema::dropIfExists('auftraege');
    }
};
