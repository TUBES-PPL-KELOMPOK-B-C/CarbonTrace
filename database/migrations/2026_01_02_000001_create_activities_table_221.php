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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('emission_factor_id')->constrained('emission_factors')->restrictOnDelete();
            $table->decimal('amount', 12, 4);
            $table->decimal('emission', 12, 4);
            $table->date('activity_date');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            // Indeks gabungan untuk skalabilitas kueri riwayat berdasarkan rentang tanggal (KNF-07)
            $table->index(['user_id', 'activity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
