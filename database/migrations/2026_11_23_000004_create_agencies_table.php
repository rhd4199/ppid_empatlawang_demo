<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Perangkat daerah (OPD) of the regency: profile + contact, shown on /instansi and used by Sapa Madani.
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name', 50)->nullable();
            $table->string('slug', 120)->unique();
            $table->string('category', 20);                 // Agency::CATEGORIES
            $table->string('keywords', 500)->nullable();    // extra words Sapa Madani matches, e.g. "ktp, akta"
            $table->text('description')->nullable();
            $table->string('address', 500)->nullable();
            $table->boolean('address_verified')->default(false); // false = 1 source only, hidden from the public
            $table->text('phones')->nullable();             // JSON [{number, label, verified}]
            $table->text('emails')->nullable();             // JSON [{address, verified}]
            $table->string('website', 255)->nullable();
            $table->text('social')->nullable();             // JSON {facebook, instagram, youtube, tiktok, x}
            $table->string('service_hours', 255)->nullable();
            $table->string('head_name')->nullable();
            $table->foreignId('official_id')->nullable()->constrained('officials')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('sources')->nullable();            // JSON [{url, title, accessed, used_for}]
            $table->string('confidence', 10)->nullable();   // high | medium | low
            $table->date('checked_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'category', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
