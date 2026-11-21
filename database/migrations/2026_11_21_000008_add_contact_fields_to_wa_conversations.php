<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // WhatsApp inbox contact card (CRM): name set by an officer (WhatsApp profile name stays in `name`), label, notes.
    public function up(): void
    {
        Schema::table('wa_conversations', function (Blueprint $table) {
            $table->string('contact_name', 100)->nullable();
            $table->string('label', 20)->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wa_conversations', fn (Blueprint $table) => $table->dropColumn(['contact_name', 'label', 'notes']));
    }
};
