<?php

namespace App\Support;

use App\Models\ContactSetting;
use Illuminate\Support\Arr;

/**
 * Sapa Madani replies while no AI is connected: greetings, thanks and common PPID questions by keyword,
 * phrased like a front-desk officer (random openers/closers so it does not read as a template).
 * ponytail: keyword matching only; first matching topic wins (keberatan before permohonan). Real conversation needs OpenAI.
 */
class AssistantFallback
{
    public static function reply(string $message): string
    {
        $text = mb_strtolower(trim($message));
        $topic = self::topic($text);
        $greeting = self::greeting($text);

        if ($topic) {
            $opener = $greeting ?? Arr::random(['Baik, Bapak/Ibu.', 'Tentu, Bapak/Ibu.', 'Bisa, Bapak/Ibu.', 'Siap, Bapak/Ibu.']);

            return "{$opener} {$topic} " . Arr::random([
                'Ada lagi yang bisa saya bantu?',
                'Kalau masih ada yang kurang jelas, silakan tanyakan saja.',
                'Semoga membantu. Ada hal lain yang ingin ditanyakan?',
            ]);
        }

        // "saya mau tanya" without a topic yet: invite the question (checked after topics so "mau tanya cara keberatan" still answers)
        if (preg_match('/\b(mau|mo|boleh|numpang|ingin|pengen|pingin|izin|ijin)\s+(tanya|nanya|bertanya)\b|\bada (pertanyaan|yang (mau|ingin) (saya )?(tanya|ditanyakan))\b/u', $text)) {
            return ($greeting ? "{$greeting} " : '') . Arr::random([
                'Silakan, Bapak/Ibu. Apa yang ingin ditanyakan?',
                'Tentu boleh, Bapak/Ibu. Silakan, apa yang ingin ditanyakan?',
            ]) . ' Misalnya cara mengajukan permohonan informasi, mengecek status permohonan, mengajukan keberatan, atau mencari dokumen publik.';
        }

        if ($greeting) {
            return "{$greeting} " . Arr::random([
                'Saya Sapa Madani dari PPID Kabupaten Empat Lawang. Ada yang bisa saya bantu hari ini?',
                'Saya Sapa Madani, siap membantu Bapak/Ibu seputar layanan informasi publik. Ada yang ingin ditanyakan?',
                'Dengan Sapa Madani di sini. Informasi apa yang sedang Bapak/Ibu cari?',
            ]);
        }

        if (preg_match('/\b(terima ?kasih|makasih|trims|thanks?|thank you|tq|oke|ok|okay|baik|siap|sip)\b/u', $text)) {
            return Arr::random([
                'Sama-sama, Bapak/Ibu. Senang bisa membantu. Kalau nanti ada pertanyaan lain, silakan sapa saya lagi.',
                'Terima kasih kembali, Bapak/Ibu. Semoga urusannya lancar ya.',
                'Sama-sama. Jangan ragu kembali bertanya kapan saja, Bapak/Ibu.',
            ]);
        }

        return Arr::random([
            'Mohon maaf, saya belum menangkap maksudnya. Boleh diceritakan informasi apa yang Bapak/Ibu cari?',
            'Maaf, Bapak/Ibu, saya kurang paham. Bisa dijelaskan sedikit lebih rinci? Misalnya cara mengajukan permohonan informasi, mengecek status, atau mengajukan keberatan.',
            'Boleh diceritakan dulu kebutuhan Bapak/Ibu? Saya bantu arahkan ke layanan yang tepat.',
        ]);
    }

    /** Salam sesuai waktu WIB; null when the message does not open with a greeting. */
    private static function greeting(string $text): ?string
    {
        if (preg_match('/^(assalamu.?alaikum|asw|ass?alam)/u', $text)) {
            return "Wa'alaikumsalam warahmatullahi wabarakatuh, Bapak/Ibu.";
        }
        if (! preg_match('/^(halo+|hallo+|hai+|hi+|hei+|helo+|selamat (pagi|siang|sore|malam)|permisi|punten)\b/u', $text)) {
            return null;
        }
        $hour = now('Asia/Jakarta')->hour;
        $time = $hour < 11 ? 'pagi' : ($hour < 15 ? 'siang' : ($hour < 18 ? 'sore' : 'malam'));

        return "Selamat {$time}, Bapak/Ibu.";
    }

