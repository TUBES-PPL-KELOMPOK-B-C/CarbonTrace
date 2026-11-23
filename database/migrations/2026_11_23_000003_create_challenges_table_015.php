<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-14 Manajemen Tantangan - Modul Gamifikasi (Man-015).
// Menggantikan tiruan `challenges` pada migrasi stub gamifikasi (Rosi-314), yang
// hanya memiliki title, description, dan points sehingga tidak cukup untuk
// periode tantangan maupun target penurunannya.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('reduction_target', 12, 4);
            $table->integer('reward_points')->default(0);
            $table->timestamps();

            // Tiga tab status pada /admin/tantangan menyaring periode terhadap hari ini.
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
