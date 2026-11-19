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

    // Empat Lawang (16.11) districts → one adm4 village code BMKG has data for (lat/lon from BMKG, used by the fallback).
    const DISTRICTS = [
        '04' => ['name' => 'Tebing Tinggi', 'adm4' => '16.11.04.2001', 'lat' => -3.7053, 'lon' => 103.0214],
        '01' => ['name' => 'Muara Pinang', 'adm4' => '16.11.01.2001', 'lat' => -3.7800, 'lon' => 103.0691],
        '02' => ['name' => 'Pendopo', 'adm4' => '16.11.02.2001', 'lat' => -3.8976, 'lon' => 102.9273],
        '03' => ['name' => 'Ulu Musi', 'adm4' => '16.11.03.2001', 'lat' => -3.7907, 'lon' => 102.6755],
        '05' => ['name' => 'Lintang Kanan', 'adm4' => '16.11.05.2001', 'lat' => -3.8271, 'lon' => 102.9746],
        '06' => ['name' => 'Talang Padang', 'adm4' => '16.11.06.2001', 'lat' => -3.7212, 'lon' => 102.9516],
        '07' => ['name' => 'Pasemah Air Keruh', 'adm4' => '16.11.07.2001', 'lat' => -3.9201, 'lon' => 102.8222],
        '08' => ['name' => 'Sikap Dalam', 'adm4' => '16.11.08.2001', 'lat' => -3.8294, 'lon' => 102.8374],
        '09' => ['name' => 'Saling', 'adm4' => '16.11.09.2001', 'lat' => -3.5468, 'lon' => 103.0837],
        '10' => ['name' => 'Pendopo Barat', 'adm4' => '16.11.10.2001', 'lat' => -3.7562, 'lon' => 102.9654],
    ];

    const FORECAST_URL = 'https://api.bmkg.go.id/publik/prakiraan-cuaca';
    const NOWCAST_URL = 'https://www.bmkg.go.id/alerts/nowcast/id';

    const OPEN_METEO_URL = 'https://api.open-meteo.com/v1/forecast';

    /** 3-hourly slots for a district: BMKG first, Open-Meteo (free, no key) when BMKG fails or is empty; [] when both fail. */
    public static function forecast(string $district): array
    {
        return self::remember("weather:forecast:$district", 3600, function () use ($district) {
            try {
                if ($slots = self::bmkgForecast(self::DISTRICTS[$district]['adm4'])) {
                    return $slots;
                }
            } catch (\Throwable $e) {
                Log::warning("BMKG forecast failed ($district), using Open-Meteo: " . $e->getMessage());
            }

            return self::openMeteoForecast(self::DISTRICTS[$district]);
        });
    }

    private static function bmkgForecast(string $adm4): array
    {
        $days = Http::timeout(6)->acceptJson()->get(self::FORECAST_URL, ['adm4' => $adm4])->throw()->json('data.0.cuaca');

        return array_map(fn ($s) => [
            'time' => $s['local_datetime'],
            'temp' => $s['t'],
            'humidity' => $s['hu'],
            'desc' => $s['weather_desc'],
            'wind' => $s['ws'],
            'wind_dir' => $s['wd'],
            'icon' => $s['image'] ?? null,
            'source' => 'BMKG',
        ], array_merge(...($days ?: [[]])));
    }

    private static function openMeteoForecast(array $d): array
    {
        $h = Http::timeout(6)->acceptJson()->get(self::OPEN_METEO_URL, [
            'latitude' => $d['lat'], 'longitude' => $d['lon'], 'timezone' => 'Asia/Jakarta', 'forecast_days' => 3,
            'hourly' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,wind_direction_10m',
        ])->throw()->json('hourly');

        $slots = [];
        foreach ($h['time'] ?? [] as $i => $time) {
            if ((int) substr($time, 11, 2) % 3 !== 2) {
                continue; // keep BMKG's 3-hour rhythm (02, 05, 08 … WIB)
            }
            $slots[] = [
                'time' => str_replace('T', ' ', $time) . ':00',
                'temp' => (int) round($h['temperature_2m'][$i]),
                'humidity' => $h['relative_humidity_2m'][$i],
                'desc' => self::wmoDesc($h['weather_code'][$i]),
                'wind' => round($h['wind_speed_10m'][$i], 1),
                'wind_dir' => ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][(int) round($h['wind_direction_10m'][$i] / 45) % 8],
                'icon' => null,
                'source' => 'Open-Meteo',
            ];
        }

        return $slots;
    }

    // WMO weather code → BMKG-style Indonesian description.
    public static function wmoDesc(int $code): string
    {
        return match (true) {
            $code === 0 => 'Cerah',
            $code <= 2 => 'Cerah Berawan',
            $code === 3 => 'Berawan',
            $code <= 48 => 'Kabut',
            $code <= 57 => 'Gerimis',
            $code <= 61, $code === 80 => 'Hujan Ringan',
            $code <= 63, $code === 81 => 'Hujan Sedang',
            $code <= 67, $code === 82 => 'Hujan Lebat',
            $code <= 86 => 'Hujan',
            default => 'Hujan Petir',
        };
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
