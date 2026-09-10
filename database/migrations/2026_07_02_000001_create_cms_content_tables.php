<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('general');
            $table->boolean('pinned')->default(false);
            $table->boolean('is_published')->default(true);
            $table->date('published_at');
            $table->json('title');
            $table->json('body');
            $table->timestamps();
        });

        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category')->default('schoolNews');
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(true);
            $table->date('published_at');
            $table->json('author');
            $table->json('title');
            $table->json('excerpt');
            $table->json('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
        Schema::dropIfExists('announcements');
    }
};
