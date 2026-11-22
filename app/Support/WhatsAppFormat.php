<?php

namespace App\Support;

/**
 * Sapa Madani replies in WhatsApp style: *tebal*, _miring_, • daftar, plain URLs.
 * Converts Markdown from the AI and dresses up the fixed (fallback) requirement answer.
 * ponytail: line patterns match RequirementFinder::reply(); change both together (WhatsAppFormatTest).
 */
class WhatsAppFormat
{
    /** Added to the AI system prompt for the WhatsApp channel. */
    public const PROMPT = 'Balasan ini dikirim lewat WhatsApp. Pakai format WhatsApp, bukan Markdown: *teks tebal* (satu bintang) untuk judul/poin penting, _miring_ untuk catatan, daftar bernomor "1." untuk persyaratan/langkah dan "•" untuk poin lain, paragraf pendek dipisah baris kosong, tautan ditulis polos (tanpa [teks](url)), tanpa tabel dan tanpa judul #. Boleh 1–2 emoji yang relevan (mis. 📋 🔗 💰 🕗), jangan berlebihan.';

    /** Web chat panel shows plain text (URLs become links there): drop AI Markdown (**bold**, # headings, [text](url)). */
    public static function plain(string $reply): string
    {
        return preg_replace(['/\*\*(.+?)\*\*/u', '/__(.+?)__/u', '/^#{1,6}\s*/mu', '/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/u'], ['$1', '$1', '', '$1: $2'], $reply);
    }

    public static function text(string $reply): string
    {
        $rules = [
            // AI Markdown → WhatsApp
            '/\*\*(.+?)\*\*/u' => '*$1*',
            '/__(.+?)__/u' => '_$1_',
            '/^#{1,6}\s*(.+?)\s*#*$/mu' => '*$1*',
            '/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/u' => '$1: $2',
            '/^(\s*)[-*]\s+/mu' => '$1• ',
            // fixed requirement answer (RequirementFinder::reply)
            '/^(.*?)\s*Persyaratan (.+?) \(dilayani (.+?)\):$/mu' => "$1\n\n📋 *Persyaratan $2*\n_Dilayani $3_\n",
            '/^Biaya: /mu' => "\n💰 *Biaya:* ",
            '/^(.*?)\s*Langkah pengurusan (.+):$/mu' => "$1\n\n🧭 *Langkah pengurusan $2*\n",
            '/^Lama proses: /mu' => "\n⏱️ *Lama proses:* ",
            '/^(Apakah .+\?)$/mu' => "\n$1", // follow-up question as its own paragraph
            '/^Rincian lengkap \(langkah, lama proses, dasar hukum\): /mu' => "🔗 *Rincian lengkap:* ",
            '/^Mungkin juga yang dimaksud: (.+?) — (\S+)$/mu' => '• Mungkin juga: *$1*  $2',
            '/^(Ini informasi umum; .+?sebelum datang\.) */mu' => "\n_$1_\n\n",
        ];
        $text = preg_replace(array_keys($rules), array_values($rules), str_replace("\r\n", "\n", $reply));

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }
}
