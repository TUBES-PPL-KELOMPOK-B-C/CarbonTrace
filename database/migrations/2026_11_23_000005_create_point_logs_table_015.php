<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-15 Keikutsertaan Tantangan - Modul Gamifikasi (Man-015).
// Bentuknya sama dengan tiruan pada migrasi stub gamifikasi (Rosi-314), yang sudah
// sesuai ERD; dipindah ke sini supaya tabel ini dimiliki modulnya sendiri.
// Papan peringkat PBI-20 dan ringkasan dashboard menjumlah `points` dan
// menyaring `created_at`; keduanya tidak boleh diganti nama.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('points')->default(0);
            $table->string('reason');
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_logs');
    }
};
