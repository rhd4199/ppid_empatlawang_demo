<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a Document entry point at an existing page/section on the site
     * (e.g. "Alamat Kantor" -> /kontak) instead of requiring a re-uploaded
     * file when the content already lives elsewhere.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('external_url')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('external_url');
        });
    }
};
