<?php

namespace App\Support;

use App\Models\ServiceRequirement;

/**
 * Finds published service requirements (ServiceRequirement, /persyaratan) for a chat message such as
 * "syarat ganti alamat KTP" or "cara bikin PIRT", so Sapa Madani answers from the researched data, never from memory.
 * ponytail: scores every published row in PHP by whole-word matches on title + keywords (the table holds tens of rows);
 * move to SQL/full-text search when it grows to thousands.
 */
class RequirementFinder
{
    /** Opening of the "did you mean" question; reply() reads the answer to it from the previous bot message. */
    public const CONFIRM = 'Apakah yang Bapak/Ibu maksud persyaratan';

    /** Follow-up question after a requirement answer; "ya" to it gets the steps (reply()). */
    public const STEPS_ASK = 'Apakah Bapak/Ibu ingin tahu langkah-langkah pengurusan';

    private const STOPWORDS = ['syarat', 'syaratnya', 'persyaratan', 'persyaratannya', 'cara', 'caranya', 'bagaimana', 'gimana', 'gmn',
        'bikin', 'buat', 'membuat', 'pembuatan', 'urus', 'mengurus', 'ngurus', 'pengurusan', 'proses', 'prosedur', 'mengajukan', 'ajukan',
        'apa', 'apa2', 'saja', 'aja', 'ya', 'yg', 'yang', 'untuk', 'utk', 'buat', 'di', 'ke', 'dari', 'dan', 'atau', 'dengan', 'mau',
        'ingin', 'pengen', 'pingin', 'saya', 'aku', 'kami', 'tolong', 'mohon', 'min', 'kak', 'pak', 'bu', 'bapak', 'ibu', 'tanya', 'nanya',
        'berapa', 'biaya', 'biayanya', 'lama', 'lamanya', 'butuh', 'perlu', 'diperlukan', 'dibutuhkan', 'dokumen', 'berkas', 'apakah',
        'kalau', 'kalo', 'klo', 'nih', 'dong', 'sih', 'kah', 'harus', 'bawa', 'dibawa', 'langkah', 'alur', 'info', 'informasi', 'tentang',
        'mengenai', 'soal', 'halo', 'hai', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'terima', 'kasih', 'ini', 'itu', 'kantor', 'el',
        'empat', 'lawang', 'kabupaten', 'kab', 'gratis', 'bayar', 'baru',
        // Palembang/Besemah chat: "cak mano" = bagaimana
        'caro', 'cak', 'mano', 'camano', 'cakmano', 'gek', 'nian', 'kito', 'aku', 'nak', 'dimano', 'apo'];

    /** Words people type vs. words in titles/keywords. */
    private const SYNONYMS = ['ektp' => 'ktp', 'akte' => 'akta', 'sertipikat' => 'sertifikat', 'serifikat' => 'sertifikat',
        'sertifikatnya' => 'sertifikat', 'spp' => 'pirt', 'irt' => 'pirt', 'imb' => 'pbg', 'p3k' => 'pppk', 'ijin' => 'izin',
        'domisili' => 'alamat', 'mobil' => 'kendaraan', 'balnam' => 'bbn', 'skpwni' => 'skpwni', 'kk' => 'keluarga'];

    /** Explicit "how do I get service X" intent; used to answer "not in our list" instead of a PPID topic. */
    public static function wants(string $text): bool
    {
        return (bool) preg_match('/\b(syarat|persyaratan|prosedur|(cara|caro) (bikin|buat|membuat|urus|mengurus|ngurus|perpanjang|ganti|balik)|bikin|urus|mengurus|ngurus|perpanjang|balik nama|mutasi)(nya)?\b/u', $text)
            && ! preg_match('/\b(permohonan informasi|informasi publik|keberatan|sengketa|ppid)\b/u', $text);
    }

