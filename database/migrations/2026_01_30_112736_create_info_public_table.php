<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('info_public', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->enum('category', ['berkala', 'serta_merta', 'setiap_saat', 'dikecualikan']);
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->date('published_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_public');
    }
};
