<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auftraggeber', function (Blueprint $table) {
            $table->id();
            $table->string('firmenname');
            $table->string('ansprechpartner');
            $table->text('adresse');
            $table->string('email');
            $table->string('telefon')->nullable();
            $table->decimal('stundensatz', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auftraggeber');
    }
};
