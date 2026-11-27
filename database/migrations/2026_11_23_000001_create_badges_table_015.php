<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-13 Manajemen Lencana (KF-14) - Modul Gamifikasi (Man-015).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon');
            // Jenis syarat: poin, runtutan, aktivitas, atau emisi. Menentukan angka mana pada
            // tabel users dan activities yang dibandingkan dengan required_value.
            $table->string('type');
            $table->decimal('required_value', 12, 4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Galeri dan mesin pemeriksa selalu menyaring lencana yang masih aktif.
            $table->index(['is_active', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
