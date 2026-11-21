<?php

namespace App\Support;

use App\Models\Document;

/**
 * Finds published documents for a chat message ("minta data APBD 2025") so Sapa Madani links real files only.
 * ponytail: plain keyword AND over title/description (+ synonyms, leading "ppid" dropped), then a looser best-match retry;
 * swap for full-text search if the document list grows large.
 */
class DocumentFinder
{
    private const STOPWORDS = ['minta', 'meminta', 'mohon', 'tolong', 'data', 'dokumen', 'file', 'berkas', 'salinan', 'ada', 'adakah',
        'apakah', 'apa', 'saya', 'aku', 'kami', 'kita', 'mau', 'ingin', 'butuh', 'perlu', 'cari', 'carikan', 'mencari', 'lihat', 'melihat',
        'unduh', 'download', 'dapat', 'bisa', 'boleh', 'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'tentang', 'soal', 'mengenai',
        'tahun', 'thn', 'tersedia', 'punya', 'dong', 'ya', 'nya', 'pak', 'bu', 'bapak', 'ibu', 'admin', 'kak', 'min', 'cara', 'bagaimana',
        'gimana', 'mengajukan', 'ajukan', 'permohonan', 'informasi', 'publik', 'halo', 'hai',
        'tanya', 'nanya', 'bertanya', 'pertanyaan', 'ditanyakan', 'numpang', 'nih', 'izin', 'ijin',
        'assalamualaikum', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'terima', 'kasih', 'ini', 'itu', 'sudah', 'belum', 'dengan', 'pada',
        'kirim', 'kirimkan', 'berikan', 'kasih', 'link', 'tautan', 'terbaru', 'lengkap',
        // chat slang and filler ("boleh mintak data apbd 2024 tidak")
        'mintak', 'minta2', 'mnta', 'tidak', 'tdk', 'gak', 'ga', 'nggak', 'ngga', 'enggak', 'kah', 'nih', 'deh', 'sih', 'kok', 'lah', 'tanya',
        'nanya', 'bertanya', 'gan', 'sis', 'bang', 'mas', 'mbak', 'tolongin', 'dapatkan', 'mendapatkan', 'memperoleh', 'kalau', 'kalo', 'klo', 'pengen',
        'pingin', 'pgn', 'mo', 'yg', 'dgn', 'utk', 'sy', 'aja', 'saja', 'juga', 'lagi', 'dulu', 'tsb', 'tersebut'];

    /**
     * Detailed / non-routine data that is rarely published as a document (contracts, recipient lists, village-level detail):
     * the answer explains the request procedure even when some documents match.
     * ponytail: keyword list; extend with the requests citizens actually make.
     */
    public static function isSpecific(string $text): bool
    {
        return (bool) preg_match('/\b(salinan|kontrak|spk|rab|rincian|detail|penerima|nama.nama|daftar nama|kuitansi|kwitansi|spj|nota|bukti (bayar|pembayaran|transfer)|penawaran|absensi|gaji|honor|desa|kelurahan|dusun|rt|rw)\b/u', $text);
    }

    /** How to request data that is not in the Daftar Informasi Publik; wording and deadline as on the Standar Layanan page. */
    public static function requestProcedure(): string
    {
        return 'Data yang belum tersedia atau bersifat khusus dapat dimohonkan ke PPID:'
            . "\n1. Isi formulir Permohonan Informasi (" . route('request.create') . ').'
            . "\n2. Siapkan identitas: nama lengkap, NIK/No. KTP, email, nomor telepon/HP, alamat, dan scan/foto KTP."
            . "\n3. Tulis rincian informasi yang dibutuhkan dan tujuan penggunaannya, lalu pilih cara mendapatkannya (email, pos, atau ambil langsung)."
            . "\nPermohonan diproses dan dijawab paling lambat 10 + 7 hari kerja (apabila informasi belum dikuasai/didokumentasikan)."
            . ' Prosedur lengkap ada di halaman Standar Layanan (' . route('standard-service.index') . '#permohonan).';
    }

    /** Request intent: the message asks for a document/data, not e.g. how to file a request. */
    public static function wants(string $text): bool
    {
        return (bool) preg_match('/\b(minta|mintak|minta2|meminta|mohon|data|dokumen|file|berkas|salinan|laporan|unduh|download|carikan|cari|butuh|perlu|ada|adakah|sk|perbup|perda|apbd|lkpj|lppd|renstra|rkpd|dpa)\b/u', $text);
    }

    /** "laporan kinerja 2025, ada biaya?" also asks whether the copy costs money ("data biaya perjalanan dinas" does not). */
    public static function asksFee(string $text): bool
    {
        return (bool) preg_match('/\b(ada|apakah|apa|berapa|kena|pakai|perlu|harus|dikenakan) (biaya|bayar|ongkos|tarif)\b|\b(biayanya|bayarnya|gratis|berbayar|dipungut)\b/u', $text);
    }

    private const FEE_WORDS = ['biaya', 'biayanya', 'bayar', 'bayarnya', 'gratis', 'berbayar', 'dipungut', 'ongkos', 'tarif', 'berapa', 'kena', 'dikenakan'];

    /** @return string[] lowercased keywords, at most 4 */
    public static function keywords(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($text), $m);
        $skip = self::asksFee(mb_strtolower($text)) ? [...self::STOPWORDS, ...self::FEE_WORDS] : self::STOPWORDS;
        $words = array_filter($m[0], fn ($w) => mb_strlen($w) >= 2 && ! in_array($w, $skip, true));
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

    /**
     * Published, non-excluded documents matching every keyword (max 5). When that finds nothing, one stray word must not
     * sink the search: numbers (years) stay required, the other words are ranked by how many match (best score wins).
     *
     * @return array<int, array{title: string, url: string}>
     */
    public static function search(array $keywords): array
    {
        if (! $keywords) {
            return [];
        }
        $docs = self::query($keywords, [])->latest()->limit(5)->get(['id', 'title']);

        $numbers = array_values(array_filter($keywords, 'ctype_digit'));
        $words = array_values(array_diff($keywords, $numbers));
        if ($docs->isEmpty() && count($words) > 1) {
            $docs = self::query($numbers, $words)->latest()->limit(100)->get(['id', 'title', 'description'])
                ->map(function ($d) use ($words) {
                    $haystack = mb_strtolower($d->title . ' ' . $d->description);
                    $d->score = count(array_filter($words, fn ($w) => str_contains($haystack, $w)));

                    return $d;
                });
            // score 0 = only the year matched ("Perpres 2018 … 2025" for "laporan kinerja 2025"): not a match
            $docs = $docs->where('score', '>', 0)->where('score', $docs->max('score'))->take(5);
        }

        return $docs->map(fn ($d) => ['title' => $d->title, 'url' => route('informasi-publik.show', $d->id)])->values()->all();
    }

    /** Every $all keyword must match, plus at least one of $any (title or description). */
    private static function query(array $all, array $any)
    {
        // LOWER() LIKE: case-insensitive on Postgres, MySQL and SQLite alike (same as InfoPublicController).
        $match = fn ($q, $word) => $q->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%'])
            ->orWhereRaw("LOWER(description) LIKE ? ESCAPE '!'", [$term]);

        $query = Document::where('is_published', true)->where('category', '!=', 'informasi-publik-dikecualikan');
        foreach ($all as $word) {
            $query->where(fn ($q) => $match($q, $word));
        }

        return $query->where(fn ($q) => array_map(fn ($word) => $q->orWhere(fn ($q2) => $match($q2, $word)), $any));
    }
}
