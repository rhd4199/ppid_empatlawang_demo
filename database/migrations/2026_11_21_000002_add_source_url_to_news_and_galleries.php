<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Origin URL/ID of content pushed via the API: a repeated POST with the same source updates instead of duplicating.
    public function up(): void
    {
        foreach (['news', 'galleries'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('source_url', 500)->nullable()->unique();
            });
        }
    }

    public function down(): void
    {
        foreach (['news', 'galleries'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropUnique($table . '_source_url_unique');
                $t->dropColumn('source_url');
            });
        }
    }
};
