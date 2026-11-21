<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Requirements of services run by other agencies (Disdukcapil, Samsat, BPN, ...), for citizens' reference.
        Schema::create('service_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 50);
            $table->string('agency');
            $table->text('requirements');
            $table->text('steps')->nullable();
            $table->string('cost')->nullable();
            $table->string('duration')->nullable();
            $table->text('legal_basis')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->date('checked_at')->nullable();
            $table->text('keywords')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requirements');
    }
};
