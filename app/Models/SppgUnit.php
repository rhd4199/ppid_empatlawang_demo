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
