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
        'source_url',
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

    protected $appends = ['cover_image_url', 'youtube_id'];

    /** Stored cover (disk path or absolute URL); a video album without one uses the YouTube thumbnail. */
    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            return Str::startsWith($this->cover_image, ['http://', 'https://']) ? $this->cover_image : storage_url($this->cover_image);
        }

        return $this->youtube_id ? "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg" : null;
    }

    public function getYoutubeIdAttribute(): ?string
    {
        return $this->type === 'video' ? self::youtubeId($this->url) : null;
    }

    /** 11-char video id from watch?v=, youtu.be/, embed/, shorts/ or live/ links; null when not a YouTube video link. */
    public static function youtubeId(?string $url): ?string
    {
        $pattern = '~^https?://(?:www\.|m\.)?(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/))([\w-]{11})(?![\w-])~i';

        return preg_match($pattern, trim((string) $url), $m) ? $m[1] : null;
    }
}
