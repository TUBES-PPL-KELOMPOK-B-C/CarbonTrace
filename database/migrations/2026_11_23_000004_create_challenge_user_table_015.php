<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-15 Keikutsertaan Tantangan - Modul Gamifikasi (Man-015).
// Bentuknya sama dengan tiruan pada migrasi stub gamifikasi (Rosi-314), yang sudah
// sesuai ERD; dipindah ke sini supaya tabel ini dimiliki modulnya sendiri.
// Papan peringkat PBI-20 merata-ratakan kolom `progress`.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->string('status')->default('aktif');
            $table->decimal('progress', 12, 4)->default(0.0000);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->primary(['user_id', 'challenge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_user');
    }
};
