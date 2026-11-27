<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-12 Laporan Jejak Karbon (Alvin-022). Skema persis seperti di README.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('period_start');
            $table->string('period_end');
            $table->string('category_scope');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};
