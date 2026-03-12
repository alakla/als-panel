<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Anrede und Einleitung zur Tabelle firmeneinstellungen hinzufügen.
 *
 * Diese Felder waren bisher als feste Werte in der Rechnungsvorschau eingebettet.
 * Jetzt können sie vom Admin in den Einstellungen angepasst werden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firmeneinstellungen', function (Blueprint $table) {
            // Anrede-Zeile (z.B. "Sehr geehrte Damen und Herren,")
            $table->string('anrede')->default('Sehr geehrte Damen und Herren,')->after('gruss');

            // Einleitungssatz nach der Anrede
            $table->string('einleitung', 500)->default('Hiermit stellen wir Ihnen folgende Leistungen in Rechnung:')->after('anrede');
        });

        // Bestehende Zeile (id=1) mit Standardwerten befüllen
        DB::table('firmeneinstellungen')->where('id', 1)->update([
            'anrede'     => 'Sehr geehrte Damen und Herren,',
            'einleitung' => 'Hiermit stellen wir Ihnen folgende Leistungen in Rechnung:',
        ]);
    }

    public function down(): void
    {
        Schema::table('firmeneinstellungen', function (Blueprint $table) {
            $table->dropColumn(['anrede', 'einleitung']);
        });
    }
};
