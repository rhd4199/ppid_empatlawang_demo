<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // AI-drafted reply the officer edits/approves; provider error of a failed outgoing message.
    public function up(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->text('reply_draft')->nullable();
        });
        Schema::table('wa_messages', function (Blueprint $table) {
            $table->string('error', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversations', fn (Blueprint $table) => $table->dropColumn('reply_draft'));
        Schema::table('wa_messages', fn (Blueprint $table) => $table->dropColumn('error'));
    }
};
