<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    protected $guarded = [];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return storage_url($this->image);
    }

    /** "judul-<unix time>", suffixed -2, -3… when the same title arrives within one second (API pushes). */
    public static function newSlug(string $title): string
    {
        $base = Str::slug($title) . '-' . time();
        $slug = $base;
        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
