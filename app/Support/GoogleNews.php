<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Latest news about Empat Lawang from the public Google News RSS (no key), cached 15 minutes. */
class GoogleNews
{
    const URL = 'https://news.google.com/rss/search';
    const TTL = 900;

    /** @return array<int, array{judul: string, sumber: string, tanggal: string}> (no links: the owner wants sources named only) */
    public static function search(string $query = '', int $limit = 8): array
    {
        $query = trim(mb_substr($query, 0, 100));
        // Always scoped to the regency, so "banjir" means banjir in Empat Lawang.
        $q = (stripos($query, 'empat lawang') === false ? '"Empat Lawang" ' : '') . $query;
        $key = 'gnews:' . md5($q);
        if (! is_array($items = Cache::get($key))) {
            try {
                $xml = Http::timeout(15)->get(self::URL, ['q' => $q, 'hl' => 'id', 'gl' => 'ID', 'ceid' => 'ID:id'])->throw()->body();
                $rss = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
            } catch (\Throwable $e) {
                Log::warning('Google News fetch failed: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 150));

                return [];
            }
            $items = [];
            foreach ($rss ? $rss->channel->item : [] as $item) {
                $source = (string) $item->source;
                $items[] = [
                    // Google titles end with " - <source>"
                    'judul' => trim(preg_replace('/\s+-\s+' . preg_quote($source, '/') . '$/u', '', (string) $item->title)),
                    'sumber' => $source,
                    'tanggal' => ($t = strtotime((string) $item->pubDate)) ? date('d-m-Y', $t) : '',
                ];
            }
            Cache::put($key, $items, self::TTL);
        }

        return array_slice($items, 0, $limit);
    }
}
