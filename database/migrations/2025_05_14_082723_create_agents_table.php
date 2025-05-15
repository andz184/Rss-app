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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('model_provider')->default('anthropic');
            $table->string('model_name')->default('claude-3-7-sonnet-20250219');
            $table->string('endpoint_url')->nullable();
            $table->text('api_key_encrypted')->nullable();
            $table->string('grounding_model_provider')->nullable();
            $table->string('grounding_model_name')->nullable();
            $table->integer('grounding_resize_width')->default(1366);
            $table->integer('grounding_resize_height')->nullable();
            $table->string('platform')->default('windows');
            $table->string('observation_type')->default('screenshot');
            $table->string('search_engine')->default('Perplexica');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
