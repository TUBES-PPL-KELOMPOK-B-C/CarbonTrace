<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// KF-25 Kuis Awal (Alvin-111): kapan pengguna memilih melewati kuis awal, supaya ia tidak
// diarahkan ke kuis lagi setiap kali masuk. Kolom tambahan yang boleh kosong; tidak mengubah
// kolom lain di tabel users.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'quiz_skipped_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('quiz_skipped_at')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'quiz_skipped_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('quiz_skipped_at');
            });
        }
    }
};
