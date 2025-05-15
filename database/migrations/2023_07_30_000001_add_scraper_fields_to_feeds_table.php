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
        Schema::table('feeds', function (Blueprint $table) {
            $table->text('css_selector')->nullable()->after('last_modified');
            $table->string('content_type')->nullable()->after('css_selector');
            $table->boolean('is_scraped')->default(false)->after('content_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropColumn('css_selector');
            $table->dropColumn('content_type');
            $table->dropColumn('is_scraped');
        });
    }
};
