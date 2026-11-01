<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// KF-01: kolom yang dibutuhkan pendaftaran dan pemeriksaan akun nonaktif.
// Kolom users lain di ERD (role_id, photo, study_program, points, streak_count) menyusul bersama KF-04.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('campus', 120)->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('campus');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['campus', 'is_active']);
        });
    }
};
