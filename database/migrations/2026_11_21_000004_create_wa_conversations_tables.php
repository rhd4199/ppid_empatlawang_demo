<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // WhatsApp inbox: one conversation per citizen number, filled by the provider webhook.
    public function up(): void
    {
        Schema::create('wa_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->unique();
            $table->string('name')->nullable();
            $table->string('status', 20)->default('new'); // new | read | done
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('wa_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 3); // in | out
            $table->text('body');
            $table->string('status', 20); // received | sent | failed
            $table->string('provider_message_id', 191)->nullable()->unique();
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
        Schema::dropIfExists('wa_conversations');
    }
};
