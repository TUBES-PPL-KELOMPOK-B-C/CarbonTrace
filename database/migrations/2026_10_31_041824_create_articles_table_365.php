
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('article_category_id')->constrained('article_categories')->restrictOnDelete();
            $table->string('title', 160);
            $table->string('slug')->unique();
            $table->string('summary', 300);
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('status')->default('draf');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
