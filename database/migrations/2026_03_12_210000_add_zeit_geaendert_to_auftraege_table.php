<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: zeit_geaendert-Spalte zu auftraege hinzufügen
 *
 * Wird auf true gesetzt, wenn der Mitarbeitende beim Bestätigen
 * die vom Admin ursprünglich eingetragenen Zeiten (Von/Bis/Pause)
 * abgeändert hat. Dient dem Admin als Hinweis zur Überprüfung.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auftraege', function (Blueprint $table) {
            // Standard: false (keine Änderung durch Mitarbeitenden)
            $table->boolean('zeit_geaendert')->default(false)->after('pause');
        });
    }

    public function down(): void
    {
        Schema::table('auftraege', function (Blueprint $table) {
            $table->dropColumn('zeit_geaendert');
        });
    }
};
