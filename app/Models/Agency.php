<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Perangkat daerah (OPD) of Kabupaten Empat Lawang: profile + contact. Seeded from database/seeders/assets/agencies.json
 * (research in workspace shared/riset-opd-empatlawang); petugas edit it in /admin/instansi.
 * A contact with only one non-government source is stored with verified=false and never shown or given to the AI.
 */
class Agency extends Model
{
    /** Sections on /instansi, in this order. */
    public const CATEGORIES = [
        'sekretariat' => 'Sekretariat',
        'dinas' => 'Dinas',
        'badan' => 'Badan',
        'lainnya' => 'Inspektorat, Satpol PP & RSUD',
        'kecamatan' => 'Kecamatan',
    ];

    public const CONFIDENCE = [
        'high' => 'Kontak terverifikasi',
        'medium' => 'Sebagian terverifikasi',
        'low' => 'Hanya nama',
    ];

    public const SOCIAL = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'x' => 'X (Twitter)'];

    protected $fillable = [
        'name', 'short_name', 'slug', 'category', 'keywords', 'description', 'address', 'address_verified', 'phones', 'emails',
        'website', 'social', 'service_hours', 'head_name', 'official_id', 'latitude', 'longitude', 'sources', 'confidence',
        'checked_at', 'is_published', 'order',
    ];

    protected $casts = [
        'phones' => 'array',
        'emails' => 'array',
        'social' => 'array',
        'sources' => 'array',
        'address_verified' => 'boolean',
        'is_published' => 'boolean',
        'checked_at' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Rows of agencies.json, used by AgencySeeder. Keyed by slug. Refreshes rows the admin never edited
     * (updated_at == created_at, like SppgUnit::import); edited rows are kept. The head is linked to an existing
     * official with the same name (officials are never created here).
     *
     * @return array{created: int, updated: int, kept: int}
     */
    public static function import(array $rows): array
    {
        $count = ['created' => 0, 'updated' => 0, 'kept' => 0];
        $officials = Official::all(['id', 'name']);
        foreach (array_values($rows) as $i => $row) {
            $head = $row['head_name'] ?? null;
            $data = [
                'name' => $row['name'],
                'short_name' => $row['short_name'] ?? null,
                'category' => $row['category'],
                'keywords' => $row['keywords'] ?? null,
                'description' => $row['description'] ?? null,
                'address' => $row['address'] ?? null,
                'address_verified' => (bool) ($row['address_verified'] ?? false),
                'phones' => array_values(array_filter($row['phones'] ?? [], fn ($p) => filled($p['number'] ?? null))),
                'emails' => array_values(array_filter($row['emails'] ?? [], fn ($e) => filled($e['address'] ?? null))),
                'website' => $row['website'] ?? null,
                'social' => array_filter($row['social'] ?? []) ?: null,
                'service_hours' => $row['service_hours'] ?? null,
                'head_name' => $head,
                'official_id' => $head ? $officials->first(fn ($o) => self::sameName($o->name, $head))?->id : null,
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
                'sources' => $row['sources'] ?? [],
                'confidence' => $row['confidence'] ?? null,
                'checked_at' => $row['checked_at'] ?? now()->toDateString(),
                'is_published' => (bool) ($row['is_published'] ?? true),
                'order' => $row['order'] ?? $i + 1,
            ];
            $agency = self::where('slug', $row['slug'])->first();
            if (! $agency) {
                self::create($data + ['slug' => $row['slug']]);
                $count['created']++;
            } elseif ($agency->updated_at->equalTo($agency->created_at)) {
                $agency->timestamps = false; // keep the "never edited" marker
                $agency->fill($data)->save();
                $count['updated']++;
            } else {
                $count['kept']++;
            }
        }

        return $count;
    }

    /** Same person, ignoring titles/degrees and punctuation: "Dery Kurniawan, S.K.M." = "Dery Kurniawan". */
    public static function sameName(?string $a, ?string $b): bool
    {
        $core = fn ($n) => trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z ]/', ' ',
            preg_replace(['/,.*$/u', '/^((h|hj|dr|drs|ir|mgs|kms|plt)\.?\s+)+/i'], '', mb_strtolower(trim((string) $n))))));

        return $core($a) !== '' && $core($a) === $core($b);
    }

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /** Every word must appear in the name, short name, keywords, description, address or head. */
    public function scopeSearch(Builder $query, ?string $text): Builder
    {
        // LIKE wildcards are dropped (no portable ESCAPE between sqlite, Postgres and MySQL)
        $clean = str_replace(['%', '_', '\\'], ' ', mb_strtolower((string) $text));
        foreach (array_slice(preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY), 0, 8) as $word) {
            $query->where(function ($q) use ($word) {
                foreach (['name', 'short_name', 'keywords', 'description', 'address', 'head_name'] as $column) {
                    $q->orWhereRaw("LOWER($column) LIKE ?", ['%' . $word . '%']);
                }
            });
        }

        return $query;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    /** Address for the public / AI: only when verified (official source or 2 matching sources). */
    public function getPublicAddressAttribute(): ?string
    {
        return $this->address_verified ? $this->address : null;
    }

    /** Verified phones only: [{number, label, tel}]. */
    public function getPublicPhonesAttribute(): array
    {
        return collect($this->phones ?? [])->filter(fn ($p) => ($p['verified'] ?? false) && filled($p['number'] ?? null))
            ->map(fn ($p) => ['number' => $p['number'], 'label' => $p['label'] ?? null, 'tel' => preg_replace('/[^0-9+]/', '', $p['number'])])
            ->values()->all();
    }

    /** Verified e-mail addresses only. */
    public function getPublicEmailsAttribute(): array
    {
        return collect($this->emails ?? [])->filter(fn ($e) => ($e['verified'] ?? false) && filled($e['address'] ?? null))
            ->pluck('address')->values()->all();
    }

    /** Head shown to the public: a linked official only while that official is published; otherwise head_name. */
    public function getHeadAttribute(): ?string
    {
        if ($this->official_id) {
            return $this->official?->is_published ? $this->official->name : null;
        }

        return $this->head_name;
    }

    public function getHasPublicContactAttribute(): bool
    {
        return $this->public_address || $this->public_phones || $this->public_emails || $this->website;
    }

    /** Google Maps search link (exact point when the coordinates are known). */
    public function getMapsUrlAttribute(): string
    {
        $name = Str::of($this->name)->replace(' Kabupaten Empat Lawang', '')
            ->when($this->category === 'kecamatan', fn ($s) => $s->replaceFirst('Kecamatan ', 'Kantor Camat '));
        $query = $this->latitude && $this->longitude
            ? $this->latitude . ',' . $this->longitude
            : $name->append(' ', (string) $this->public_address)
                ->unless(fn ($s) => $s->contains('Empat Lawang'), fn ($s) => $s->append(' Empat Lawang'))->squish();

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string) $query);
    }
}
