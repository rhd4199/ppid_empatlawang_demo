<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Disaster events from BNPB open GIS data (gis.bnpb.go.id, "Kejadian Bencana Mingguan" layer, attribution "BNPB")
 * for Empat Lawang and the regencies bordering it. The layer only holds the current week, so every fetched event is
 * also kept in the cache for HISTORY_DAYS to show the last weeks.
 * ponytail: history lives in the cache store (database in production); move it to a table if it must survive cache:clear.
 */
class BnpbDisaster
{
    const URL = 'https://gis.bnpb.go.id/server/rest/services/Kejadian_Bencana_Mingguan/FeatureServer/25/query';

    // Empat Lawang first, then the regencies whose BNPB boundary touches it (checked with an intersect query).
    const REGIONS = ['Empat Lawang', 'Lahat', 'Musi Rawas', 'Kepahiang', 'Rejang Lebong', 'Seluma'];

    const HISTORY_DAYS = 30;

    const FIELDS = ['id', 'dt', 'kabupaten', 'kategori_bencana', 'meninggal', 'hilang', 'luka_sakit', 'mengungsi', 'menderita',
        'rumah_rusak', 'rumah_terendam', 'kronologis', 'kondisi_mutakhir'];

    /** Events of the last HISTORY_DAYS days, newest first; [] when BNPB has none or was never reachable. */
    public static function recent(): array
    {
        $history = Cache::get('disaster:history', []);
        if (! Cache::has('disaster:fetched')) {
            try {
                foreach (self::fetch() as $event) {
                    $history[$event['id']] = $event;
                }
                Cache::put('disaster:fetched', now('Asia/Jakarta')->format('Y-m-d H:i:s'), 3600);
            } catch (\Throwable $e) {
                Log::warning('BNPB disaster fetch failed: ' . $e->getMessage());
                Cache::put('disaster:fetched', '', 300); // retry in 5 minutes, not on every visit ('' because null counts as missing)
            }
            $since = now('Asia/Jakarta')->subDays(self::HISTORY_DAYS)->format('Y-m-d');
            $history = array_filter($history, fn ($e) => $e['date'] >= $since);
            Cache::forever('disaster:history', $history);
        }
        usort($history, fn ($a, $b) => [$b['date'], $a['region']] <=> [$a['date'], $b['region']]);

        return array_values($history);
    }

    /** Totals for the infographic: event count, summed impact, count per type (most first) and per region (all REGIONS, zeros kept). */
    public static function stats(array $events): array
    {
        $impact = [];
        $types = [];
        $regions = array_fill_keys(self::REGIONS, 0);
        foreach ($events as $e) {
            foreach ($e['impact'] as $key => $value) {
                $impact[$key] = ($impact[$key] ?? 0) + $value;
            }
            $types[$e['type']] = ($types[$e['type']] ?? 0) + 1;
            if (isset($regions[$e['region']])) {
                $regions[$e['region']]++;
            }
        }
        arsort($types);

        return ['total' => count($events), 'impact' => (object) $impact, 'types' => (object) $types, 'regions' => $regions];
    }

    public static function fetchedAt(): ?string
    {
        return Cache::get('disaster:fetched') ?: null;
    }

    private static function fetch(): array
    {
        $regions = implode(',', array_map(fn ($r) => "'{$r}'", self::REGIONS)); // constants only, no user input
        $features = Http::timeout(8)->acceptJson()->get(self::URL, [
            'where' => "kabupaten IN ({$regions})",
            'outFields' => implode(',', self::FIELDS),
            'orderByFields' => 'dt DESC', // the server rejects some queries without it
            'returnGeometry' => 'false',
            'f' => 'json',
        ])->throw()->json();
        if (isset($features['error'])) {
            throw new \RuntimeException(json_encode($features['error']));
        }

        return array_map(fn ($f) => self::event($f['attributes']), $features['features'] ?? []);
    }

    private static function event(array $a): array
    {
        $text = fn ($v, $max) => \Illuminate\Support\Str::limit(trim(preg_replace('/[●•\s]+/u', ' ', (string) $v)), $max);

        return [
            'id' => (string) $a['id'],
            'date' => gmdate('Y-m-d', intdiv((int) $a['dt'], 1000)), // dt = the event day at 00:00 UTC
            'region' => (string) $a['kabupaten'],
            'type' => (string) $a['kategori_bencana'],
            'impact' => array_filter([
                'meninggal' => (int) $a['meninggal'], 'hilang' => (int) $a['hilang'], 'luka' => (int) $a['luka_sakit'],
                'mengungsi' => (int) $a['mengungsi'], 'terdampak' => (int) $a['menderita'],
                'rumah rusak' => (int) $a['rumah_rusak'], 'rumah terendam' => (int) $a['rumah_terendam'],
            ]),
            'summary' => $text($a['kronologis'] ?? '', 280),
            'status' => $text($a['kondisi_mutakhir'] ?? '', 160),
        ];
    }
}
