<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceRequirement extends Model
{
    public const CATEGORIES = [
        'kependudukan' => 'Kependudukan',
        'perizinan_usaha' => 'Perizinan Usaha',
        'kendaraan' => 'Kendaraan',
        'pertanahan' => 'Pertanahan',
        'kepegawaian' => 'Kepegawaian',
        'kesehatan_sosial' => 'Kesehatan & Sosial',
        'pendidikan' => 'Pendidikan',
        'lainnya' => 'Lainnya',
    ];

    // Suggestions only (datalist); agency stays free text.
    public const AGENCIES = [
        'Disdukcapil', 'DPMPTSP', 'Satpas Polres', 'Samsat', 'BPN', 'BKPSDM',
        'Dinas Kesehatan', 'Dinas Sosial', 'Dinas Pendidikan', 'Kementerian Agama',
    ];

    protected $fillable = [
        'title', 'slug', 'category', 'agency', 'requirements', 'steps', 'cost', 'duration',
        'legal_basis', 'source_url', 'checked_at', 'keywords', 'is_published',
    ];

    protected $casts = [
        'checked_at' => 'date',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Slug is set once so shared links keep working after a title edit.
        static::saving(function (ServiceRequirement $item) {
            if ($item->slug) {
                return;
            }
            $base = Str::slug($item->title) ?: 'layanan';
            $slug = $base;
            for ($i = 2; static::where('slug', $slug)->where('id', '!=', $item->id)->exists(); $i++) {
                $slug = $base . '-' . $i;
            }
            $item->slug = $slug;
        });
    }

    /** Every word must appear in the title, keywords/synonyms or agency ("ktp hilang" finds "KTP-el Hilang"). */
    public function scopeSearch($query, ?string $text)
    {
        // LIKE wildcards are dropped (no portable ESCAPE between sqlite and Postgres)
        $clean = str_replace(['%', '_', '\\'], ' ', mb_strtolower((string) $text));
        $words = array_slice(preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY), 0, 8);
        foreach ($words as $word) {
            $like = '%' . $word . '%';
            $query->where(fn ($w) => $w->whereRaw('LOWER(title) LIKE ?', [$like])
                ->orWhereRaw('LOWER(keywords) LIKE ?', [$like])
                ->orWhereRaw('LOWER(agency) LIKE ?', [$like]));
        }

        return $query;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
