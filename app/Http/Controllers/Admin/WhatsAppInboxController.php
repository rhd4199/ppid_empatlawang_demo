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

    /** Poll: messages after ?after=<id> plus bubbles changed since ?since (status ticks), as rendered HTML. */
    public function messages(Request $request, WaConversation $conversation)
    {
        $data = $request->validate(['after' => 'required|integer|min:0', 'since' => 'nullable|date']);
        // Officer has the chat open: new citizen messages are read, like show().
        if ($conversation->status === 'new') {
            $conversation->update(['status' => 'read']);
        }
        $now = now();
        $render = fn ($m) => ['id' => $m->id, 'day' => WaMessage::dayLabel($m->sent_at), 'html' => view('admin.whatsapp._message', ['m' => $m])->render()];
        $messages = $conversation->messages()->where('id', '>', $data['after'])->orderBy('sent_at')->orderBy('id')->get();
        $changed = empty($data['since']) ? collect() : $conversation->messages()
            ->where('id', '<=', $data['after'])->where('direction', 'out')
            ->where('updated_at', '>=', $data['since'])->get();

        return response()->json([
            'messages' => $messages->map($render)->values(),
            'changed' => $changed->map($render)->values(),
            'since' => $now->toDateTimeString(),
        ]);
    }

    /** Poll: contact list HTML (same page/filters as the page) and the sidebar unread count. */
    public function summary(Request $request)
    {
        return response()->json([
            'list' => view('admin.whatsapp._list', ['conversations' => $this->conversations(), 'currentId' => $request->integer('current') ?: null])->render(),
            'unread' => WaConversation::where('status', 'new')->count(),
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
            // Citizen messages since the last delivered reply (list badge, like WhatsApp's unread count).
            ->addSelect(['unread_count' => WaMessage::selectRaw('count(*)')
                ->whereColumn('wa_conversation_id', 'wa_conversations.id')
                ->where('direction', 'in')
                ->whereRaw("sent_at > coalesce((select max(o.sent_at) from wa_messages o where o.wa_conversation_id = wa_messages.wa_conversation_id and o.direction = 'out' and o.status <> 'failed'), ?)", ['1970-01-01 00:00:00'])])
            ->when(self::filters()['status'] ?? null, fn ($q, $status) => match ($status) {
                'new', 'done' => $q->where('status', $status),
                'handoff' => $q->whereNotNull('handoff_reason')->where('status', '!=', 'done'),
                default => $q,
            })
            ->when(self::filters()['q'] ?? null, function ($q, $term) {
                // Name, number (0812… = 62812…) or any message text, case-insensitive (pg LIKE is not).
                // ponytail: % and _ typed by the officer act as wildcards; harmless for a search box.
                $like = '%' . mb_strtolower($term) . '%';
                $digits = preg_replace('/\D/', '', $term);
                $q->where(fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->when(strlen($digits) >= 3, fn ($q) => $q->orWhere('phone', 'like', '%' . preg_replace('/^0/', '62', $digits) . '%'))
                    ->orWhereHas('messages', fn ($m) => $m->whereRaw('LOWER(body) LIKE ?', [$like])));
            })
            ->orderByDesc('last_message_at')
            ->paginate(30)
            ->withQueryString();
    }

    /** List filters from the query string (?q=&status=), kept in list links, pagination and the poll. */
    public static function filters(): array
    {
        [$q, $status] = [request()->query('q'), request()->query('status')];

        return array_filter([
            'q' => is_string($q) ? trim(mb_substr($q, 0, 100)) : null,
            'status' => in_array($status, ['new', 'handoff', 'done'], true) ? $status : null,
        ]);
    }
}
