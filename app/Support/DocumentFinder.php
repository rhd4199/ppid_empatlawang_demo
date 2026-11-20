<?php

namespace App\Support;

use App\Models\Document;

/**
 * Finds published documents for a chat message ("minta data APBD 2025") so Sapa Madani links real files only.
 * ponytail: plain keyword AND over title/description (+ synonyms, leading "ppid" dropped); swap for full-text search if the document list grows large.
 */
class DocumentFinder
{
    private const STOPWORDS = ['minta', 'meminta', 'mohon', 'tolong', 'data', 'dokumen', 'file', 'berkas', 'salinan', 'ada', 'adakah',
        'apakah', 'apa', 'saya', 'aku', 'kami', 'kita', 'mau', 'ingin', 'butuh', 'perlu', 'cari', 'carikan', 'mencari', 'lihat', 'melihat',
        'unduh', 'download', 'dapat', 'bisa', 'boleh', 'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'tentang', 'soal', 'mengenai',
        'tahun', 'thn', 'tersedia', 'punya', 'dong', 'ya', 'nya', 'pak', 'bu', 'bapak', 'ibu', 'admin', 'kak', 'min', 'cara', 'bagaimana',
        'gimana', 'mengajukan', 'ajukan', 'permohonan', 'informasi', 'publik', 'halo', 'hai',
        'assalamualaikum', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'terima', 'kasih', 'ini', 'itu', 'sudah', 'belum', 'dengan', 'pada',
        'kirim', 'kirimkan', 'berikan', 'kasih', 'link', 'tautan', 'terbaru', 'lengkap'];

    /** Request intent: the message asks for a document/data, not e.g. how to file a request. */
    public static function wants(string $text): bool
    {
        return (bool) preg_match('/\b(minta|meminta|mohon|data|dokumen|file|berkas|salinan|laporan|unduh|download|carikan|cari|butuh|perlu|ada|adakah|sk|perbup|perda|apbd|lkpj|lppd|renstra|rkpd|dpa)\b/u', $text);
    }

    /** @return string[] lowercased keywords, at most 4 */
    public static function keywords(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($text), $m);
        $words = array_filter($m[0], fn ($w) => mb_strlen($w) >= 2 && ! in_array($w, self::STOPWORDS, true));
        $words = array_values(array_map(fn ($w) => self::SYNONYMS[$w] ?? $w, $words));
        while ($words && in_array($words[0], self::ADDRESS, true)) {
            array_shift($words); // "min ppid, ada data apbd?" addresses PPID; "laporan ppid" / "SK PPID" keep it
        }

        return array_slice(array_values(array_unique($words)), 0, 4);
    }

    /** Abbreviations people type vs. the words used in document titles. */
    private const SYNONYMS = ['sk' => 'keputusan', 'perbup' => 'peraturan bupati', 'perda' => 'peraturan daerah', 'perpres' => 'peraturan presiden'];

    /** Words that only address the office when they lead the message. */
    private const ADDRESS = ['ppid', 'kabupaten', 'kab', 'empat', 'lawang', 'pemda', 'pemkab'];

    /** @return array<int, array{title: string, url: string}> published, non-excluded documents matching every keyword (max 5) */
    public static function search(array $keywords): array
    {
        if (! $keywords) {
            return [];
        }
        $query = Document::where('is_published', true)->where('category', '!=', 'informasi-publik-dikecualikan');
        foreach ($keywords as $word) {
            // LOWER() LIKE: case-insensitive on Postgres, MySQL and SQLite alike (same as InfoPublicController).
            $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';
            $query->where(fn ($q) => $q->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$term])
                ->orWhereRaw("LOWER(description) LIKE ? ESCAPE '!'", [$term]));
        }

        return $query->latest()->limit(5)->get(['id', 'title'])
            ->map(fn ($d) => ['title' => $d->title, 'url' => route('informasi-publik.show', $d->id)])->all();
    }
}
