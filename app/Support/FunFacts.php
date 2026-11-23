<?php

namespace App\Support;

use App\Models\SppgUnit;

/**
 * "Tahukah Anda?" info for short chat openers ("P p info"): owner wants light general info from our own data —
 * today's weather, food prices, the latest news, disasters nearby, a random statistic, SPPG — or a fixed sourced fact.
 * A random kind is picked; kinds whose source is down are skipped. Never invented: the AI phrases the data it gets.
 */
class FunFacts
{
    /** [fact, source] — checked 2026-11-23. */
    const FIXED = [
        ['Semboyan Kabupaten Empat Lawang adalah "Saling Keruani Sangi Kerawati".', 'lambang daerah Kabupaten Empat Lawang (Wikipedia)'],
        ['Ibu kota Kabupaten Empat Lawang adalah Tebing Tinggi.', 'Wikipedia, BPS Kabupaten Empat Lawang'],
        ['Nama Empat Lawang berasal dari sebutan "Empat Lawangan", artinya empat pendekar atau orang hebat.', 'detik.com, Sejarah Kabupaten Empat Lawang'],
        ['Kabupaten Empat Lawang dibentuk tahun 2007 sebagai pemekaran dari Kabupaten Lahat (UU Nomor 1 Tahun 2007).', 'UU No. 1 Tahun 2007 (peraturan.go.id, JDIH BPK)'],
    ];

    const HINT = 'Sampaikan sebagai SATU info singkat yang ringan (1–2 kalimat), angka/waktu persis seperti di data, sebut sumbernya singkat.';

    public static function random(): array
    {
        $kinds = ['weather', 'price', 'news', 'disaster', 'statistic', 'sppg'];
        shuffle($kinds);
        foreach ($kinds as $kind) {
            if ($info = self::$kind()) {
                return $info + ['petunjuk' => self::HINT];
            }
        }
        [$fact, $source] = self::FIXED[array_rand(self::FIXED)];

        return ['jenis' => 'fakta', 'fakta' => $fact, 'sumber' => $source];
    }

    private static function weather(): ?array
    {
        $slots = array_slice(BmkgWeather::upcoming(BmkgWeather::forecast(BmkgWeather::DEFAULT_DISTRICT)), 0, 3);
        if (! $slots) {
            return null;
        }
        $alert = BmkgWeather::alerts()[0]['title'] ?? null;

        return ['jenis' => 'cuaca', 'wilayah' => 'Tebing Tinggi', 'prakiraan' => array_map(fn ($s) => substr($s['time'], 11, 5) . " WIB {$s['desc']} {$s['temp']}°C", $slots),
            'peringatan_dini' => $alert, 'sumber' => $slots[0]['source'] ?? 'BMKG'];
    }

    private static function price(): ?array
    {
        $rows = FoodPrice::latest()['harga'] ?? [];
        if (! $rows) {
            return null;
        }
        $r = $rows[array_rand($rows)];

        return ['jenis' => 'harga pangan', 'komoditas' => $r['komoditas'], 'harga' => 'Rp' . number_format($r['harga'], 0, ',', '.') . '/' . $r['satuan'],
            'tanggal' => $r['tanggal'], 'sumber' => 'Panel Harga Badan Pangan Nasional'];
    }

    private static function news(): ?array
    {
        $item = GoogleNews::search('', 3)[0] ?? null;

        return $item ? ['jenis' => 'berita terbaru', 'judul' => $item['judul'], 'tanggal' => $item['tanggal'], 'sumber' => $item['sumber']] : null;
    }

    private static function disaster(): ?array
    {
        $e = collect(BnpbDisaster::recent())->firstWhere('region', 'Empat Lawang');

        return $e ? ['jenis' => 'kejadian bencana', 'tanggal' => $e['date'], 'bencana' => $e['type'], 'kondisi' => mb_strimwidth((string) $e['status'], 0, 150, '…'), 'sumber' => 'BNPB'] : null;
    }

    private static function sppg(): ?array
    {
        $n = SppgUnit::where('is_published', true)->count();

        return $n ? ['jenis' => 'program', 'fakta' => "Ada {$n} dapur SPPG program Makan Bergizi Gratis yang beroperasi di Empat Lawang.", 'sumber' => 'Radar MBG Badan Gizi Nasional'] : null;
    }

    /** Newest rows of a random Portal Data dataset; null when the portal is unreachable. */
    public static function statistic(): ?array
    {
        $catalog = PortalTools::enabled() ? PortalTools::run('portal_katalog', []) : [];
        $datasets = array_merge(...array_values($catalog['topik'] ?? [[]]));
        // skip reference tables (codes) that make no fun fact
        $datasets = array_values(array_filter($datasets, fn ($d) => ! empty($d['dataset']) && ! str_contains(mb_strtolower($d['judul'] ?? ''), 'referensi')));
        for ($try = 0; $try < 2 && $datasets; $try++) {
            $d = $datasets[array_rand($datasets)];
            $rows = PortalTools::run('portal_baris', ['dataset' => $d['dataset'], 'jumlah' => 4]);
            if (! empty($rows['baris'])) {
                return ['jenis' => 'statistik', 'dataset' => $d['judul'] ?? $d['dataset'], 'periode' => $d['periode'] ?? null,
                    'data_terbaru' => $rows['baris'], 'sumber' => 'Portal Data Empat Lawang'];
            }
        }

        return null;
    }
}