    private static function topic(string $text): ?string
    {
        $topics = [
            '\b(keberatan|sengketa|banding)\b' => 'Bila permohonan informasi ditolak, tidak ditanggapi, atau jawabannya belum sesuai, Bapak/Ibu dapat mengajukan keberatan lewat menu Pengajuan Keberatan (' . route('complaint.create') . '). Cukup isi identitas, nomor registrasi permohonan, dan alasannya, paling lambat 30 hari kerja. Atasan PPID akan menanggapi paling lambat 30 hari kerja.',
            '\b(lapor|melapor|mengadu|pengaduan|aduan|adukan)\b' => 'PPID melayani permintaan informasi publik. Bila Bapak/Ibu ingin meminta data atau dokumen, silakan lewat menu Permohonan Informasi (' . route('request.create') . '). Untuk pengaduan pelayanan publik, Bapak/Ibu bisa memakai SP4N-LAPOR! di lapor.go.id atau menghubungi kami lewat halaman Kontak (' . route('contact.index') . ').',
            '\b(status|cek|lacak|nomor registrasi)\b' => 'Status permohonan bisa dicek sendiri di menu Cek Status Permohonan (' . route('request.status') . ') dengan nomor registrasi yang Bapak/Ibu terima saat mengajukan.',
            '\b(kontak|telepon|telpon|alamat|email|e-mail|surel|jam|buka|tutup|kantor|lokasi|whatsapp|wa|hubungi)\b' => fn () => self::contact($text),
            // pattern unused here: documents() checks request intent and keywords itself and returns null to fall through
            '' => fn () => self::documents($text),
            '\b(daftar informasi|dip|berkala|serta.?merta|setiap saat|dikecualikan|dokumen)\b' => 'Daftar informasi publik, baik yang berkala, serta-merta, maupun setiap saat, bisa dilihat dan diunduh di menu Daftar Informasi Publik (' . route('informasi-publik.index') . ').',
            '\b(permohonan|mengajukan|ajukan|minta|meminta|memperoleh|informasi)\b' => 'Caranya mudah: buka menu Permohonan Informasi (' . route('request.create') . '), isi identitas, informasi yang dibutuhkan beserta tujuannya, lalu pilih cara menerima salinannya. Simpan nomor registrasinya untuk mengecek status. PPID akan menjawab paling lambat 10 hari kerja, dan bisa diperpanjang 7 hari kerja bila perlu.',
        ];
        foreach ($topics as $pattern => $reply) {
            if (preg_match("/{$pattern}/u", $text) && ($answer = $reply instanceof \Closure ? $reply() : $reply) !== null) {
                return $answer;
            }
        }

        return null;
    }

    /** Matching documents as a numbered list, or "not listed yet" + permohonan link; null when the message is not a document request. */
    private static function documents(string $text): ?string
    {
        $keywords = DocumentFinder::wants($text) ? DocumentFinder::keywords($text) : [];
        if (! $keywords) {
            return null;
        }
        $docs = DocumentFinder::search($keywords);
        if (! $docs) {
            return 'Dokumen tentang "' . implode(' ', $keywords) . '" belum tersedia di Daftar Informasi Publik. Bapak/Ibu dapat memintanya lewat menu Permohonan Informasi (' . route('request.create') . '); PPID akan menjawab paling lambat 10 hari kerja.';
        }

        $lines = array_map(fn ($d, $i) => ($i + 1) . ". {$d['title']}\n   {$d['url']}", $docs, array_keys($docs));

        return "Berikut dokumen yang cocok di Daftar Informasi Publik:\n" . implode("\n", $lines) . "\nBila yang dicari belum ada, Bapak/Ibu dapat mengajukan permohonan informasi (" . route('request.create') . ').';
    }

    /** Answers with the ContactSetting data that was asked for (all of it for a plain "kontak"); links to /kontak only when it is missing. */
    private static function contact(string $text): string
    {
        $c = ContactSetting::first();
        $fields = [
            'hours' => ['/\b(jam|buka|tutup|hari)\b/u', implode('; ', (array) $c?->working_hours), 'Jam layanan PPID: %s.'],
            'address' => ['/\b(alamat|lokasi|kantor|datang|dimana|di mana)\b/u', trim((string) $c?->address), 'Kantor PPID beralamat di %s.'],
            'phones' => ['/\b(telepon|telpon|nomor|hp|whatsapp|wa|hubungi)\b/u', implode(' atau ', (array) $c?->phones), 'PPID dapat dihubungi di nomor %s.'],
            'emails' => ['/\b(email|e-mail|surel)\b/u', implode(' atau ', (array) $c?->emails), 'Email PPID: %s.'],
        ];
        $asked = array_filter($fields, fn ($f) => preg_match($f[0], $text)) ?: $fields;

        $sentences = [];
        foreach ($asked as [, $value, $format]) {
            if ($value !== '') {
                $sentences[] = sprintf($format, $value);
            }
        }
        if (! $sentences) {
            return 'Data kontak lengkap (alamat, telepon, email, dan jam layanan) ada di halaman Kontak (' . route('contact.index') . ').';
        }
        if (array_keys($asked) === ['hours'] && $fields['address'][1] !== '') {
            $sentences[] = 'Kalau ingin datang langsung, alamatnya di ' . $fields['address'][1] . '.';
        }
        if (count($sentences) < count($asked)) {
            $sentences[] = 'Info kontak lainnya ada di halaman Kontak (' . route('contact.index') . ').';
        }

        return implode(' ', $sentences);
    }
}
