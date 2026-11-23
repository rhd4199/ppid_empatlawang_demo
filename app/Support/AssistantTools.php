<?php

namespace App\Support;

use App\Models\Agency;
use App\Models\ContactSetting;
use App\Models\Event;
use App\Models\News;
use App\Models\Official;
use App\Models\Procurement;
use App\Models\ServiceRequirement;
use App\Models\SppgUnit;
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
            $fn('kontak_ppid', 'Alamat, telepon, email, jam layanan dan media sosial kantor PPID Kabupaten Empat Lawang SAJA. Bukan kontak instansi lain (Disdukcapil, Samsat, dinas, dll.): untuk itu pakai kontak_instansi.'),
            $fn('kontak_instansi', 'Profil, alamat, telepon, email, situs dan jam layanan instansi SELAIN PPID di Empat Lawang. Dicari dulu di Direktori Perangkat Daerah situs PPID (dinas, badan, sekretariat, inspektorat, Satpol PP, RSUD, kecamatan), bila tidak ada baru di web publik (Samsat, Polres, Satpas SIM, Kantor Pertanahan, puskesmas, dll.). Sebut sumbernya; bila tidak ditemukan, katakan jujur.',
                ['instansi' => ['type' => 'string', 'description' => 'mis. "Disdukcapil", "Dinas Kesehatan", "Kantor Camat Pendopo", "Samsat"']], ['instansi']),
            $fn('daftar_instansi', 'Daftar perangkat daerah (OPD) Pemkab Empat Lawang di Direktori Perangkat Daerah situs PPID: nama, singkatan, tautan profil, per kategori.',
                ['kategori' => ['type' => 'string', 'enum' => array_keys(Agency::CATEGORIES), 'description' => 'Opsional: hanya satu kategori']]),
            $fn('cari_berita', 'Berita terbaru di situs PPID/Pemkab (judul, tanggal, ringkasan, tautan). Pakai untuk pertanyaan kejadian/kegiatan terbaru.',
                ['kata_kunci' => ['type' => 'string', 'description' => 'Opsional; kosong = 5 berita terbaru']]),
            $fn('agenda_kegiatan', 'Agenda/kegiatan Pemkab dan PPID yang akan datang (atau di sekitar tanggal tertentu).',
                ['dari_tanggal' => ['type' => 'string', 'description' => 'Opsional, YYYY-MM-DD; bawaan hari ini']]),
            $fn('cari_pengadaan', 'Informasi pengadaan barang/jasa yang diumumkan di situs PPID (judul, kategori, status).',
                ['kata_kunci' => ['type' => 'string', 'description' => 'Opsional']]),
            $fn('harga_pangan', 'Harga eceran bahan pangan terbaru (beras, cabai, bawang, daging, telur, gula, minyak goreng, ikan, dll.) di Kabupaten Empat Lawang dari Panel Harga Badan Pangan Nasional.',
                ['komoditas' => ['type' => 'string', 'description' => 'Opsional, mis. "cabai", "beras"; kosong = semua']]),
            $fn('cuaca', 'Prakiraan cuaca BMKG per 3 jam untuk sebuah kecamatan di Empat Lawang, plus peringatan dini cuaca BMKG yang sedang berlaku.',
                ['kecamatan' => ['type' => 'string', 'enum' => array_column(BmkgWeather::DISTRICTS, 'name'), 'description' => 'Bawaan Tebing Tinggi']]),
            $fn('info_bencana', 'Kejadian bencana 30 hari terakhir di Empat Lawang dan kabupaten sekitarnya (data BNPB): jenis, tanggal, dampak, kondisi.'),
            $fn('data_sppg', 'SPPG (dapur program Makan Bergizi Gratis/MBG, Badan Gizi Nasional) di Empat Lawang: jumlah per status & kecamatan, nama, desa, alamat, jumlah instansi dilayani, dan SPPG mana yang melayani sebuah sekolah.',
                ['kecamatan' => ['type' => 'string', 'description' => 'Opsional, mis. "Ulu Musi"'], 'kata_kunci' => ['type' => 'string', 'description' => 'Opsional: nama SPPG, desa, atau sekolah, mis. "SD Negeri 7 Muara Pinang"']]),
            $fn('cari_tempat', 'Tempat umum di Kabupaten Empat Lawang dari peta (OpenStreetMap) + tautan pencarian Google Maps: penginapan/hotel, rumah makan, masjid, SPBU, rumah sakit/klinik, apotek, bank/ATM, pasar, wisata, sekolah, kantor. Data peta bisa belum lengkap: selalu sertakan tautan google_maps agar warga bisa mencari sendiri.',
                ['jenis' => ['type' => 'string', 'description' => 'mis. "penginapan", "rumah makan", "wisata", "spbu"'], 'lokasi' => ['type' => 'string', 'description' => 'Opsional: kecamatan/desa, mis. "Tebing Tinggi"']], ['jenis']),
            $fn('berita_google', 'Berita terbaru tentang Kabupaten Empat Lawang dari Google News (judul, nama media, tanggal). Pakai untuk kejadian/isu terkini di luar situs PPID.',
                ['kata_kunci' => ['type' => 'string', 'description' => 'Opsional, mis. "banjir", "kapolres"; otomatis dibatasi Empat Lawang']]),
            ...(PortalTools::enabled() ? PortalTools::definitions() : []),
            ...($web ? [$fn('cari_web', 'Cari di web publik (situs resmi pemerintah, media berita) bila data PPID di tool lain tidak punya jawabannya, mis. pejabat Forkopimda, kegiatan/berita di luar situs, regulasi umum. Hasilnya bukan data resmi PPID: sebut sumbernya dan bahwa informasi bisa belum terbaru.',
                ['pertanyaan' => ['type' => 'string', 'description' => 'Pertanyaan/kata kunci pencarian, singkat dan spesifik']], ['pertanyaan'])] : []),
        ];
    }

    /** Runs one tool call; the result goes back to the model as JSON. */
    public static function run(string $name, array $args): array
    {
        if (str_starts_with($name, 'portal_')) { // data portal tools live in PortalTools
            return PortalTools::run($name, $args);
        }
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
                'catatan' => 'Ini kontak PPID/Diskominfo, JANGAN disebut sebagai kontak instansi lain.',
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
            'harga_pangan' => ($p = FoodPrice::find((string) ($args['komoditas'] ?? ''))) ? $p + ['sumber' => 'Panel Harga Badan Pangan Nasional (harga konsumen)']
                : ['error' => 'Data harga tidak ditemukan/tidak tersedia saat ini. Arahkan ke panelharga.badanpangan.go.id.'],
            'cuaca' => self::weather((string) ($args['kecamatan'] ?? '')),
            'info_bencana' => ['kejadian' => array_map(fn ($e) => ['tanggal' => $e['date'], 'wilayah' => $e['region'], 'jenis' => $e['type'], 'dampak' => $e['impact'], 'kondisi' => mb_strimwidth((string) $e['status'], 0, 200, '…')],
                array_slice(BnpbDisaster::recent(), 0, 8)) ?: 'Tidak ada kejadian bencana tercatat 30 hari terakhir.', 'sumber' => 'BNPB'],
            'data_sppg' => SppgUnit::forAssistant($args['kecamatan'] ?? null, $args['kata_kunci'] ?? null),
            'cari_tempat' => self::places((string) ($args['jenis'] ?? ''), (string) ($args['lokasi'] ?? '')),
            'berita_google' => ['berita' => GoogleNews::search((string) ($args['kata_kunci'] ?? '')) ?: 'Tidak ada berita yang ditemukan.', 'sumber' => 'Google News; sebut nama medianya saja'],
            'daftar_instansi' => AgencyDirectory::list($args['kategori'] ?? null),
            'kontak_instansi' => AgencyDirectory::contact((string) ($args['instansi'] ?? '')) ?? self::web('Alamat kantor, nomor telepon/WhatsApp resmi dan jam layanan ' . mb_substr(trim((string) ($args['instansi'] ?? '')), 0, 100) . ' Kabupaten Empat Lawang. Hanya dari sumber yang menyebut instansi itu secara jelas; jangan menebak nomor.'),
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
        if ($query === '' || (string) config('services.openai.search_model') === '' || ! config('services.openai.key')) {
            return ['error' => 'Pencarian web tidak tersedia.'];
        }
        try {
            // Responses API + built-in web_search (the old *-search-preview chat models were retired).
            $output = Http::withToken(config('services.openai.key'))->timeout(60)->acceptJson()
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.search_model'),
                    'tools' => [['type' => 'web_search', 'search_context_size' => 'low',
                        'user_location' => ['type' => 'approximate', 'country' => 'ID', 'region' => 'Sumatera Selatan', 'city' => 'Empat Lawang']]],
                    'instructions' => 'Cari di web publik (utamakan situs resmi pemerintah, media berita tepercaya, dan Google Maps untuk tempat) dan jawab ringkas dalam bahasa Indonesia, maksimal 150 kata, tanpa tabel. Sebut tanggal/tahun informasi bila ada. Bila tidak menemukan sumber yang jelas, katakan tidak ditemukan; jangan menebak.',
                    'input' => $query . ' (Kabupaten Empat Lawang, Sumatera Selatan)',
                ])->throw()->json('output');
        } catch (\Throwable $e) {
            Log::warning('Sapa Madani web search error: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 200));

            return ['error' => 'Pencarian web gagal. Katakan jawabannya belum bisa dipastikan dan arahkan ke halaman Kontak atau Permohonan Informasi.'];
        }
        $content = collect(is_array($output) ? $output : [])->where('type', 'message')->flatMap(fn ($m) => $m['content'] ?? [])->where('type', 'output_text');
        $sources = $content->flatMap(fn ($c) => $c['annotations'] ?? [])->where('type', 'url_citation')
            ->map(fn ($c) => ['judul' => $c['title'] ?? '', 'url' => $c['url'] ?? ''])->unique('url')->take(5)->values()->all();

        return ['jawaban' => trim($content->pluck('text')->implode("\n")), 'sumber' => $sources,
            'catatan' => 'Dari web publik, bukan data resmi PPID. Sebut nama situs sumbernya saja (tanpa tautan) dan bahwa informasi bisa belum terbaru.'];
    }

    /**
     * Places from the map; when it has fewer than 3 named places (OSM is sparse in Empat Lawang), the web-search model
     * looks them up too, so "rekomendasi nginap" gets names instead of only a Google Maps link.
     */
    private static function places(string $kind, string $area): array
    {
        $found = PlaceFinder::find($kind, $area) + ['sumber' => 'OpenStreetMap (bisa belum lengkap)'];
        $named = array_filter($found['tempat'], fn ($p) => $p['nama'] !== '(tanpa nama di peta)');
        if (count($named) < 3) {
            $web = self::web('Rekomendasi ' . $found['jenis'] . ($area !== '' ? " di {$area}" : '') . ': nama tempat, alamat/desa, dan nomor telepon bila ada');
            $found['dari_web'] = $web['error'] ?? $web;
        }

        return $found;
    }

    /** BMKG forecast (next 24 hours) for a district by name + active early warnings for Empat Lawang. */
    private static function weather(string $district): array
    {
        $code = collect(BmkgWeather::DISTRICTS)->search(fn ($d) => mb_strtolower($d['name']) === mb_strtolower(trim($district))) ?: BmkgWeather::DEFAULT_DISTRICT;
        $slots = array_map(fn ($s) => ['waktu' => $s['time'] . ' WIB', 'cuaca' => $s['desc'], 'suhu' => $s['temp'] . '°C', 'kelembapan' => $s['humidity'] . '%'],
            BmkgWeather::upcoming(BmkgWeather::forecast($code)));

        return [
            'kecamatan' => BmkgWeather::DISTRICTS[$code]['name'],
            'prakiraan' => $slots ?: 'Data prakiraan sedang tidak tersedia.',
            'peringatan_dini' => array_map(fn ($a) => ['judul' => $a['title'], 'kecamatan' => $a['areas'], 'mulai' => $a['start'], 'hingga' => $a['end'], 'dampak' => $a['impact']], BmkgWeather::alerts()) ?: 'Tidak ada peringatan dini.',
            'sumber' => $slots[0]['source'] ?? 'BMKG',
        ];
    }

    /** Agenda start: a valid YYYY-MM-DD from the model, else today. */
    private static function date(?string $value): Carbon
    {
        return $value && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? Carbon::parse($value)->startOfDay() : now()->startOfDay();
    }
}
