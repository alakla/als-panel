<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Taetigkeiten-Tabelle erstellen
 *
 * Speichert die vordefinierten Taetigkeitsbeschreibungen,
 * die Mitarbeitende bei der Zeiterfassung auswaehlen koennen.
 * Administratoren koennen diese Liste jederzeit anpassen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taetigkeiten', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // Bezeichnung der Taetigkeit
            $table->integer('reihenfolge')->default(0); // Sortierreihenfolge in der Auswahlliste
            $table->timestamps();
        });

        // Initiale Taetigkeiten einfuegen
        DB::table('taetigkeiten')->insert([
            ['name' => 'Unterhaltsreinigung', 'reihenfolge' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Außenreinigung',       'reihenfolge' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Theke Reinigung',      'reihenfolge' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reinigungsarbeit',     'reihenfolge' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kasse',                'reihenfolge' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Metzgerei',            'reihenfolge' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('taetigkeiten');
    }
};
