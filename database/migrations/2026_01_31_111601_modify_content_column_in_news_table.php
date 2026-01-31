<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change content column to LONGTEXT to support larger content size
        // Using raw SQL to avoid doctrine/dbal dependency requirement
        DB::statement('ALTER TABLE news MODIFY content LONGTEXT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to TEXT
        DB::statement('ALTER TABLE news MODIFY content TEXT');
    }
};
