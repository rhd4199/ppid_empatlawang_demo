<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BMKG open data (free, attribution "BMKG" required): 3-day forecast per village and nowcast early warnings.
 * Results are cached so visitors never hit BMKG directly (limit: 60 req/min per IP).
 */
class BmkgWeather
{
    const DEFAULT_DISTRICT = '04';

    // Empat Lawang (16.11) districts → one adm4 village code BMKG has data for.
    const DISTRICTS = [
        '04' => ['name' => 'Tebing Tinggi', 'adm4' => '16.11.04.2001'],
        '01' => ['name' => 'Muara Pinang', 'adm4' => '16.11.01.2001'],
        '02' => ['name' => 'Pendopo', 'adm4' => '16.11.02.2001'],
        '03' => ['name' => 'Ulu Musi', 'adm4' => '16.11.03.2001'],
        '05' => ['name' => 'Lintang Kanan', 'adm4' => '16.11.05.2001'],
        '06' => ['name' => 'Talang Padang', 'adm4' => '16.11.06.2001'],
        '07' => ['name' => 'Pasemah Air Keruh', 'adm4' => '16.11.07.2001'],
        '08' => ['name' => 'Sikap Dalam', 'adm4' => '16.11.08.2001'],
        '09' => ['name' => 'Saling', 'adm4' => '16.11.09.2001'],
        '10' => ['name' => 'Pendopo Barat', 'adm4' => '16.11.10.2001'],
    ];

    const FORECAST_URL = 'https://api.bmkg.go.id/publik/prakiraan-cuaca';
    const NOWCAST_URL = 'https://www.bmkg.go.id/alerts/nowcast/id';

    /** Upcoming 3-hourly slots (first = current) for a district, [] when BMKG is unreachable. */
    public static function forecast(string $district): array
    {
        $adm4 = self::DISTRICTS[$district]['adm4'];

        return self::remember("bmkg:forecast:$adm4", 3600, function () use ($adm4) {
            $days = Http::timeout(6)->acceptJson()->get(self::FORECAST_URL, ['adm4' => $adm4])->throw()->json('data.0.cuaca');

            return array_map(fn ($s) => [
                'time' => $s['local_datetime'],
                'temp' => $s['t'],
                'humidity' => $s['hu'],
                'desc' => $s['weather_desc'],
                'wind' => $s['ws'],
                'wind_dir' => $s['wd'],
                'icon' => $s['image'] ?? null,
            ], array_merge(...($days ?: [[]])));
        });
    }

    /** Slots from the one covering "now" onward (BMKG slots are 3 hours long). */
    public static function upcoming(array $slots, int $limit = 8): array
    {
        $from = now('Asia/Jakarta')->subMinutes(90)->format('Y-m-d H:i:s');

        return array_slice(array_values(array_filter($slots, fn ($s) => $s['time'] >= $from)), 0, $limit);
    }

    /** Active BMKG early warnings that name Empat Lawang or one of its districts. */
    public static function alerts(): array
    {
        return self::remember('bmkg:nowcast', 900, function () {
            $xml = simplexml_load_string(Http::timeout(6)->get(self::NOWCAST_URL)->throw()->body(), 'SimpleXMLElement', LIBXML_NONET);

            return self::filterAlerts($xml ? $xml->xpath('//item') : []);
        });
    }

    public static function filterAlerts(iterable $items): array
    {
        $names = array_merge(['EMPAT LAWANG'], array_map(fn ($d) => strtoupper($d['name']), self::DISTRICTS));
        $alerts = [];
        foreach ($items as $item) {
            $title = trim((string) $item->title);
            $desc = trim((string) $item->description);
            if (! str_contains($title, 'Sumatera Selatan')) {
                continue;
            }
            $hits = array_values(array_filter($names, fn ($n) => preg_match('/\b' . preg_quote($n, '/') . '\b/', strtoupper($desc))));
            if ($hits) {
                $alerts[] = ['title' => $title, 'description' => $desc, 'areas' => $hits, 'link' => (string) $item->link];
            }
        }

        return $alerts;
    }

    // Cache successes only, so a BMKG outage is retried on the next request instead of being stored as "no data".
    private static function remember(string $key, int $ttl, callable $fetch): array
    {
        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }
        try {
            $value = $fetch();
        } catch (\Throwable $e) {
            Log::warning("BMKG fetch failed ($key): " . $e->getMessage());

            return [];
        }
        Cache::put($key, $value, $ttl);

        return $value;
    }
}
