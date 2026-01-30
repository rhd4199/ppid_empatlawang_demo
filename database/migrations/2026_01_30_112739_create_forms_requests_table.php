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
        Schema::create('forms_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('name');
            $table->string('nik')->nullable();
            $table->string('ktp_file')->nullable();
            $table->string('address');
            $table->string('email');
            $table->string('phone');
            $table->text('info_requested');
            $table->text('reason');
            $table->string('delivery_method'); // email, pos, ambil_langsung
            $table->enum('status', ['pending', 'processed', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms_requests');
    }
};
