<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            // What Sapa Madani remembers about this citizen across chats (CitizenMemory); officers can edit it.
            $table->text('ai_memory')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversations', fn (Blueprint $table) => $table->dropColumn('ai_memory'));
    }
};
