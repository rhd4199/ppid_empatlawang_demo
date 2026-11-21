<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Controller;
use App\Models\WaConversation;
use App\Models\WaMessage;
use App\Support\WhatsAppGateway;
use Illuminate\Http\Request;

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
        $messages = $conversation->messages()->orderBy('sent_at')->orderBy('id')->get();

        // Draft once per unanswered question (cleared when a new message arrives), so reloading does not call the AI again.
        if ($conversation->reply_draft === null && $messages->last()?->direction === 'in') {
            $conversation->update(['reply_draft' => $this->draft($messages)]);
        }

        return view('admin.whatsapp.inbox', [
            'conversations' => $this->conversations(),
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function redraft(WaConversation $conversation)
    {
        $messages = $conversation->messages()->orderBy('sent_at')->orderBy('id')->get();
        $conversation->update(['reply_draft' => $messages->isEmpty() ? null : $this->draft($messages)]);

        return redirect()->route('admin.wa-inbox.show', $conversation)->with('success', 'Draf balasan dibuat ulang.');
    }

    public function send(Request $request, WaConversation $conversation)
    {
        $text = trim($request->validate(['body' => 'required|string|max:4000'])['body']);

        $result = WhatsAppGateway::sendLogged($conversation, $text);

        if (! $result['ok']) {
            // Keep the officer's edited text so it can be retried.
            $conversation->update(['reply_draft' => $text]);

            return redirect()->route('admin.wa-inbox.show', $conversation)->with('error', 'Balasan gagal dikirim: ' . $result['error']);
        }
        $conversation->update(['status' => 'done', 'reply_draft' => null, 'last_message_at' => now()]);

        return redirect()->route('admin.wa-inbox.show', $conversation)->with('success', 'Balasan terkirim ke +' . $conversation->phone . '.');
    }

    /** Sapa Madani's answer to the citizen's unanswered messages, earlier turns as history. */
    private function draft($messages): string
    {
        $lastOut = $messages->where('direction', 'out')->where('status', '!=', 'failed')->keys()->last();
        $pending = $lastOut === null ? $messages : $messages->slice($lastOut + 1);
        $question = $pending->where('direction', 'in')->pluck('body')->implode("\n");
        if ($question === '') {
            return '';
        }
        $history = $messages->take($lastOut === null ? 0 : $lastOut + 1)
            ->where(fn ($m) => $m->direction === 'in' || $m->status !== 'failed')
            ->take(-10)
            ->map(fn ($m) => ['role' => $m->direction === 'in' ? 'user' : 'assistant', 'content' => mb_substr($m->body, 0, 1000)])
            ->values()->all();

        return app(AssistantController::class)->answer(mb_substr($question, 0, 1000), $history);
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
