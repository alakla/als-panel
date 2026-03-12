<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: zeit_aenderung_info-Spalte zu auftraege hinzufügen
 *
 * Speichert eine lesbare Beschreibung der Zeitänderung durch den Mitarbeitenden,
 * z. B. "Von: 08:00 → 09:00 | Bis: 16:00 → 17:30 | Pause: Nein → Ja"
 * Wird im Admin-Tooltip beim "geändert"-Badge angezeigt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auftraege', function (Blueprint $table) {
            $table->string('zeit_aenderung_info')->nullable()->after('zeit_geaendert');
        });
    }

    public function down(): void
    {
        Schema::table('auftraege', function (Blueprint $table) {
            $table->dropColumn('zeit_aenderung_info');
        });
    }
};
