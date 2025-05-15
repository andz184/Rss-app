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
        Schema::create('agent_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_task_id')->constrained()->onDelete('cascade');
            $table->string('action_type'); // screenshot, keyboard, mouse, etc.
            $table->json('action_data');
            $table->json('result')->nullable();
            $table->string('status')->default('pending'); // pending, executed, failed
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_actions');
    }
};
