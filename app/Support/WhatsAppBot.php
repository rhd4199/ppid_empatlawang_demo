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
    /** Numbered menu sent to citizens (1–5 answered by the bot from fixed topics, 0 = officer). */
    public const MENU = [
        '1' => 'jam layanan',
        '2' => 'cara mengajukan permohonan informasi',
        '3' => 'daftar informasi publik',
        '4' => 'cek status permohonan',
        '5' => 'cara mengajukan keberatan',
    ];

    /** Why an officer must answer, or null when the bot may answer on its own. */
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
        $messages = $conversation->messages()->orderBy('sent_at')->orderBy('id')->get();
        $last = $messages->last();
        if (! $last || $last->direction !== 'in') {
            return;
        }
        $wa = WhatsAppGateway::settings();
        $reason = self::handoffReason($last->body);
        if (! $wa['enabled'] || ! $wa['auto_reply'] || $reason !== null) {
            $conversation->update(['handoff_reason' => $wa['enabled'] && $wa['auto_reply'] ? $reason : null]);

            return; // stays "new" → badge Baru in the inbox
        }

        $history = $messages->slice(0, -1)->take(-10)
            ->filter(fn ($m) => $m->direction === 'in' || $m->status !== 'failed')
            ->map(fn ($m) => ['role' => $m->direction === 'in' ? 'user' : 'assistant', 'content' => mb_substr($m->body, 0, 1000)])
            ->values()->all();
        // Menu number: fixed answer (never the AI), so "2" is not read as a question.
        $choice = self::menuChoice(mb_strtolower(trim($last->body)));
        $reply = $choice !== null
            ? AssistantFallback::reply(self::MENU[$choice], null) . "\n\nKetik angka lain untuk menu berikutnya, atau 0 untuk bicara dengan petugas."
            : app(AssistantController::class)->answer(mb_substr($last->body, 0, 1000), $history);
        $result = WhatsAppGateway::sendLogged($conversation, $reply, 'bot');
        // Answered by the bot → out of the queue; a failed send stays new so an officer sees it.
        $conversation->update($result['ok']
            ? ['status' => 'done', 'handoff_reason' => null, 'reply_draft' => null, 'last_message_at' => now()]
            : ['handoff_reason' => 'Balasan otomatis gagal terkirim']);
    }

    /** "2", "2.", " 2 " → '2'; '0'..'5' only. */
    private static function menuChoice(string $text): ?string
    {
        return preg_match('/^\W*([0-5])\W*$/u', $text, $m) && ($m[1] === '0' || isset(self::MENU[$m[1]])) ? $m[1] : null;
    }

    /** Only a greeting / thanks / ok / test, nothing to look up. */
    private static function smallTalk(string $text): bool
    {
        $words = '(assalamu.?alaikum( wr\.? ?wb\.?)?|halo+|hallo+|hai+|hi+|hey+|selamat (pagi|siang|sore|malam)|pagi|siang|sore|malam|permisi|'
            . 'terima ?kasih( banyak)?|makasih|trims|thanks?|ok(e|ey|ay)?|tes+|test(ing)?|coba|baik|siap|sip|min|kak|pak|bu|bapak|ibu|admin|ppid)';

        return (bool) preg_match("/^(\W*{$words}\W*)+$/u", $text);
    }
}
