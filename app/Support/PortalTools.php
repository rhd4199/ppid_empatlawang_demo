<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI tools over the Portal Data Empat Lawang public API (/api/v1, OpenAPI at <base>/api/v1/openapi.json, docs at
 * <base>/dokumentasi-api/): BPS statistics, village conditions (PODES), DTSEN aggregates, POI, settlements.
 * The base URL is config('services.data_portal.url') = env DATA_PORTAL_URL, so the dev portal can be swapped for the
 * production domain without code changes. Read-only GETs, cached 10 minutes, responses trimmed for the model.
 */
class PortalTools
{
    const TTL = 600;
    const MAX_ROWS = 15;
    const MAX_CHARS = 7000;

    public static function base(): string
    {
        return rtrim((string) config('services.data_portal.url'), '/');
    }

    public static function enabled(): bool
    {
        return self::base() !== '';
    }

    public static function definitions(): array
    {
        $fn = fn (string $name, string $description, array $properties = [], array $required = []) => ['type' => 'function', 'function' => [
            'name' => $name, 'description' => $description,
            'parameters' => ['type' => 'object', 'properties' => (object) $properties, 'required' => $required],
        ]];
        $s = fn (string $d) => ['type' => 'string', 'description' => $d];

        return [
            $fn('portal_cari_dataset', 'Cari dataset di Portal Data Empat Lawang (statistik BPS, kemiskinan, kondisi desa/PODES, DTSEN, pendidikan, kesehatan, fasilitas, dll.). Mengembalikan nama dataset, judul, sumber. Mulai dari sini untuk pertanyaan data/statistik, lalu portal_info_dataset dan portal_baris.',
                ['kata_kunci' => $s('mis. "kemiskinan", "penduduk", "sekolah"; kosong = semua'), 'topik' => $s('Opsional, nama grup topik')]),
            $fn('portal_info_dataset', 'Metadata satu dataset: judul, sumber, lisensi, dan daftar kolom (id kolom dipakai sebagai filter di portal_baris).',
                ['dataset' => $s('nama dataset dari portal_cari_dataset')], ['dataset']),
            $fn('portal_baris', 'Ambil baris data sebuah dataset dengan filter sama-dengan per kolom dan/atau teks bebas. Maksimal 15 baris dikembalikan, plus total. Sebut tahun/sumber data dalam jawaban.',
                ['dataset' => $s('nama dataset'), 'filter' => ['type' => 'object', 'description' => 'kolom → nilai, mis. {"kecamatan":"Pendopo","tahun":"2024"}; id kolom dari portal_info_dataset', 'additionalProperties' => ['type' => 'string']],
                    'q' => $s('Cari teks di semua kolom'), 'jumlah' => ['type' => 'integer', 'description' => '1–15, bawaan 10']], ['dataset']),
            $fn('portal_wilayah', 'Data wilayah Empat Lawang: daftar kecamatan (jumlah desa, luas), daftar desa (kode Kemendagri, kecamatan, luas), atau profil lengkap satu desa (semua indikator, fasilitas, permukiman).',
                ['jenis' => ['type' => 'string', 'enum' => ['kecamatan', 'desa', 'profil_desa']], 'kecamatan' => $s('saring desa per kecamatan'), 'nama_desa' => $s('cari desa dengan nama mengandung kata ini'),
                    'kode_desa' => $s('kode Kemendagri untuk profil_desa, mis. 16.11.01.2001')], ['jenis']),
            $fn('portal_titik', 'Titik lokasi di Empat Lawang dengan koordinat: POI (fasilitas umum), daftar fasilitas per desa, atau pusat permukiman. Saring per kolom (mis. kecamatan, kode_desa, kategori) atau teks bebas; hasil memuat nama, desa, lat/lng. Kolom tepatnya ada di hasil (kolom) atau lewat portal_info_dataset.',
                ['jenis' => ['type' => 'string', 'enum' => ['poi', 'fasilitas', 'permukiman']], 'filter' => ['type' => 'object', 'additionalProperties' => ['type' => 'string'], 'description' => 'kolom → nilai'],
                    'q' => $s('teks bebas, mis. "puskesmas"'), 'jumlah' => ['type' => 'integer', 'description' => '1–15, bawaan 10']], ['jenis']),
            $fn('portal_geo', 'Lapisan peta Empat Lawang (tanpa bentuk poligon, hanya ringkasan atribut): batas desa, batas kecamatan, jalan (kelas arteri/kolektor/lokal/…), sungai, dan sebaran penduduk. Pakai untuk pertanyaan seperti berapa desa di sebuah kecamatan, luas wilayah, nama jalan/sungai.',
                ['lapisan' => ['type' => 'string', 'enum' => ['desa', 'kecamatan', 'jalan', 'sungai', 'penduduk-grid']], 'nama' => $s('saring nama mengandung kata ini'), 'jumlah' => ['type' => 'integer', 'description' => '1–15, bawaan 10']], ['lapisan']),
            $fn('portal_pembaruan', 'Riwayat pembaruan API dan data Portal Data (terbaru di atas): kapan data terakhir diperbarui dan apa yang berubah.'),
            $fn('portal_survei', 'Ringkasan survei kepuasan pengguna Portal Data.'),
        ];
    }

