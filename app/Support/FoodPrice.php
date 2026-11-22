<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Consumer food prices (harga eceran) from Panel Harga Badan Pangan Nasional, public API behind
 * panelharga.badanpangan.go.id (no key). Kab. Empat Lawang first; Sumatera Selatan when the regency has no figures.
 * Cached 10 minutes (successes only). Skill: workspace skills/bapanas-food-price.
 */
class FoodPrice
{
    const URL = 'https://api-panelhargav2.badanpangan.go.id/api/front/harga-pangan-informasi';
    const PROVINCE = 6;   // Sumatera Selatan
    const CITY = 109;     // Kab. Empat Lawang
    const LEVEL = 3;      // konsumen / eceran
    const TTL = 600;

    /**
     * @return array{wilayah: string, harga: array<int, array{komoditas: string, harga: int, satuan: string, tanggal: string, perubahan: ?int}>}|array{}
     */
    public static function latest(): array
    {
        foreach ([['Kabupaten Empat Lawang', self::CITY], ['Provinsi Sumatera Selatan (rata-rata)', '']] as [$area, $city]) {
            $rows = self::fetch($city);
            if ($rows) {
                return ['wilayah' => $area, 'harga' => $rows];
            }
        }

        return [];
    }

    /** Rows for one commodity name fragment ("cabe" also finds "Cabai"). */
    public static function find(string $term): array
    {
        $latest = self::latest();
        $term = str_replace('cabe', 'cabai', mb_strtolower(trim($term)));
        $words = array_filter(preg_split('/\s+/u', $term), fn ($w) => mb_strlen($w) >= 3);
        $rows = array_values(array_filter($latest['harga'] ?? [], fn ($r) => $words === [] || (bool) array_filter($words, fn ($w) => str_contains(mb_strtolower($r['komoditas']), $w))));

        return $rows ? ['wilayah' => $latest['wilayah'], 'harga' => $rows] : [];
    }

    /** Non-empty priced rows; "today" is often 0 until the day's input is in, then yesterday's figure is used. */
    private static function fetch(int|string $city): array
    {
        $key = "food-price:{$city}";
        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }
        try {
            $data = Http::timeout(15)->acceptJson()->withUserAgent('Mozilla/5.0 PPID Empat Lawang')
                ->get(self::URL, ['province_id' => self::PROVINCE, 'city_id' => $city, 'level_harga_id' => self::LEVEL])
                ->throw()->json('data');
        } catch (\Throwable $e) {
            Log::warning('Panel Harga fetch failed: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 200));

            return [];
        }
        $rows = [];
        foreach (is_array($data) ? $data : [] as $r) {
            $today = (int) ($r['today'] ?? 0);
            $price = $today ?: (int) ($r['yesterday'] ?? 0);
            if ($price <= 0) {
                continue;
            }
            $rows[] = [
                'komoditas' => (string) $r['name'],
                'harga' => $price,
                'satuan' => str_replace(['Rp./', 'Rp/'], '', (string) ($r['satuan'] ?? '')),
                'tanggal' => $today ? now('Asia/Jakarta')->format('d-m-Y') : (string) ($r['yesterday_date'] ?? ''),
                'perubahan' => $today && isset($r['yesterday']) && $r['yesterday'] ? $today - (int) $r['yesterday'] : null,
            ];
        }
        if ($rows) {
            Cache::put($key, $rows, self::TTL);
        }

        return $rows;
    }
}
