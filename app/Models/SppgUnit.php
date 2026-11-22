<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * SPPG (Satuan Pelayanan Pemenuhan Gizi) = a kitchen of the Makan Bergizi Gratis programme.
 * Seeded from BGN Radar MBG (database/seeders/assets/sppg-units.json); petugas edit it in /admin/sppg.
 */
class SppgUnit extends Model
{
    public const STATUSES = [
        'operasional' => 'Operasional',
        'persiapan' => 'Persiapan',
        'nonaktif' => 'Dihentikan sementara',
    ];

    protected $fillable = [
        'bgn_id', 'code', 'name', 'kecamatan', 'village', 'address', 'operator', 'status', 'beneficiaries',
        'institutions', 'latitude', 'longitude', 'notes', 'source_url', 'checked_at', 'is_published',
    ];

    protected $casts = [
        'institutions' => 'array',
        'checked_at' => 'date',
        'is_published' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(fn (SppgUnit $unit) => $unit->institutions_count = count($unit->institutions ?? []));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Every word must appear in the name, village, kecamatan, address, operator or served institutions. */
    public function scopeSearch(Builder $query, ?string $text): Builder
    {
        // LIKE wildcards are dropped (no portable ESCAPE between sqlite, Postgres and MySQL)
        $clean = str_replace(['%', '_', '\\'], ' ', mb_strtolower((string) $text));
        foreach (array_slice(preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY), 0, 8) as $word) {
            $query->where(function ($q) use ($word) {
                foreach (['name', 'village', 'kecamatan', 'address', 'operator', 'institutions'] as $column) {
                    $q->orWhereRaw("LOWER($column) LIKE ?", ['%' . $word . '%']);
                }
            });
        }

        return $query;
    }

    /** Kecamatan names of published units with their counts, A–Z. */
    public static function kecamatanCounts(): Collection
    {
        return static::published()->selectRaw('kecamatan, COUNT(*) AS total')->groupBy('kecamatan')->orderBy('kecamatan')
            ->pluck('total', 'kecamatan')->map(fn ($n) => (int) $n);
    }

