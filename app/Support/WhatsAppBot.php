<?php

namespace App\Support;

use App\Http\Controllers\AssistantController;
use App\Models\WaConversation;

/**
 * WhatsApp auto-reply: Sapa Madani answers general questions itself, everything else waits in the officer inbox.
 * ponytail: rule-based routing on the citizen's text (same finders as Sapa Madani); the AI only writes the answer,
 * it never decides to skip an officer.
 */
class WhatsAppBot
{
    /** Numbered menu sent to citizens (the AI answers the topic; 0 = officer follow-up). */
    public const MENU = [
        '1' => 'jam layanan',
        '2' => 'cara mengajukan permohonan informasi',
        '3' => 'daftar informasi publik',
        '4' => 'cek status permohonan',
        '5' => 'cara mengajukan keberatan',
        '0' => 'saya ingin bicara dengan petugas PPID',
    ];

    /** Why an officer should follow up after the AI reply, or null when the AI reply is enough. */
    public static function handoffReason(string $message): ?string
    {
        $text = mb_strtolower(trim($message));
        $choice = self::menuChoice($text);

        return match (true) {
            $choice === '0' => 'Warga minta bicara dengan petugas',
            $choice !== null => null,
            AssistantComplaint::reply($text) !== null || (bool) preg_match('/\b(lapor|melapor|laporkan|aduan|adukan|mengadu|pengaduan|keluhan|komplain)\b/u', $text) => 'Aduan / laporan warga',
            (bool) preg_match('/\b(keberatan|sengketa|banding)\b/u', $text) => 'Keberatan / sengketa informasi',
            // "ada biaya?" also matches wants(), but leaves no subject keyword
            DocumentFinder::wants($text) && DocumentFinder::keywords($text) => 'Permintaan dokumen / permohonan informasi',
            AssistantInfo::reply($text) !== null, RequirementFinder::find($text)->isNotEmpty(), self::smallTalk($text) => null,
            (bool) preg_match('/\b(permohonan|mengajukan|ajukan|salinan|data|dokumen)\b/u', $text) => 'Permintaan dokumen / permohonan informasi',
            default => 'Bot tidak yakin dengan maksud pertanyaan',
        };
    }

    /** Called after a citizen message is stored; replies or marks the conversation for an officer. */
    public static function handle(WaConversation $conversation): void
    {
        $conversation->refresh(); // a stale model (status "done" in memory) would skip saving "done" again
        $messages = $conversation->messages()->orderBy('sent_at')->orderBy('id')->get();
        $last = $messages->last();
        if (! $last || $last->direction !== 'in') {
            return;
        }
        $wa = WhatsAppGateway::settings();
        if ($wa['enabled'] && $wa['auto_reply'] && ! AssistantController::aiAvailable() && ! config('services.assistant.keyword_fallback')) {
            $conversation->update(['handoff_reason' => 'AI belum aktif (OPENAI_API_KEY kosong), dibalas petugas']);

            return; // no template answers: stays "new" for an officer
        }
        if (! $wa['enabled'] || ! $wa['auto_reply']) {
            $conversation->update(['handoff_reason' => null]);

            return; // stays "new" → badge Baru in the inbox
        }
        // Owner's choice: the AI answers every message. Topics an officer must still follow up (aduan, keberatan,
        // dokumen, unclear) keep their reason and stay "new" after the AI reply.
        // Our last message asked something ("maksudnya layanan X?", "mau tahu langkahnya?"): "ya" / a number answers it.
        $lastOut = (string) $messages->where('direction', 'out')->last()?->body;
        $confirming = str_contains($lastOut, RequirementFinder::CONFIRM) || str_contains($lastOut, RequirementFinder::STEPS_ASK);
        $reason = $confirming ? null : self::handoffReason($last->body);

        $history = $messages->slice(0, -1)->take(-20)
            ->filter(fn ($m) => $m->direction === 'in' || $m->status !== 'failed')
            ->map(fn ($m) => ['role' => $m->direction === 'in' ? 'user' : 'assistant', 'content' => mb_substr($m->body, 0, 1000)])
            ->values()->all();
        // Menu number ("2"): the AI gets the menu topic as the question.
        // Answering our "maksudnya layanan X?" (ya / a number) is not a menu choice.
        $choice = $confirming ? null : self::menuChoice(mb_strtolower(trim($last->body)));
        $question = $choice !== null ? self::MENU[$choice] : mb_substr($last->body, 0, 1000);
        $started = now()->subSecond();
        LarikaPresence::read($conversation, typing: true); // blue ticks + "sedang mengetik…" while the AI works
        $reply = app(AssistantController::class)->answer($question, $history, 'wa', $conversation);
        $result = WhatsAppGateway::sendLogged($conversation, $reply, 'bot');
        // The AI recorded/updated a report in this turn (catat_laporan): it waits for an officer.
        if ($report = \App\Models\CitizenReport::where('wa_conversation_id', $conversation->id)->where('updated_at', '>=', $started)->latest('id')->first()) {
            $reason = "Laporan warga {$report->code}: " . \App\Models\CitizenReport::CATEGORIES[$report->category];
        }
        // A failed send stays new so an officer sees it.
        $conversation->update(match (true) {
            ! $result['ok'] => ['handoff_reason' => 'Balasan otomatis gagal terkirim'],
            $reason !== null => ['handoff_reason' => $reason, 'reply_draft' => null, 'last_message_at' => now()],
            default => ['status' => 'done', 'handoff_reason' => null, 'reply_draft' => null, 'last_message_at' => now()],
        });
    }

    /** "2", "2.", " 2 " → '2'; '0'..'5' only. */
    private static function menuChoice(string $text): ?string
    {
        return preg_match('/^\W*([0-5])\W*$/u', $text, $m) ? $m[1] : null;
    }

    /** Only a greeting / thanks / ok / test, nothing to look up. */
    private static function smallTalk(string $text): bool
    {
        $words = '(assalamu.?alaikum( wr\.? ?wb\.?)?|halo+|hallo+|hai+|hi+|hey+|selamat (pagi|siang|sore|malam)|pagi|siang|sore|malam|permisi|'
            . 'terima ?kasih( banyak)?|makasih|trims|thanks?|ok(e|ey|ay)?|tes+|test(ing)?|coba|baik|siap|sip|min|kak|pak|bu|bapak|ibu|admin|ppid)';

        return (bool) preg_match("/^(\W*{$words}\W*)+$/u", $text);
    }
}
