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

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
