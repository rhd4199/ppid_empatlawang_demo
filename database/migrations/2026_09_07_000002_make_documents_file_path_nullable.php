<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * file_path was NOT NULL, but StandardServiceController/ProcurementController
     * already treat the upload as optional (validation: nullable|file) — this
     * closes that mismatch and allows draft documents awaiting a file upload.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