    /** @return string[] lowercased keywords, at most 5 */
    public static function keywords(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($text), $m);
        // Chat suffixes: "ktpku" = ktp, "mengurusnya" = mengurus. ponytail: may cut a rare word ending in -ku/-mu/-nya.
        $stems = array_map(fn ($w) => preg_match('/^(\p{L}{3,}?)(ku|mu|nya)$/u', $w, $s) ? $s[1] : $w, $m[0]);
        $words = array_filter($stems, fn ($w) => mb_strlen($w) >= 2 && ! in_array($w, self::STOPWORDS, true));

        return array_slice(array_values(array_unique(array_map(fn ($w) => self::SYNONYMS[$w] ?? $w, $words))), 0, 5);
    }

    /**
     * Best matches (max 3): rows matching the most keywords, at least half of them; title hits break ties.
     *
     * @return \Illuminate\Support\Collection<int, ServiceRequirement>
     */
    public static function find(string $text)
    {
        $keywords = self::keywords($text);
        if (! $keywords) {
            return collect();
        }
        $has = fn (string $haystack, string $word) => (bool) preg_match('/\b' . preg_quote($word, '/') . '\b/u', $haystack);

        $rows = ServiceRequirement::where('is_published', true)->get()->map(function ($row) use ($keywords, $has) {
            $title = mb_strtolower($row->title);
            $all = $title . ' ' . mb_strtolower((string) $row->keywords);
            $row->score = count(array_filter($keywords, fn ($w) => $has($all, $w)));
            $row->titleScore = count(array_filter($keywords, fn ($w) => $has($title, $w)));

            return $row;
        });
        // Without "syarat/cara urus" the service itself must be named: "tes" alone is not "SIM Baru" (keywords: tes psikologi).
        if (! self::wants($text)) {
            $rows = $rows->where('titleScore', '>', 0);
        }
        $best = $rows->max('score');
        if (! $best || $best < count($keywords) / 2) {
            return collect();
        }

        // "bikin/buat KTP" = the new one first ("KTP-el Baru", not "Ganti Alamat KTP-el").
        $create = (bool) preg_match('/\b(bikin|buat|membuat|pembuatan|baru)\b/u', mb_strtolower($text));
        $rows->each(fn ($row) => $row->isNew = $create && str_contains(mb_strtolower($row->title), 'baru') ? 1 : 0);

        return $rows->where('score', $best)->sortBy([['titleScore', 'desc'], ['isNew', 'desc']])->take(3)->values();
    }

    /** Requirement list items as plain text (the column holds an <ol>/<ul> from the seeder or the admin editor). */
    public static function items(?string $html): array
    {
        preg_match_all('/<li[^>]*>(.*?)<\/li>/su', (string) $html, $m);
        $items = array_values(array_filter(array_map('plain_text', $m[1])));

        return $items ?: array_filter([plain_text($html)]);
    }

    /** Fallback-mode answer: main requirements of the best match + link, other matches as links. */
    public static function reply(string $text, ?string $lastReply = null): ?string
    {
        $yes = (bool) preg_match('/^\W*(ya+|iya+|y|yes|betul|benar|bener|boleh|mau|oke?|ok|iyo|nah)\b/u', $text);
        // "ya" to "… langkah-langkah pengurusan <judul>?": the steps of that service.
        if ($yes && $lastReply && preg_match('/' . preg_quote(self::STEPS_ASK, '/') . ' (.+)\?/u', $lastReply, $s)
            && ($item = ServiceRequirement::where('is_published', true)->where('title', $s[1])->first())) {
            return self::steps($item);
        }
        // Answer to our "Apakah yang Bapak/Ibu maksud persyaratan …?": "ya" = first option, a number = that option.
        if ($lastReply && str_contains($lastReply, self::CONFIRM) && preg_match_all('/^(\d)\. (.+)$/mu', $lastReply, $m)) {
            $pick = $yes ? 0
                : (preg_match('/^\W*(\d)\W*$/u', $text, $n) ? (int) $n[1] - 1 : null);
            $item = $pick !== null && isset($m[2][$pick])
                ? ServiceRequirement::where('is_published', true)->where('title', trim($m[2][$pick]))->first() : null;
            if ($item) {
                return self::describe($item, collect());
            }
        }
        $found = self::find($text);
        // Without an explicit "syarat/cara urus" every keyword must match, so "alamat kantor ppid" is not "Ganti Alamat KTP".
        if ($found->isEmpty() || (! self::wants($text) && $found->first()->score < count(self::keywords($text)))) {
            return null;
        }
        // Matched only through keywords, not the service name ("bikin kue" → PIRT): confirm before answering.
        // Or several services fit equally ("syarat ktp": baru / hilang / ganti alamat): let the citizen choose.
        [$a, $b] = [$found->get(0), $found->get(1)];
        $tie = $b && [$a->score, $a->titleScore, $a->isNew] === [$b->score, $b->titleScore, $b->isNew];
        if ($a->titleScore < $a->score || $tie) {
            $options = $found->values()->map(fn ($r, $i) => ($i + 1) . ". {$r->title}")->implode("\n");

            return self::CONFIRM . ($found->count() > 1 ? " salah satu layanan berikut?\n{$options}\nBalas angkanya untuk melihat persyaratannya."
                : " berikut?\n{$options}\nBalas \"ya\" untuk melihat persyaratannya, atau ceritakan lebih rinci yang Bapak/Ibu butuhkan.");
        }

        return self::describe($found->first(), $found->slice(1));
    }

    /** Fallback answer for one service, other matches as links. */
    private static function describe(ServiceRequirement $item, $others): string
    {
        $items = self::items($item->requirements);
        $lines = ["Persyaratan {$item->title} (dilayani {$item->agency}):"];
        foreach (array_slice($items, 0, 5) as $i => $line) {
            $lines[] = ($i + 1) . '. ' . $line;
        }
        if (count($items) > 5) {
            $lines[] = '…dan ' . (count($items) - 5) . ' syarat lainnya.';
        }
        if ($item->cost) {
            $lines[] = 'Biaya: ' . $item->cost;
        }
        $lines[] = 'Rincian lengkap (langkah, lama proses, dasar hukum): ' . route('service-requirements.show', $item->slug);
        foreach ($others as $other) {
            $lines[] = "Mungkin juga yang dimaksud: {$other->title} — " . route('service-requirements.show', $other->slug);
        }
        $lines[] = "Ini informasi umum; persyaratan bisa berubah, jadi mohon pastikan kembali ke {$item->agency} sebelum datang.";
        // Always end with a follow-up question (owner's wish).
        $lines[] = $item->steps ? self::STEPS_ASK . " {$item->title}?" : 'Apakah Bapak/Ibu juga perlu persyaratan layanan lain?';

        return implode("\n", $lines);
    }

    /** Steps of one service (answer to the STEPS_ASK follow-up). */
    private static function steps(ServiceRequirement $item): string
    {
        $lines = ["Langkah pengurusan {$item->title}:"];
        foreach (array_slice(self::items($item->steps), 0, 8) as $i => $line) {
            $lines[] = ($i + 1) . '. ' . $line;
        }
        if ($item->duration) {
            $lines[] = 'Lama proses: ' . $item->duration;
        }
        $lines[] = 'Rincian lengkap (langkah, lama proses, dasar hukum): ' . route('service-requirements.show', $item->slug);
        $lines[] = 'Apakah ada bagian yang masih kurang jelas, atau Bapak/Ibu perlu persyaratan layanan lain?';

        return implode("
", $lines);
    }

    /** No match for an explicit requirement question: point to the list and the serving agency. */
    public static function notFound(): string
    {
        return 'Mohon maaf, persyaratan untuk layanan itu belum ada di daftar kami. Bapak/Ibu bisa melihat daftar lengkapnya di halaman Persyaratan Layanan ('
            . route('service-requirements.index') . '), atau menanyakannya langsung ke instansi yang melayani. Bila perlu bantuan menghubungi instansi, silakan lihat halaman Kontak PPID ('
            . route('contact.index') . ').';
    }
}
