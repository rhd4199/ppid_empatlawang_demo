<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Gallery extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'cover_image',
        'url',
        'is_published',
    ];

    protected static function booted(): void
    {
        // Slug is set once so shared links keep working after a title edit.
        static::saving(function (Gallery $gallery) {
            if ($gallery->slug) {
                return;
            }
            $base = Str::slug($gallery->title) ?: 'album';
            $slug = $base;
            for ($i = 2; static::where('slug', $slug)->where('id', '!=', $gallery->id)->exists(); $i++) {
                $slug = $base . '-' . $i;
            }
            $gallery->slug = $slug;
        });
    }

    public function items()
    {
        return $this->hasMany(GalleryItem::class);
    }
}
