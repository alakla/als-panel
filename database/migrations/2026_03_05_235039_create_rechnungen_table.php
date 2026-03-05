<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rechnungen', function (Blueprint $table) {
            $table->id();
            $table->string('rechnungsnummer')->unique();
            $table->foreignId('auftraggeber_id')->constrained('auftraggeber')->onDelete('cascade');
            $table->date('zeitraum_von');
            $table->date('zeitraum_bis');
            $table->decimal('nettobetrag', 10, 2);
            $table->decimal('mwst_betrag', 10, 2);
            $table->decimal('gesamtbetrag', 10, 2);
            $table->string('pdf_pfad')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rechnungen');
    }
};
