<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Larika delivers events at least once (poll + webhook): handled event ids, so each is processed once.
    public function up(): void
    {
        Schema::create('larika_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 191)->unique();
            $table->string('event', 64);
            $table->timestamp('handled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('larika_events');
    }
};