    public static function run(string $name, array $args): array
    {
        if (! self::enabled()) {
            return ['error' => 'Portal Data belum diatur (DATA_PORTAL_URL kosong).'];
        }
        $str = fn (string $k, int $max = 100) => trim(mb_substr((string) ($args[$k] ?? ''), 0, $max));

        return match ($name) {
            'portal_cari_dataset' => self::datasets($str('kata_kunci'), $str('topik')),
            'portal_info_dataset' => self::get('/api/v1/datasets/' . rawurlencode($str('dataset')), [], fn ($j) => self::meta($j['result'] ?? [])),
            'portal_baris' => self::rows($str('dataset'), is_array($args['filter'] ?? null) ? $args['filter'] : [], $str('q'), (int) ($args['jumlah'] ?? 10)),
            'portal_titik' => self::points(in_array($args['jenis'] ?? '', ['poi', 'fasilitas', 'permukiman'], true) ? $args['jenis'] : 'poi', is_array($args['filter'] ?? null) ? $args['filter'] : [], $str('q'), (int) ($args['jumlah'] ?? 10)),
            'portal_geo' => self::geo($str('lapisan'), $str('nama'), (int) ($args['jumlah'] ?? 10)),
            'portal_pembaruan' => self::get('/api/v1/changelog', [], fn ($j) => ['pembaruan' => array_slice($j['entries'] ?? [], 0, 5)]),
            'portal_survei' => self::get('/api/v1/feedback/summary', [], fn ($j) => $j),
            'portal_wilayah' => self::region($str('jenis'), $str('kecamatan'), $str('nama_desa'), $str('kode_desa', 30)),
            default => ['error' => "Tool {$name} tidak ada."],
        };
    }

    private static function datasets(string $q, string $group): array
    {
        return self::get('/api/v1/datasets', array_filter(['q' => $q, 'group' => $group]), fn ($j) => [
            'jumlah' => $j['count'] ?? count($j['result'] ?? []),
            'dataset' => array_map(fn ($d) => array_filter([
                'nama' => $d['name'] ?? null, 'judul' => $d['title'] ?? null, 'sumber' => $d['source'] ?? null,
                'topik' => $d['group'] ?? null, 'spasial' => $d['spatial'] ?? null, 'tahun' => $d['year'] ?? ($d['period'] ?? null),
            ]), array_slice($j['result'] ?? [], 0, 30)),
        ]);
    }

    private static function meta(array $m): array
    {
        return array_filter([
            'nama' => $m['name'] ?? null, 'judul' => $m['title'] ?? null, 'deskripsi' => isset($m['description']) ? mb_strimwidth((string) $m['description'], 0, 400, '…') : null,
            'sumber' => $m['source'] ?? null, 'lisensi' => $m['license'] ?? null, 'jumlah_baris' => $m['rows'] ?? null,
            'kolom' => array_map(fn ($f) => array_filter(['id' => $f['id'] ?? null, 'label' => $f['label'] ?? ($f['title'] ?? null), 'tipe' => $f['type'] ?? null]), $m['fields'] ?? []),
        ]);
    }

    /** Query string for a records call: limit, free text and up to 6 equality filters on well-formed column ids. */
    private static function query(array $filter, string $q, int $n): array
    {
        $query = ['limit' => min(max($n, 1), self::MAX_ROWS)] + array_filter(['q' => $q]);
        foreach (array_slice($filter, 0, 6, true) as $column => $value) {
            if (is_string($column) && preg_match('/^[a-z0-9_]{1,40}$/i', $column) && is_scalar($value)) {
                $query[$column] = (string) $value;
            }
        }

        return $query;
    }

