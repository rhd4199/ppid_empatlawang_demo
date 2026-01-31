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
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->text('address')->nullable();
            $table->json('phones')->nullable(); // Array of phone numbers
            $table->json('emails')->nullable(); // Array of emails
            $table->json('working_hours')->nullable(); // Object or array
            $table->text('maps_embed')->nullable();
            $table->json('social_media')->nullable(); // Array of social media objects
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};
