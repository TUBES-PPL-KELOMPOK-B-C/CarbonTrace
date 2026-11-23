<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-13 Manajemen Lencana (KF-14) - Modul Gamifikasi (Man-015).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->timestamp('earned_at')->nullable();
            $table->timestamps();

            // Dua aktivitas yang tercatat hampir bersamaan bisa memicu lencana yang sama dua kali.
            // Kunci unik ini yang menolaknya di tingkat basis data, bukan pemeriksaan di PHP.
            $table->unique(['user_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_user');
    }
};
