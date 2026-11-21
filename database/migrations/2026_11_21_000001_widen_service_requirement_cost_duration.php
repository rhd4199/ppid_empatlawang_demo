<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Cost/duration texts include tariff formulas and legal references; 255 chars is too short.
    public function up(): void
    {
        Schema::table('service_requirements', function (Blueprint $table) {
            $table->text('cost')->nullable()->change();
            $table->text('duration')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_requirements', function (Blueprint $table) {
            $table->string('cost')->nullable()->change();
            $table->string('duration')->nullable()->change();
        });
    }
};
