<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaConversation;
use App\Models\WaMessage;

class WhatsAppInboxController extends Controller
{
    public function index()
    {
        return view('admin.whatsapp.inbox', ['conversations' => $this->conversations(), 'conversation' => null]);
    }

    public function show(WaConversation $conversation)
    {
        if ($conversation->status === 'new') {
            $conversation->update(['status' => 'read']);
        }

        return view('admin.whatsapp.inbox', [
            'conversations' => $this->conversations(),
            'conversation' => $conversation,
            'messages' => $conversation->messages()->orderBy('sent_at')->orderBy('id')->get(),
        ]);
    }

    private function conversations()
    {
        // Last message text as a subquery, so the list needs no query per row.
        return WaConversation::query()
            ->addSelect(['last_body' => WaMessage::select('body')
                ->whereColumn('wa_conversation_id', 'wa_conversations.id')
                ->orderByDesc('sent_at')->orderByDesc('id')->limit(1)])
            ->orderByDesc('last_message_at')
            ->paginate(30);
    }
}
