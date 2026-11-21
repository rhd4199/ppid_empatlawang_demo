<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // WhatsApp bot: who wrote an outgoing message (null = officer, 'bot') and why the bot handed a conversation to an officer.
    public function up(): void
    {
        Schema::table('wa_messages', function (Blueprint $table) {
            $table->string('sender', 20)->nullable();
        });
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->string('handoff_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wa_messages', fn (Blueprint $table) => $table->dropColumn('sender'));
        Schema::table('wa_conversations', fn (Blueprint $table) => $table->dropColumn('handoff_reason'));
    }
};
