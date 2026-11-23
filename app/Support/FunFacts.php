<?php

namespace App\Support;

use App\Models\Agency;
use App\Models\SppgUnit;

/**
 * "Tahukah Anda?" facts about Empat Lawang for short chat openers ("P p info"): fixed facts with a source, plus facts
 * built from our data and the Portal Data when they are reachable. Only sourced facts, never invented.
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

    /** @return array{fakta: string, sumber: string, jumlah_fakta: int} */
    public static function random(): array
    {
        // Owner: a random fact from any of our data, e.g. a statistic. Mostly a random Portal Data dataset (its newest
        // rows, phrased by the AI); otherwise a ready fact from our own tables or the fixed list.
        if (mt_rand(1, 100) <= 70 && $stat = self::statistic()) {
            return $stat;
        }
        $facts = [...self::FIXED, ...self::fromData()];
        [$fact, $source] = $facts[array_rand($facts)];

        return ['fakta' => $fact, 'sumber' => $source];
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
                return [
                    'jenis' => 'statistik',
                    'dataset' => $d['judul'] ?? $d['dataset'],
                    'periode' => $d['periode'] ?? null,
                    'data_terbaru' => $rows['baris'],
                    'sumber' => 'Portal Data Empat Lawang',
                    'petunjuk' => 'Ubah data ini menjadi SATU fakta menarik yang singkat dan benar (sebut angka, tahun, dan wilayahnya persis seperti di data; jangan menambah angka lain).',
                ];
            }
        }

        return null;
    }

    /** Facts from live/local data; each one is skipped when its source is unavailable. */
    private static function fromData(): array
    {
        $facts = [];
        $kec = PortalTools::enabled() ? PortalTools::run('portal_wilayah', ['jenis' => 'kecamatan']) : [];
        if (! empty($kec['kecamatan'])) {
            $rows = $kec['kecamatan'];
            $largest = collect($rows)->sortByDesc('luas_ha')->first();
            $facts[] = ['Empat Lawang punya ' . count($rows) . ' kecamatan dengan ' . array_sum(array_column($rows, 'jumlah_desa')) . ' desa/kelurahan; kecamatan terluas adalah ' . $largest['kecamatan'] . ' (±' . number_format($largest['luas_ha'], 0, ',', '.') . ' ha).', 'Portal Data Empat Lawang'];
        }
        if ($sppg = SppgUnit::where('is_published', true)->count()) {
            $facts[] = ["Saat ini ada {$sppg} dapur SPPG program Makan Bergizi Gratis yang beroperasi di Empat Lawang.", 'Radar MBG Badan Gizi Nasional'];
        }
        if ($opd = Agency::where('is_published', true)->where('category', '!=', 'kecamatan')->count()) {
            $facts[] = ["Pemerintah Kabupaten Empat Lawang punya {$opd} perangkat daerah (dinas, badan, sekretariat dan lainnya) yang melayani warga.", 'direktori perangkat daerah PPID Empat Lawang'];
        }
        if ($chili = FoodPrice::find('cabai rawit')['harga'][0] ?? null) {
            $facts[] = ['Harga ' . mb_strtolower($chili['komoditas']) . ' rata-rata di Empat Lawang per ' . $chili['tanggal'] . ' sekitar Rp' . number_format($chili['harga'], 0, ',', '.') . '/' . $chili['satuan'] . '.', 'Panel Harga Badan Pangan Nasional'];
        }

        return $facts;
    }
}
