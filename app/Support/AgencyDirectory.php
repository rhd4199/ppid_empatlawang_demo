<?php

namespace App\Support;

use App\Models\Agency;
use Illuminate\Support\Collection;

/**
 * Sapa Madani lookups in the agencies table (tools kontak_instansi and daftar_instansi). Published rows only;
 * contacts with a single non-government source are never returned.
 */
class AgencyDirectory
{
    /** Words that say nothing about which agency is meant. */
    private const STOPWORDS = ['kantor', 'kabupaten', 'kab', 'empat', 'lawang', 'pemkab', 'pemerintah', 'daerah', 'alamat', 'kontak',
        'nomor', 'no', 'telepon', 'telp', 'hp', 'wa', 'whatsapp', 'email', 'di', 'dan', 'serta', 'yang', 'mana', 'berapa', 'jam', 'layanan', 'buka', 'ke', 'the', 'opd'];

    /** Common shorthand → words of the official name. */
    private const ALIASES = ['camat' => 'kecamatan', 'kec' => 'kecamatan', 'dukcapil' => 'kependudukan', 'capil' => 'kependudukan',
        'kominfo' => 'komunikasi', 'diskominfo' => 'komunikasi', 'pu' => 'pekerjaan', 'damkar' => 'kebakaran', 'kb' => 'keluarga',
        'pol' => 'polisi', 'rs' => 'rumah', 'rsud' => 'rumah', 'setda' => 'sekretariat', 'setwan' => 'dprd', 'sekwan' => 'dprd',
        'pemda' => 'sekretariat', 'pemadam' => 'kebakaran', 'disdik' => 'pendidikan', 'dinkes' => 'kesehatan', 'dinsos' => 'sosial'];

    /**
     * Best matches for "Disdukcapil", "Kantor Camat Pendopo", "dinas kesehatan". Every meaningful word must match the
     * name, short name, slug or keywords; among equals the shortest name wins ("Pendopo" before "Pendopo Barat").
     */
    public static function find(string $query, int $limit = 3): Collection
    {
        $words = collect(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY))
            ->reject(fn ($w) => in_array($w, self::STOPWORDS, true))->unique()->values();
        if ($words->isEmpty()) {
            return collect();
        }

        return Agency::published()->with('official')->get()
            ->filter(function (Agency $a) use ($words) {
                $hay = mb_strtolower(implode(' ', [$a->name, $a->short_name, str_replace('-', ' ', $a->slug), $a->keywords]));
                $tokens = preg_split('/[^\p{L}\p{N}]+/u', $hay, -1, PREG_SPLIT_NO_EMPTY);

                return $words->every(fn ($w) => in_array($w, $tokens, true)
                    || (mb_strlen($w) >= 4 && str_contains($hay, $w))
                    || (isset(self::ALIASES[$w]) && in_array(self::ALIASES[$w], $tokens, true)));
            })
            ->sortBy(fn (Agency $a) => mb_strlen($a->name))->take($limit)->values();
    }

    /** Tool kontak_instansi: data from the table, or null when no agency matches (caller falls back to the web). */
    public static function contact(string $query): ?array
    {
        $found = self::find(mb_substr(trim($query), 0, 100));
        if ($found->isEmpty()) {
            return null;
        }

        return [
            'hasil' => $found->map(fn (Agency $a) => self::card($a))->all(),
            'sumber' => 'Direktori Perangkat Daerah situs PPID (dicek ' . ($found->max('checked_at')?->format('d-m-Y') ?? '-') . ')',
            'catatan' => 'Hanya kontak yang terverifikasi yang dicantumkan. Bila telepon/email tidak ada, katakan belum tersedia di data PPID, '
                . 'sarankan datang langsung atau cari lewat tautan google_maps; jangan menebak nomor. Sertakan tautan halaman profilnya.',
        ];
    }

    /** Tool daftar_instansi: names per category with their profile links. */
    public static function list(?string $category = null): array
    {
        $rows = Agency::published()->when(isset(Agency::CATEGORIES[$category ?? '']), fn ($q) => $q->where('category', $category))
            ->ordered()->get(['name', 'short_name', 'slug', 'category']);

        return [
            'instansi' => collect(Agency::CATEGORIES)->mapWithKeys(fn ($label, $key) => [$label => $rows->where('category', $key)
                ->map(fn ($a) => array_filter(['nama' => $a->name, 'singkatan' => $a->short_name, 'halaman' => route('agencies.show', $a->slug)]))->values()->all()])
                ->filter()->all() ?: 'Belum ada data perangkat daerah.',
            'jumlah' => $rows->count(),
            'halaman' => route('agencies.index'),
            'catatan' => 'Untuk alamat/telepon satu instansi pakai kontak_instansi.',
        ];
    }

    private static function card(Agency $a): array
    {
        return array_filter([
            'nama' => $a->name,
            'singkatan' => $a->short_name,
            'kategori' => $a->category_label,
            'tugas' => $a->description,
            'kepala' => $a->head,
            'alamat' => $a->public_address,
            'telepon' => array_map(fn ($p) => $p['number'] . ($p['label'] ? " ({$p['label']})" : ''), $a->public_phones),
            'email' => $a->public_emails,
            'situs_web' => $a->website,
            'media_sosial' => $a->social,
            'jam_layanan' => $a->service_hours,
            'google_maps' => $a->maps_url,
            'halaman' => route('agencies.show', $a->slug),
        ]);
    }
}
