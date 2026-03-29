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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            $table->string('playlist_id', 64)->unique();
 
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->string('thumbnail_url', 1000)->nullable();
            $table->string('channel_name', 255)->nullable();
            $table->string('category', 100);
            $table->string('search_query', 500)->nullable();
            $table->unsignedSmallInteger('video_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->string('playlist_duration')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
