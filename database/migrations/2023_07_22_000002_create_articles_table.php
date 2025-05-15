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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->index();
            $table->string('title');
            $table->string('author')->nullable();
            $table->longText('content')->nullable();
            $table->string('url');
            $table->string('comments_url')->nullable();
            $table->timestamp('date');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->string('hash')->index();
            $table->timestamps();

            // Make sure we don't duplicate articles in the same feed
            $table->unique(['hash', 'feed_id']);
        });

        // Creating indexes for common queries
        Schema::table('articles', function (Blueprint $table) {
            $table->index(['feed_id', 'is_read']);
            $table->index(['feed_id', 'is_favorite']);
            $table->index(['feed_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
