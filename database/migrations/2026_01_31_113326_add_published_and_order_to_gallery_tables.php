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
        Schema::table('galleries', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('description');
        });

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('caption');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn('order');
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
