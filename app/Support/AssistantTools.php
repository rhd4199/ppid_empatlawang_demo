<?php

namespace App\Support;

use App\Models\ContactSetting;
use App\Models\Event;
use App\Models\News;
use App\Models\Official;
use App\Models\Procurement;
use App\Models\ServiceRequirement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI tools for Sapa Madani: the model looks up public site data itself (requirements, documents, officials,
 * contact, news, agenda, procurement)
 * instead of relying only on what is pre-matched into the system prompt.
 * Read-only, published rows only.
 */
class AssistantTools
{
    /** Tool rounds per answer (each round = one more OpenAI call). */
    public const MAX_ROUNDS = 4;

    /** OpenAI "tools" parameter. */
    public static function definitions(): array
    {
        $web = (string) config('services.openai.search_model') !== '';
        $fn = fn (string $name, string $description, array $properties = [], array $required = []) => ['type' => 'function', 'function' => [
            'name' => $name,
            'description' => $description,
            'parameters' => ['type' => 'object', 'properties' => (object) $properties, 'required' => $required],
        ]];

        return [
            $fn('daftar_persyaratan', 'Daftar semua layanan yang persyaratannya tersedia di halaman Persyaratan Layanan (KTP-el, KK, akta, SIM, STNK, tanah, izin usaha, dll.), per kategori. Pakai bila pengguna bertanya layanan apa saja yang ada, atau untuk mencari slug yang tepat.',
                ['kategori' => ['type' => 'string', 'enum' => array_keys(ServiceRequirement::CATEGORIES), 'description' => 'Opsional: hanya satu kategori.']]),
            $fn('cari_persyaratan', 'Cari layanan di data Persyaratan Layanan dengan kata kunci (mis. "ktp hilang", "balik nama motor", "pirt"). Mengembalikan judul, instansi, slug dan tautan.',
                ['kata_kunci' => ['type' => 'string']], ['kata_kunci']),
            $fn('detail_persyaratan', 'Rincian satu layanan: persyaratan, langkah, biaya, lama proses, dasar hukum, instansi, tautan. Pakai sebelum menjawab persyaratan sebuah layanan.',
                ['slug' => ['type' => 'string', 'description' => 'slug dari daftar_persyaratan atau cari_persyaratan']], ['slug']),
            $fn('cari_dokumen', 'Cari dokumen di Daftar Informasi Publik PPID (APBD, LKjIP, Renstra, peraturan, laporan, SK, dll.). Mengembalikan judul dan tautan unduh.',
                ['kata_kunci' => ['type' => 'string', 'description' => 'mis. "apbd 2025", "lkjip dinas kesehatan"']], ['kata_kunci']),
            $fn('daftar_pejabat', 'Nama pejabat publik Kabupaten Empat Lawang yang ada di data PPID (bupati, wakil, sekda, kepala dinas, Forkopimda, DPRD, camat, dll.). Satu-satunya sumber nama pejabat; jangan menebak nama di luar hasil ini.',
                ['jabatan' => ['type' => 'string', 'description' => 'Opsional: saring kata pada jabatan, mis. "kapolres", "dinas kesehatan", "camat"']]),
            $fn('kontak_ppid', 'Alamat, telepon, email, jam layanan dan media sosial resmi PPID Kabupaten Empat Lawang.'),
            $fn('cari_berita', 'Berita terbaru di situs PPID/Pemkab (judul, tanggal, ringkasan, tautan). Pakai untuk pertanyaan kejadian/kegiatan terbaru.',
                ['kata_kunci' => ['type' => 'string', 'description' => 'Opsional; kosong = 5 berita terbaru']]),
            $fn('agenda_kegiatan', 'Agenda/kegiatan Pemkab dan PPID yang akan datang (atau di sekitar tanggal tertentu).',
                ['dari_tanggal' => ['type' => 'string', 'description' => 'Opsional, YYYY-MM-DD; bawaan hari ini']]),
            $fn('cari_pengadaan', 'Informasi pengadaan barang/jasa yang diumumkan di situs PPID (judul, kategori, status).',
                ['kata_kunci' => ['type' => 'string', 'description' => 'Opsional']]),
            ...($web ? [$fn('cari_web', 'Cari di web publik (situs resmi pemerintah, media berita) bila data PPID di tool lain tidak punya jawabannya, mis. pejabat Forkopimda, kegiatan/berita di luar situs, regulasi umum. Hasilnya bukan data resmi PPID: sebut sumbernya dan bahwa informasi bisa belum terbaru.',
                ['pertanyaan' => ['type' => 'string', 'description' => 'Pertanyaan/kata kunci pencarian, singkat dan spesifik']], ['pertanyaan'])] : []),
        ];
    }

