<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use App\Support\AssistantFallback;
use App\Support\DocumentFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    private const FAILED = 'Maaf, Sapa Madani sedang tidak dapat menjawab. Silakan coba lagi nanti.';

    // Default persona; admins may override it in Admin > Pengaturan Kontak > Sapa Madani (PpidSetting assistant_prompt).
    public const DEFAULT_PROMPT = <<<'TXT'
Anda adalah Sapa Madani, petugas layanan informasi virtual PPID (Pejabat Pengelola Informasi dan Dokumentasi) Kabupaten Empat Lawang.

Gaya: seperti petugas layanan yang hangat dan empatik, bukan robot atau template. Sapa pengguna dengan "Bapak/Ibu". Bahasa Indonesia yang sopan, sederhana, dan luwes (tidak kaku). Jawab inti pertanyaan lebih dulu, lalu beri penjelasan seperlunya; ringkas (paling banyak sekitar 150 kata). Pakai daftar bernomor hanya untuk langkah-langkah. Balas salam dan ucapan terima kasih dengan wajar. Bila maksud pertanyaan belum jelas, tanyakan balik dengan sopan (mis. "Boleh diceritakan informasi apa yang Bapak/Ibu cari?") daripada langsung memberi daftar menu. Hindari kalimat pembuka yang sama berulang-ulang.

Fokus Anda hanya keterbukaan informasi publik:
- Dasar hukum: UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik (KIP) dan aturan turunannya.
- Cara mengajukan permohonan informasi: lewat menu Permohonan Informasi di situs ini (atau datang langsung ke meja layanan PPID), isi identitas, rincian informasi yang diminta, tujuan penggunaan, dan cara memperoleh salinan. Status permohonan dicek di menu Cek Status Permohonan.
- Jangka waktu: PPID wajib memberi jawaban tertulis paling lambat 10 hari kerja sejak permohonan diterima, dan dapat diperpanjang paling lama 7 hari kerja dengan pemberitahuan tertulis.
- Keberatan: bila permohonan ditolak, tidak ditanggapi, atau jawabannya tidak sesuai, pemohon dapat mengajukan keberatan kepada Atasan PPID paling lambat 30 hari kerja lewat menu Pengajuan Keberatan; Atasan PPID menanggapi paling lambat 30 hari kerja. Bila tetap tidak puas, dapat mengajukan sengketa ke Komisi Informasi.
- Jenis informasi: informasi yang wajib disediakan dan diumumkan secara berkala, informasi yang wajib diumumkan serta-merta, informasi yang wajib tersedia setiap saat, dan informasi yang dikecualikan (Pasal 17 UU KIP). Daftarnya ada di menu Daftar Informasi Publik.
- Maklumat pelayanan, profil PPID, dan kontak resmi: arahkan ke menu Profil dan halaman Kontak.

Aturan:
- Tolak dengan sopan pertanyaan di luar layanan PPID dan informasi publik, lalu tawarkan bantuan seputar informasi publik.
- Jangan mengarang data, angka, nomor telepon, alamat, tautan, nama atau jabatan pejabat, maupun tanggal. Pakai hanya data kontak resmi dan tautan yang diberikan di bawah; bila tidak ada atau Anda tidak yakin, katakan tidak tahu dan arahkan ke halaman Kontak atau ajukan permohonan informasi.
- Jangan meminta data pribadi sensitif (NIK, nomor KK, rekening, kata sandi, alamat rumah). Data identitas cukup diisi di formulir resmi.
- Jangan memberi nasihat hukum yang mengikat; sampaikan sebagai informasi umum.
TXT;

    // Order: OPENAI_API_KEY set → OpenAI Chat Completions; else assistant_api_url (forwarded as {"message"}, must answer
    // {"reply"}); else a fixed reply. Only OpenAI gets the history (the fixed reply only reads the last bot message). Cost bounded by max_tokens, history size + route throttle.
    public function chat(Request $request)
    {
        // history = the last turns kept by the chat panel (browser only, never stored server-side) so follow-ups make sense.
        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:10',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:1000',
        ]);
        $settings = assistant_settings();

        if (! $settings['enabled']) {
            abort(404);
        }

        if (config('services.openai.key')) {
            return $this->reply(fn () => Http::withToken(config('services.openai.key'))->timeout(30)->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model'),
                    'max_tokens' => (int) config('services.openai.max_tokens'),
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt($settings['prompt'], $data['message'])],
                        ...array_map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']], $data['history'] ?? []),
                        ['role' => 'user', 'content' => $data['message']],
                    ],
                ])->throw()->json('choices.0.message.content'), 'OpenAI');
        }

        if ($settings['api_url'] !== '') {
            return $this->reply(fn () => Http::timeout(20)->acceptJson()
                ->post($settings['api_url'], ['message' => $data['message']])->throw()->json('reply'), 'API');
        }

        $lastReply = collect($data['history'] ?? [])->where('role', 'assistant')->last()['content'] ?? null;

        return response()->json(['reply' => AssistantFallback::reply($data['message'], $lastReply)]);
    }

    /** Persona (admin override or default) plus official contact data, site links and matching documents, so the model never has to guess them. */
    private function systemPrompt(string $persona, string $message): string
    {
        $contact = ContactSetting::first();
        $facts = array_filter([
            'Alamat' => $contact?->address,
            'Telepon' => implode(', ', (array) $contact?->phones),
            'Email' => implode(', ', (array) $contact?->emails),
            'Jam layanan' => implode('; ', (array) $contact?->working_hours),
        ]);
        $links = [
            'Permohonan Informasi' => route('request.create'),
            'Cek Status Permohonan' => route('request.status'),
            'Pengajuan Keberatan' => route('complaint.create'),
            'Daftar Informasi Publik' => route('informasi-publik.index'),
            'Kontak' => route('contact.index'),
        ];

        $lines = [trim($persona !== '' ? $persona : self::DEFAULT_PROMPT), '', 'Kontak resmi PPID:'];
        foreach ($facts ?: ['Kontak' => 'belum tersedia, arahkan ke halaman Kontak'] as $label => $value) {
            $lines[] = "- {$label}: {$value}";
        }
        $lines[] = '';
        $lines[] = 'Tautan menu situs:';
        foreach ($links as $label => $url) {
            $lines[] = "- {$label}: {$url}";
        }

        $text = mb_strtolower($message);
        if (DocumentFinder::wants($text) && $keywords = DocumentFinder::keywords($text)) {
            $docs = DocumentFinder::search($keywords);
            $lines[] = '';
            $lines[] = 'Hasil pencarian Daftar Informasi Publik untuk "' . implode(' ', $keywords) . '" (sebut hanya dokumen dan tautan dari daftar ini, jangan mengarang dokumen lain):';
            foreach ($docs as $doc) {
                $lines[] = "- {$doc['title']}: {$doc['url']}";
            }
            if (! $docs) {
                $lines[] = '- Tidak ada dokumen yang cocok. Sampaikan bahwa dokumen itu belum tersedia di daftar dan sarankan mengajukan permohonan informasi.';
            }
        }

        return implode("\n", $lines);
    }

    private function reply(callable $call, string $source)
    {
        try {
            $reply = $call();
        } catch (\Throwable $e) {
            // Exception messages hold the URL and response body only, never the Authorization header.
            Log::warning("Sapa Madani {$source} error: " . class_basename($e) . ' ' . $e->getCode() . ' ' . mb_substr($e->getMessage(), 0, 300));
            $reply = null;
        }

        return response()->json(['reply' => is_string($reply) && trim($reply) !== '' ? trim($reply) : self::FAILED]);
    }
}
