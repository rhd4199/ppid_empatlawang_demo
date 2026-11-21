<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // URL a photo was downloaded from (API photos[][url]): re-sending the same album skips photos it already has.
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('source_url', 500)->nullable();
            $table->unique(['gallery_id', 'source_url']);
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropUnique(['gallery_id', 'source_url']);
            $table->dropColumn('source_url');
        });
    }
};