    /** Point layers (/poi, /fasilitas, /permukiman): the API returns every row, so cut to $n here. */
    private static function points(string $kind, array $filter, string $q, int $n): array
    {
        $n = min(max($n, 1), self::MAX_ROWS);

        return self::get("/api/v1/{$kind}", self::query($filter, $q, $n), fn ($j) => [
            'lapisan' => $kind, 'total' => $j['total'] ?? count($j['records'] ?? []), 'ditampilkan' => min($n, count($j['records'] ?? [])),
            'baris' => array_slice($j['records'] ?? [], 0, $n),
        ]);
    }

    /** GeoJSON layers without geometry: feature count + the properties of the first matches. */
    private static function geo(string $layer, string $name, int $n): array
    {
        if (! in_array($layer, ['desa', 'kecamatan', 'jalan', 'sungai', 'penduduk-grid'], true)) {
            return ['error' => 'lapisan harus desa, kecamatan, jalan, sungai atau penduduk-grid.'];
        }
        $n = min(max($n, 1), self::MAX_ROWS);

        return self::get("/api/v1/geo/{$layer}", [], function ($j) use ($name, $n, $layer) {
            $props = array_map(fn ($f) => $f['properties'] ?? [], $j['features'] ?? []);
            $hit = $name === '' ? $props : array_values(array_filter($props, fn ($p) => str_contains(mb_strtolower(json_encode($p, JSON_UNESCAPED_UNICODE)), mb_strtolower($name))));

            return ['lapisan' => $layer, 'jumlah_fitur' => count($props), 'cocok' => count($hit), 'atribut' => array_slice($hit, 0, $n)];
        });
    }

    private static function rows(string $dataset, array $filter, string $q, int $n): array
    {
        $query = self::query($filter, $q, $n);

        return self::get('/api/v1/datasets/' . rawurlencode($dataset) . '/records', $query, fn ($j) => [
            'dataset' => $j['dataset'] ?? $dataset, 'total' => $j['total'] ?? null, 'ditampilkan' => count($j['records'] ?? []),
            'kolom' => array_column($j['fields'] ?? [], 'id'), 'baris' => $j['records'] ?? [],
        ]);
    }

    private static function region(string $kind, string $kecamatan, string $name, string $code): array
    {
        return match ($kind) {
            'kecamatan' => self::get('/api/v1/wilayah/kecamatan', [], fn ($j) => ['kecamatan' => $j['result'] ?? []]),
            'desa' => self::get('/api/v1/wilayah/desa', array_filter(['kecamatan' => $kecamatan]), function ($j) use ($name) {
                $list = array_values(array_filter($j['result'] ?? [], fn ($d) => $name === '' || str_contains(mb_strtolower($d['desa'] ?? ''), mb_strtolower($name))));

                return ['jumlah' => count($list), 'desa' => array_map(fn ($d) => array_diff_key($d, ['links' => 1]), array_slice($list, 0, 40))];
            }),
            'profil_desa' => preg_match('/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/', $code)
                ? self::get('/api/v1/wilayah/desa/' . $code, [], fn ($j) => ($j['result'] ?? []) + [])
                : ['error' => 'kode_desa harus berformat Kemendagri, mis. 16.11.01.2001 (cari dulu lewat jenis "desa").'],
            default => ['error' => 'jenis harus kecamatan, desa atau profil_desa.'],
        };
    }

    /** One cached GET; $shape trims the JSON for the model. Never throws: failures become an honest error. */
    private static function get(string $path, array $query, callable $shape): array
    {
        $key = 'portal:' . md5($path . json_encode($query));
        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }
        try {
            $res = Http::timeout(20)->acceptJson()->get(self::base() . $path, $query);
        } catch (\Throwable $e) {
            Log::warning('Portal Data fetch failed: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 150));

            return ['error' => 'Portal Data sedang tidak dapat dihubungi. Katakan datanya belum bisa diambil sekarang.'];
        }
        if ($res->failed()) {
            // 4xx carries a useful message ("kolom tidak dikenal… Kolom tersedia: …") for the model; 5xx does not.
            return ['error' => $res->status() >= 500 ? 'Portal Data sedang tidak tersedia (HTTP ' . $res->status() . ').' : (string) ($res->json('error') ?? 'Tidak ditemukan.')];
        }
        $out = $shape($res->json() ?? []);
        // keep the model's context small
        while (mb_strlen(json_encode($out, JSON_UNESCAPED_UNICODE)) > self::MAX_CHARS && isset($out['baris']) && count($out['baris']) > 1) {
            $out['baris'] = array_slice($out['baris'], 0, intdiv(count($out['baris']), 2));
            $out['ditampilkan'] = count($out['baris']);
        }
        $out['sumber_api'] = self::base() . $path;
        Cache::put($key, $out, self::TTL);

        return $out;
    }
}
