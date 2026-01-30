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
        Schema::create('forms_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // new ticket for complaint
            $table->string('request_ticket_number')->nullable(); // reference to original request
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->text('reason_complaint');
            $table->enum('status', ['pending', 'processed', 'resolved', 'rejected'])->default('pending');
            $table->text('admin_reply')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms_complaints');
    }
};
