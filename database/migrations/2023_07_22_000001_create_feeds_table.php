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
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('feed_url')->index();
            $table->string('site_url')->nullable();
            $table->text('description')->nullable();
            $table->string('language')->nullable();
            $table->string('icon')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('error_count')->default(0);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('etag')->nullable(); // For HTTP caching
            $table->string('last_modified')->nullable(); // For HTTP caching
            $table->timestamps();

            // Each URL must be unique per user
            $table->unique(['feed_url', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
