<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Abrechnungsart zur Taetigkeiten-Tabelle hinzufuegen
 *
 * Jede Taetigkeit kann entweder nach Stundensatz (stundensatz × stunden)
 * oder als Pauschalbetrag (einmaliger Betrag, unabhaengig von den Stunden) abgerechnet werden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taetigkeiten', function (Blueprint $table) {
            // 'stundensatz' = Stundensatz × Stunden, 'pauschal' = Einmalbetrag
            $table->string('abrechnungsart', 20)->default('stundensatz')->after('stundensatz');
        });
    }

    public function down(): void
    {
        Schema::table('taetigkeiten', function (Blueprint $table) {
            $table->dropColumn('abrechnungsart');
        });
    }
};
