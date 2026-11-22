<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Citizen reports (aduan: jalan rusak, pungli, ...) recorded by Sapa Madani (tool catat_laporan) or officers.
        Schema::create('citizen_reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // LW-2026-0001
            $table->foreignId('wa_conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 20)->default('whatsapp'); // whatsapp | web | petugas
            $table->string('category', 40);
            $table->text('description');
            $table->string('location', 500);
            $table->string('kecamatan', 100)->nullable();
            $table->string('village', 100)->nullable();
            $table->string('occurred', 150)->nullable(); // "sejak 2 minggu lalu", as the citizen said it
            $table->string('reporter_name', 100)->nullable();
            $table->string('contact', 30)->nullable();
            $table->string('status', 20)->default('baru')->index();
            $table->text('officer_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizen_reports');
    }
};
