<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Public places in Empat Lawang (lodging, food, worship, fuel, health, ...) for general questions, from OpenStreetMap
 * Nominatim (free, no key; policy: identify with User-Agent, ≤1 request/s, cache). OSM is sparse here, so every answer
 * also carries a Google Maps search link the citizen can open.
 * ponytail: Google Places API would be more complete but needs a paid key; add it behind this class when there is one.
 */
class PlaceFinder
{
    const URL = 'https://nominatim.openstreetmap.org/search';
    const VIEWBOX = '102.55,-3.45,103.3,-4.05'; // Kabupaten Empat Lawang (lon,lat,lon,lat)
    const TTL = 86400;

    /** Citizen's words → OSM search terms (Nominatim understands English categories best). */
    const KINDS = [
        'penginapan' => ['hotel', 'guest house'],
        'rumah makan' => ['restaurant', 'cafe'],
        'masjid' => ['masjid'],
        'spbu' => ['fuel', 'spbu'],
        'kesehatan' => ['hospital', 'clinic'],
        'apotek' => ['pharmacy'],
        'bank/atm' => ['bank', 'atm'],
        'pasar' => ['marketplace', 'pasar'],
        'wisata' => ['attraction', 'waterfall'],
        'sekolah' => ['school'],
        'kantor pemerintah' => ['townhall', 'kantor'],
    ];

    /** @return array{jenis: string, tempat: array<int, array{nama: string, jenis: string, alamat: string, peta: string}>, google_maps: string} */
    public static function find(string $kind, string $area = ''): array
    {
        $kind = self::kind($kind);
        $area = trim(mb_substr($area, 0, 60));
        $places = [];
        foreach (self::KINDS[$kind] ?? [$kind] as $term) {
            foreach (self::search(trim("{$term} {$area}")) as $p) {
                $places[$p['peta']] = $p;
            }
        }

        return [
            'jenis' => $kind,
            'tempat' => array_slice(array_values($places), 0, 10),
            'google_maps' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(implode(' ', array_filter([$kind, $area, 'Empat Lawang']))),
        ];
    }

    /** "hotel", "nginap", "losmen" → penginapan; unknown words are searched as given. */
    private static function kind(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $aliases = [
            'penginapan' => '/hotel|nginap|inap|losmen|wisma|homestay|guest/u',
            'rumah makan' => '/makan|resto|warung|kuliner|kafe|cafe|kopi/u',
            'masjid' => '/masjid|mushola|musala|surau/u',
            'spbu' => '/spbu|bensin|bbm|pom/u',
            'kesehatan' => '/rumah sakit|rs\b|klinik|puskesmas|dokter/u',
            'apotek' => '/apotek|apotik|obat/u',
            'bank/atm' => '/bank|atm/u',
            'pasar' => '/pasar/u',
            'wisata' => '/wisata|air terjun|curup|danau|bukit|pemandangan/u',
            'sekolah' => '/sekolah|sd\b|smp|sma|smk/u',
            'kantor pemerintah' => '/kantor|camat|desa|dinas/u',
        ];
        foreach ($aliases as $kind => $pattern) {
            if (isset(self::KINDS[$text]) || preg_match($pattern, $text)) {
                return isset(self::KINDS[$text]) ? $text : $kind;
            }
        }

        return mb_substr($text, 0, 40);
    }

    private static function search(string $query): array
    {
        $key = 'place:' . md5($query);
        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }
        try {
            $rows = Http::timeout(15)->withUserAgent('PPID-EmpatLawang/1.0 (' . config('app.url') . ')')
                ->get(self::URL, ['q' => $query, 'format' => 'jsonv2', 'limit' => 8, 'viewbox' => self::VIEWBOX, 'bounded' => 1, 'accept-language' => 'id'])
                ->throw()->json();
        } catch (\Throwable $e) {
            Log::warning('Nominatim search failed: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 150));

            return [];
        }
        $places = [];
        foreach (is_array($rows) ? $rows : [] as $r) {
            $places[] = [
                'nama' => ($r['name'] ?? '') !== '' ? $r['name'] : '(tanpa nama di peta)',
                'jenis' => str_replace('_', ' ', (string) ($r['type'] ?? '')),
                'alamat' => (string) ($r['display_name'] ?? ''),
                'peta' => "https://www.google.com/maps/search/?api=1&query={$r['lat']},{$r['lon']}",
            ];
        }
        Cache::put($key, $places, self::TTL);

        return $places;
    }
}
