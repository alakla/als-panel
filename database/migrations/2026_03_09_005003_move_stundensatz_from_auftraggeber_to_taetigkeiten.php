<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Stundensatz von auftraggeber zu taetigkeiten verschieben
 *
 * Begruendung: Der Stundensatz gehoert logisch zur Taetigkeit
 * (jede Taetigkeit hat ihren eigenen Satz), nicht zum Auftraggeber.
 *
 * Zusaetzlich: taetigkeit_id zu zeiterfassungen hinzufuegen,
 * damit der Stundensatz bei der Rechnungsstellung korrekt
 * aus der Taetigkeit gelesen werden kann.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Stundensatz zur Taetigkeiten-Tabelle hinzufuegen
        Schema::table('taetigkeiten', function (Blueprint $table) {
            $table->decimal('stundensatz', 8, 2)->default(0)->after('reihenfolge');
        });

        // Taetigkeit-Referenz zur Zeiterfassung hinzufuegen (nullable fuer bestehende Eintraege)
        Schema::table('zeiterfassungen', function (Blueprint $table) {
            $table->foreignId('taetigkeit_id')->nullable()->after('auftraggeber_id')
                  ->constrained('taetigkeiten')->nullOnDelete();
        });

        // Stundensatz aus der Auftraggeber-Tabelle entfernen
        Schema::table('auftraggeber', function (Blueprint $table) {
            $table->dropColumn('stundensatz');
        });
    }

    public function down(): void
    {
        // Rueckgaengig: stundensatz wieder zur auftraggeber-Tabelle hinzufuegen
        Schema::table('auftraggeber', function (Blueprint $table) {
            $table->decimal('stundensatz', 8, 2)->default(0)->after('telefon');
        });

        // taetigkeit_id aus zeiterfassungen entfernen
        Schema::table('zeiterfassungen', function (Blueprint $table) {
            $table->dropForeign(['taetigkeit_id']);
            $table->dropColumn('taetigkeit_id');
        });

        // stundensatz aus taetigkeiten entfernen
        Schema::table('taetigkeiten', function (Blueprint $table) {
            $table->dropColumn('stundensatz');
        });
    }
};
