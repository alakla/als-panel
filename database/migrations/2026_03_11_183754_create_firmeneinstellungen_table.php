<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabelle firmeneinstellungen
 *
 * Speichert die Firmendaten, die in der Rechnungsvorschau und im PDF
 * als Standardwerte erscheinen. Nur eine Zeile (id=1) wird verwendet.
 * Der Admin kann diese Daten ueber das Dashboard bearbeiten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firmeneinstellungen', function (Blueprint $table) {
            $table->id();

            // Absenderzeile unter der blauen Linie (klein, blau)
            $table->string('absender')->default('');

            // Footer-Spalte 1: Firmeninformationen (mehrzeilig)
            $table->text('footer_firma');

            // Footer-Spalte 2: Kontaktdaten (mehrzeilig)
            $table->text('footer_kontakt');

            // Footer-Spalte 3: Bankverbindung (mehrzeilig)
            $table->text('footer_bank');

            // Standard-Zahlungstext fuer Rechnungen
            $table->text('zahlungstext');

            // Standard-Grussformel
            $table->text('gruss');

            $table->timestamps();
        });

        // Einzige Zeile direkt beim Erstellen einfuegen (id=1)
        DB::table('firmeneinstellungen')->insert([
            'id'             => 1,
            'absender'       => 'ALS Dienstleistungen – Frankfurter Landstraße.91a, 64291 Darmstadt',
            'footer_firma'   => "ALS Dienstleistungen\nFrankfurter Landstraße.91a\n64291 Darmstadt\nSteuer Nr.: 00780160575",
            'footer_kontakt' => "Hasan Aljasem\nTel: 017670549424\nE-Mail: als.dienstleistungen@gmail.com",
            'footer_bank'    => "Sparkasse Darmstadt\nIBAN: DE94 5085 0150 0080 0254 91\nBIC: HELADEFIDAS",
            'zahlungstext'   => "Bitte überweisen Sie den Betrag innerhalb von 14 Tagen auf unser unten genanntes Konto, und geben Sie bitte die Rechnungsnummer als Verwendungszweck.",
            'gruss'          => "Mit freundlichen Grüßen\nALS Dienstleistungen",
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('firmeneinstellungen');
    }
};
