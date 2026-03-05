<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('zeiterfassungen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitarbeiter_id')->constrained('mitarbeiter')->onDelete('cascade');
            $table->foreignId('auftraggeber_id')->constrained('auftraggeber')->onDelete('cascade');
            $table->date('datum');
            $table->decimal('stunden', 4, 2);
            $table->text('beschreibung')->nullable();
            $table->enum('status', ['offen', 'freigegeben', 'abgelehnt'])->default('offen');
            $table->foreignId('freigegeben_von')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('freigegeben_am')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('zeiterfassungen');
    }
};
