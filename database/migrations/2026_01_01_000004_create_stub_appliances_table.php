<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appliances')) {
            Schema::create('appliances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('emission_factor_id')->constrained('emission_factors')->cascadeOnDelete();
                $table->string('name', 100);
                $table->decimal('wattage', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('appliances');
    }
};