    /** Served institutions whose name or village has every word of $text (for "SD 7 dilayani SPPG mana?"). */
    public function servedMatching(?string $text): array
    {
        $words = preg_split('/\s+/', mb_strtolower(trim((string) $text)), -1, PREG_SPLIT_NO_EMPTY);
        if (! $words) {
            return [];
        }

        return array_values(array_filter($this->institutions ?? [], function ($i) use ($words) {
            $hay = mb_strtolower(($i['name'] ?? '') . ' ' . ($i['village'] ?? ''));
            foreach ($words as $word) {
                if (! str_contains($hay, $word)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Sapa Madani answer without AI for "sppg di pendopo ada berapa", "dimana lokasi sppg ulu musi", "dapur mbg".
     * Null when the text is not about SPPG/MBG. $text is lower-case (AssistantFallback/WhatsAppBot).
     */
    public static function reply(string $text): ?string
    {
        if (! preg_match('/\b(sppg|mbg|makan bergizi( gratis)?|makan siang gratis|dapur (mbg|gizi|makan))\b/u', $text)) {
            return null;
        }
        $units = static::published()->orderBy('kecamatan')->orderBy('name')->get();
        $page = route('sppg.index');
        $source = 'Sumber: Radar MBG Badan Gizi Nasional' . ($units->max('checked_at') ? ', dicek ' . $units->max('checked_at')->translatedFormat('j F Y') : '') . '.';
        if ($units->isEmpty()) {
            return "Data SPPG (dapur Makan Bergizi Gratis) di Empat Lawang belum tersedia di situs PPID. Informasi resminya ada di Radar MBG Badan Gizi Nasional (bgn.go.id/radar-mbg).";
        }
        // longest name first so "pendopo barat" wins over "pendopo"
        $kecamatan = $units->pluck('kecamatan')->unique()->sortByDesc(fn ($k) => mb_strlen($k))
            ->first(fn ($k) => preg_match('/\b' . preg_quote(mb_strtolower($k), '/') . '\b/u', $text));
        $line = fn ($u) => "- {$u->name}" . ($u->address ? " — {$u->address}" : '') . ($u->institutions_count ? " ({$u->institutions_count} instansi dilayani)" : '');

        if ($kecamatan) {
            $here = $units->where('kecamatan', $kecamatan);

            return "Di Kecamatan {$kecamatan} ada {$here->count()} SPPG (dapur Makan Bergizi Gratis) yang tercatat:\n"
                . $here->map($line)->implode("\n")
                . "\nDaftar lengkap dan sekolah yang dilayani ada di halaman MBG (" . route('sppg.index', ['kecamatan' => $kecamatan]) . "). {$source}"
                . "\nApakah Bapak/Ibu ingin tahu SPPG di kecamatan lain?";
        }
        $perKecamatan = $units->countBy('kecamatan')->map(fn ($n, $k) => "{$k} {$n}")->implode(', ');
        $statuses = $units->countBy(fn ($u) => mb_strtolower($u->status_label))->map(fn ($n, $s) => "{$n} {$s}")->implode(', ');

        return "Saat ini tercatat {$units->count()} SPPG (dapur Makan Bergizi Gratis) di Kabupaten Empat Lawang ({$statuses}). Per kecamatan: {$perKecamatan}."
            . " Nama, alamat, dan sekolah yang dilayani tiap SPPG ada di halaman MBG ({$page}). {$source}"
            . "\nSPPG di kecamatan mana yang ingin Bapak/Ibu ketahui?";
    }

    /** Data for the Sapa Madani tool data_sppg: summary, short list, page link. Published rows only. */
    public static function forAssistant(?string $kecamatan = null, ?string $keyword = null): array
    {
        $kecamatan = trim((string) $kecamatan);
        $keyword = mb_substr(trim((string) $keyword), 0, 100);
        $all = static::published()->get();
        $rows = static::published()->search($keyword)
            ->when($kecamatan !== '', fn ($q) => $q->whereRaw('LOWER(kecamatan) LIKE ?', ['%' . mb_strtolower(str_replace(['%', '_'], '', $kecamatan)) . '%']))
            ->orderBy('kecamatan')->orderBy('name')->get();

        return [
            'ringkasan' => [
                'total_sppg' => $all->count(),
                'per_status' => $all->countBy(fn ($u) => $u->status_label)->all(),
                'per_kecamatan' => $all->countBy('kecamatan')->sortKeys()->all(),
                'instansi_dilayani' => (int) $all->sum('institutions_count'),
            ],
            'hasil' => $rows->take(30)->map(fn ($u) => array_filter([
                'nama' => $u->name, 'kecamatan' => $u->kecamatan, 'desa' => $u->village, 'alamat' => $u->address,
                'status' => $u->status_label, 'mitra' => $u->operator, 'penerima_manfaat' => $u->beneficiaries,
                'jumlah_instansi_dilayani' => $u->institutions_count,
                'instansi_cocok' => $keyword !== '' ? array_slice(array_column($u->servedMatching($keyword), 'name'), 0, 10) : null,
            ]))->values()->all() ?: 'Tidak ada SPPG yang cocok dengan filter ini.',
            'halaman' => route('sppg.index', array_filter(['kecamatan' => $rows->pluck('kecamatan')->unique()->count() === 1 ? $rows->first()->kecamatan : null])),
            'sumber' => 'Radar MBG Badan Gizi Nasional (bgn.go.id/radar-mbg), dicek ' . ($all->max('checked_at')?->format('d-m-Y') ?? '-'),
            'catatan' => 'Jumlah instansi = sekolah/PAUD/pesantren yang tercatat dilayani, bukan jumlah penerima. SPPG tahap persiapan belum tentu tercatat.',
        ];
    }
}
