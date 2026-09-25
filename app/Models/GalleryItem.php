<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $fillable = ['gallery_id', 'image_path', 'caption', 'order'];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return storage_url($this->image_path);
    }
}
