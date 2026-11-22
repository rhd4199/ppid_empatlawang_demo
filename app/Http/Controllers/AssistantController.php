<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use App\Support\AssistantComplaint;
use App\Support\AssistantFallback;
use App\Support\AssistantInfo;
use App\Support\DocumentFinder;
use App\Support\RequirementFinder;
use App\Support\AssistantTools;
use App\Support\WhatsAppFormat;
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
        if (! assistant_settings()['enabled']) {
            abort(404);
        }

        return response()->json(['reply' => $this->answer($data['message'], $data['history'] ?? [])]);
    }

    /**
     * Reply text for a citizen message; also drafts WhatsApp inbox replies (Admin\WhatsAppInboxController).
     *
     * @param  array<int, array{role: string, content: string}>  $history  earlier turns, oldest first
     * @param  string  $channel  'wa' = WhatsApp: the AI writes WhatsApp formatting and the reply is converted to it
     */
    public function answer(string $message, array $history = [], string $channel = 'web'): string
    {
        $wa = $channel === 'wa';

        return $wa ? WhatsAppFormat::text($this->rawAnswer($message, $history, $wa)) : $this->rawAnswer($message, $history, false);
    }

    private function rawAnswer(string $message, array $history, bool $wa): string
    {
        $settings = assistant_settings();

        if (config('services.openai.key')) {
            $messages = [
                ['role' => 'system', 'content' => $this->systemPrompt($settings['prompt'], $message) . ($wa ? "\n\n" . WhatsAppFormat::PROMPT : '')],
                ...array_map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']], $history),
                ['role' => 'user', 'content' => $message],
            ];

            return $this->reply(fn () => $this->openAi($messages), 'OpenAI');
        }

        if ($settings['api_url'] !== '') {
            return $this->reply(fn () => Http::timeout(20)->acceptJson()
                ->post($settings['api_url'], ['message' => $message])->throw()->json('reply'), 'API');
        }

        $lastReply = collect($history)->where('role', 'assistant')->last()['content'] ?? null;

        return AssistantFallback::reply($message, $lastReply);
    }

    /** Chat completion with the Persyaratan Layanan tools: run the model's tool calls, then ask again (max MAX_ROUNDS). */
    private function openAi(array $messages): ?string
    {
        for ($round = 0; ; $round++) {
            $tools = $round < AssistantTools::MAX_ROUNDS; // last round: no tools, so the model must answer
            $choice = Http::withToken(config('services.openai.key'))->timeout(30)->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', array_filter([
                    'model' => config('services.openai.model'),
                    'max_tokens' => (int) config('services.openai.max_tokens'),
                    'messages' => $messages,
                    'tools' => $tools ? AssistantTools::definitions() : null,
                ]))->throw()->json('choices.0.message');
            if (empty($choice['tool_calls']) || ! $tools) {
                return $choice['content'] ?? null;
            }
            $messages[] = $choice;
            foreach ($choice['tool_calls'] as $call) {
                $args = json_decode($call['function']['arguments'] ?? '{}', true);
                $messages[] = ['role' => 'tool', 'tool_call_id' => $call['id'],
                    'content' => json_encode(AssistantTools::run($call['function']['name'] ?? '', is_array($args) ? $args : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
            }
        }
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
            'Persyaratan Layanan (KTP, SIM, tanah, izin usaha, dll.)' => route('service-requirements.index'),
            'Standar Layanan – Permohonan' => route('standard-service.index') . '#permohonan',
            'Standar Layanan – Pengaduan' => route('standard-service.index') . '#pengaduan',
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
        // fixed block, also kept when an admin replaces the persona: it depends on the links and channels above
        $lines[] = '';
        // Owner's decision: every chat is from an Empat Lawang resident.
        $lines[] = 'Anggap setiap pengguna adalah warga Kabupaten Empat Lawang: jangan tanyakan asal daerah/domisilinya, dan arahkan ke kantor, instansi, dan layanan di Kabupaten Empat Lawang (Disdukcapil, Samsat, Satlantas Polres, Kantor Pertanahan, DPMPTSP, desa/kecamatan setempat, dll. di Empat Lawang).';
        $lines[] = '';
        $lines[] = 'Anda punya tool untuk data situs: persyaratan layanan, dokumen Daftar Informasi Publik (cari_dokumen), nama pejabat (daftar_pejabat), kontak PPID, berita terbaru, agenda kegiatan, dan pengadaan. Pakai tool bila data di atas belum cukup; nama pejabat hanya dari daftar_pejabat (bila tidak ada, katakan belum tersedia).';
        $lines[] = 'Akhiri setiap jawaban dengan satu pertanyaan lanjutan yang singkat dan relevan (mis. "Apakah Bapak/Ibu ingin tahu langkah-langkah pengurusannya?" atau "Apakah ada dokumen lain yang Bapak/Ibu cari?"), kecuali pengguna sedang berpamitan atau berterima kasih.';
        $lines[] = '';
        $lines[] = 'Kenali dulu niat pengguna, lalu jawab sesuai tujuannya:';
        $lines[] = '1. Tanya informasi umum (kantor, alamat/lokasi, jam layanan, telepon/email, biaya, lama proses): jawab dengan kontak resmi di atas. Biaya: ' . AssistantInfo::FEE . ' Lama proses: ' . AssistantInfo::DURATION;
        $lines[] = '2. Permohonan data publik yang umum (APBD, laporan, peraturan): sebut dokumen dari hasil pencarian Daftar Informasi Publik di bawah (bila ada) beserta tautannya.';
        $lines[] = '3. Permohonan data khusus/tidak umum atau yang tidak ada di hasil pencarian (kontrak, daftar penerima, rincian per desa): jelaskan singkat prosedur permohonan dan beri tautan "Standar Layanan – Permohonan" serta "Permohonan Informasi".';
        $lines[] = '4. Pengaduan/laporan warga: bila jenisnya belum jelas, tanyakan balik dulu (jalan/fasilitas umum rusak, pelayanan publik, penyalahgunaan wewenang/pungli, lainnya). Bila sudah jelas, sebut kanal sesuai jenis dan beri tautan "Standar Layanan – Pengaduan". '
            . 'Jalan/fasilitas rusak dan pelayanan publik: ' . AssistantComplaint::LAPOR . '. Penyalahgunaan wewenang, pungli, korupsi, pelanggaran ASN/pejabat: ' . AssistantComplaint::INSPEKTORAT . '; ' . AssistantComplaint::OMBUDSMAN . '; atau SP4N-LAPOR!. '
            . 'Jangan arahkan pengaduan ke Permohonan Informasi atau Pengajuan Keberatan (keberatan hanya untuk permohonan informasi yang ditolak/tidak ditanggapi).';
        $lines[] = '5. Tanya persyaratan/cara mengurus layanan instansi lain (KTP-el, KK, akta, SIM, STNK, balik nama, sertifikat tanah, izin usaha, mutasi ASN): ini boleh dijawab walau bukan layanan PPID, tetapi hanya dari data Persyaratan Layanan: blok "Data Persyaratan Layanan" di bawah (bila ada) atau tool cari_persyaratan / detail_persyaratan / daftar_persyaratan (pakai tool bila blok itu kosong atau kurang rinci). Bila layanan yang cocok hanya kebetulan dari kata kunci dan namanya tidak disebut pengguna (mis. "cara bikin kue" → izin PIRT), konfirmasi dulu: "Apakah yang Bapak/Ibu maksud persyaratan …?" sebelum memberi rinciannya; bila jelas (mis. "syarat KTP hilang"), langsung jawab. Sebut instansinya dan beri tautannya; bila tidak ditemukan, jangan mengarang: arahkan ke halaman "Persyaratan Layanan" dan ke instansi yang melayani.';

        $text = mb_strtolower($message);
        $lines = [...$lines, ...$this->requirementFacts($text)];
        if (DocumentFinder::wants($text) && $keywords = DocumentFinder::keywords($text)) {
            $docs = DocumentFinder::search($keywords);
            $lines[] = '';
            $lines[] = 'Hasil pencarian Daftar Informasi Publik untuk "' . implode(' ', $keywords) . '" (sebut hanya dokumen dan tautan dari daftar ini, jangan mengarang dokumen lain):';
            foreach ($docs as $doc) {
                $lines[] = "- {$doc['title']}: {$doc['url']}";
            }
            if (! $docs) {
                $lines[] = '- Tidak ada dokumen yang cocok. Sampaikan bahwa dokumen itu belum tersedia di daftar, lalu jelaskan prosedur permohonan di bawah.';
            }
            if (! $docs || DocumentFinder::isSpecific($text)) {
                $lines[] = '';
                $lines[] = 'Data ini rinci/khusus atau belum tersedia: jelaskan singkat prosedur permohonan berikut (persis, dengan tautannya):';
                $lines[] = DocumentFinder::requestProcedure();
            }
        }

        return implode("\n", $lines);
    }

    /** Only the matching rows of /persyaratan (never the whole table), or a "not listed" instruction for an explicit "syarat X". */
    private function requirementFacts(string $text): array
    {
        $found = RequirementFinder::find($text);
        if ($found->isEmpty()) {
            return RequirementFinder::wants($text)
                ? ['', 'Persyaratan layanan yang ditanyakan tidak ada di data Persyaratan Layanan. Jangan mengarang persyaratan; sampaikan belum tersedia, arahkan ke ' . route('service-requirements.index') . ' dan ke instansi yang melayani atau halaman Kontak.']
                : [];
        }
        $lines = ['', 'Data Persyaratan Layanan yang cocok (layanan instansi lain, bukan PPID). Bila pertanyaannya tentang layanan ini, jawab ringkas persyaratan utamanya dari data ini saja, sebut instansinya, beri tautan rinciannya, dan ingatkan bahwa ini informasi umum yang perlu dipastikan ke instansi:'];
        foreach ($found as $item) {
            $lines[] = "- {$item->title} | instansi: {$item->agency} | tautan: " . route('service-requirements.show', $item->slug);
            $lines[] = '  Persyaratan: ' . mb_strimwidth(implode('; ', RequirementFinder::items($item->requirements)), 0, 700, '…');
            foreach (['Biaya' => $item->cost, 'Lama proses' => $item->duration] as $label => $value) {
                if ($value) {
                    $lines[] = "  {$label}: " . mb_strimwidth($value, 0, 300, '…');
                }
            }
        }

        return $lines;
    }

    private function reply(callable $call, string $source): string
    {
        try {
            $reply = $call();
        } catch (\Throwable $e) {
            // Exception messages hold the URL and response body only, never the Authorization header.
            Log::warning("Sapa Madani {$source} error: " . class_basename($e) . ' ' . $e->getCode() . ' ' . mb_substr($e->getMessage(), 0, 300));
            $reply = null;
        }

        return is_string($reply) && trim($reply) !== '' ? trim($reply) : self::FAILED;
    }
}
