<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Telefonnummer-Spalte zur mitarbeiter-Tabelle hinzufuegen.
     */
    public function up(): void
    {
        Schema::table('mitarbeiter', function (Blueprint $table) {
            // Telefonnummer: optional, nach der Personalnummer
            $table->string('telefon', 50)->nullable()->after('personalnummer');
        });
    }

    /**
     * Migration rueckgaengig machen.
     */
    public function down(): void
    {
        Schema::table('mitarbeiter', function (Blueprint $table) {
            $table->dropColumn('telefon');
        });
    }
};
