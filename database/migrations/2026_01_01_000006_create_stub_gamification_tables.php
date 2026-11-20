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
        if (!Schema::hasTable('challenges')) {
            Schema::create('challenges', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('points')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('challenge_user')) {
            Schema::create('challenge_user', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
                $table->string('status')->default('aktif');
                $table->decimal('progress', 12, 4)->default(0.0000);
                $table->timestamp('joined_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('point_logs')) {
            Schema::create('point_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->integer('points')->default(0);
                $table->string('reason');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_logs');
        Schema::dropIfExists('challenge_user');
        Schema::dropIfExists('challenges');
    }
};
