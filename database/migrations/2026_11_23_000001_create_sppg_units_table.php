<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SPPG = kitchens of the national free-meal programme (MBG, Badan Gizi Nasional) in the regency.
        Schema::create('sppg_units', function (Blueprint $table) {
            $table->id();
            $table->string('bgn_id', 20)->nullable()->unique(); // SPPG id in BGN Radar MBG
            $table->string('code', 30)->nullable();             // BGN code: <Kemendagri village code>.<seq>
            $table->string('name');
            $table->string('kecamatan', 100);
            $table->string('village', 100)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('operator')->nullable();              // mitra / yayasan
            $table->string('status', 20)->default('operasional');
            $table->unsignedInteger('beneficiaries')->nullable(); // penerima manfaat, when known
            $table->unsignedSmallInteger('institutions_count')->default(0);
            $table->text('institutions')->nullable();           // JSON list of served schools etc.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->date('checked_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'kecamatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sppg_units');
    }
};