    /** Runs one tool call; the result goes back to the model as JSON. */
    public static function run(string $name, array $args): array
    {
        $published = fn () => ServiceRequirement::where('is_published', true);
        $brief = fn ($r) => ['judul' => $r->title, 'slug' => $r->slug, 'instansi' => $r->agency, 'tautan' => route('service-requirements.show', $r->slug)];

        return match ($name) {
            'daftar_persyaratan' => $published()
                ->when(isset(ServiceRequirement::CATEGORIES[$args['kategori'] ?? '']), fn ($q) => $q->where('category', $args['kategori']))
                ->orderBy('category')->orderBy('title')->get()
                ->groupBy(fn ($r) => ServiceRequirement::CATEGORIES[$r->category] ?? $r->category)
                ->map(fn ($rows) => $rows->map(fn ($r) => ['judul' => $r->title, 'slug' => $r->slug])->values())
                ->all() + ['halaman' => route('service-requirements.index')],
            // "syarat " = explicit intent, so find() does not require the service name in the title
            'cari_persyaratan' => ['hasil' => RequirementFinder::find('syarat ' . mb_substr((string) ($args['kata_kunci'] ?? ''), 0, 200))
                ->map($brief)->values()->all() ?: 'Tidak ada yang cocok. Coba kata lain atau daftar_persyaratan.'],
            'detail_persyaratan' => ($r = $published()->where('slug', (string) ($args['slug'] ?? ''))->first())
                ? $brief($r) + array_filter([
                    'persyaratan' => RequirementFinder::items($r->requirements),
                    'langkah' => $r->steps ? RequirementFinder::items($r->steps) : null,
                    'biaya' => $r->cost,
                    'lama_proses' => $r->duration,
                    'dasar_hukum' => $r->legal_basis ? plain_text($r->legal_basis) : null,
                    'dicek_pada' => $r->checked_at?->format('d-m-Y'),
                ])
                : ['error' => 'Slug tidak ditemukan. Pakai cari_persyaratan atau daftar_persyaratan.'],
            'cari_dokumen' => ['hasil' => DocumentFinder::search(DocumentFinder::keywords('minta ' . mb_substr((string) ($args['kata_kunci'] ?? ''), 0, 200)))
                ?: 'Tidak ada dokumen yang cocok; sarankan Permohonan Informasi ' . route('request.create'), 'daftar' => route('informasi-publik.index')],
            'daftar_pejabat' => ['pejabat' => Official::where('is_published', true)
                ->when(trim((string) ($args['jabatan'] ?? '')), fn ($q, $term) => $q->whereRaw('LOWER(position) LIKE ?', ['%' . mb_strtolower(mb_substr($term, 0, 100)) . '%']))
                ->orderBy('order')->get(['name', 'position'])->map(fn ($o) => ['jabatan' => $o->position, 'nama' => $o->name])->all()
                ?: 'Tidak ada di data; katakan belum tersedia, jangan menebak.', 'halaman' => route('officials.index')],
            'kontak_ppid' => ($c = ContactSetting::first()) ? array_filter([
                'alamat' => $c->address, 'telepon' => $c->phones, 'email' => $c->emails, 'jam_layanan' => $c->working_hours,
                'media_sosial' => $c->social_media, 'halaman_kontak' => route('contact.index'),
            ]) : ['halaman_kontak' => route('contact.index')],
            'cari_berita' => ['berita' => News::where('is_published', true)->where('published_at', '<=', now())
                ->when(trim((string) ($args['kata_kunci'] ?? '')), fn ($q, $term) => $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower(mb_substr($term, 0, 100)) . '%']))
                ->latest('published_at')->limit(5)->get()
                ->map(fn ($n) => ['judul' => $n->title, 'tanggal' => Carbon::parse($n->published_at)->format('d-m-Y'),
                    'ringkasan' => mb_strimwidth(plain_text($n->content), 0, 300, '…'), 'tautan' => route('news.show', $n->slug)])->all()
                ?: 'Tidak ada berita yang cocok.', 'semua_berita' => route('news.index')],
            'agenda_kegiatan' => ['agenda' => Event::where('start_date', '>=', self::date($args['dari_tanggal'] ?? null))
                ->orderBy('start_date')->limit(8)->get()
                ->map(fn ($e) => ['kegiatan' => $e->title, 'mulai' => Carbon::parse($e->start_date)->format('d-m-Y H:i') . ' WIB', 'lokasi' => $e->location])->all()
                ?: 'Belum ada agenda.', 'halaman' => route('events.index')],
            'cari_pengadaan' => ['pengadaan' => Procurement::query()
                ->when(trim((string) ($args['kata_kunci'] ?? '')), fn ($q, $term) => $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower(mb_substr($term, 0, 100)) . '%']))
                ->latest()->limit(8)->get(['title', 'category', 'status'])->map(fn ($p) => ['judul' => $p->title, 'kategori' => $p->category, 'status' => $p->status])->all()
                ?: 'Tidak ada yang cocok.', 'halaman' => route('procurements.index')],
            'cari_web' => self::web(mb_substr(trim((string) ($args['pertanyaan'] ?? '')), 0, 300)),
            default => ['error' => "Tool {$name} tidak ada."],
        };
    }

    /**
     * Public-web lookup by an OpenAI search model (answer + cited URLs). Never throws: a failure becomes an error the
     * model must report honestly. Costs one more OpenAI call, bounded by MAX_ROUNDS.
     */
    private static function web(string $query): array
    {
        if ($query === '' || (string) config('services.openai.search_model') === '') {
            return ['error' => 'Pencarian web tidak tersedia.'];
        }
        try {
            $message = Http::withToken(config('services.openai.key'))->timeout(45)->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.search_model'),
                    'web_search_options' => ['search_context_size' => 'low',
                        'user_location' => ['type' => 'approximate', 'approximate' => ['country' => 'ID', 'region' => 'Sumatera Selatan', 'city' => 'Empat Lawang']]],
                    'messages' => [
                        ['role' => 'system', 'content' => 'Cari di web publik (utamakan situs resmi pemerintah dan media berita tepercaya) dan jawab ringkas dalam bahasa Indonesia, maksimal 120 kata. Sebut tanggal/tahun informasi. Bila tidak menemukan sumber yang jelas, katakan tidak ditemukan; jangan menebak.'],
                        ['role' => 'user', 'content' => $query . ' (Kabupaten Empat Lawang, Sumatera Selatan)'],
                    ],
                ])->throw()->json('choices.0.message');
        } catch (\Throwable $e) {
            Log::warning('Sapa Madani web search error: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 200));

            return ['error' => 'Pencarian web gagal. Katakan jawabannya belum bisa dipastikan dan arahkan ke halaman Kontak atau Permohonan Informasi.'];
        }
        $sources = collect($message['annotations'] ?? [])->pluck('url_citation')->filter()
            ->map(fn ($c) => ['judul' => $c['title'] ?? '', 'url' => $c['url'] ?? ''])->unique('url')->take(5)->values()->all();

        return ['jawaban' => trim((string) ($message['content'] ?? '')), 'sumber' => $sources,
            'catatan' => 'Dari web publik, bukan data resmi PPID. Sebut sumbernya (nama situs/tautan) dan bahwa informasi bisa belum terbaru.'];
    }

    /** Agenda start: a valid YYYY-MM-DD from the model, else today. */
    private static function date(?string $value): Carbon
    {
        return $value && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? Carbon::parse($value)->startOfDay() : now()->startOfDay();
    }
}
