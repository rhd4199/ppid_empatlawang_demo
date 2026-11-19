<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\GalleryItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryFeedSeeder extends Seeder
{
    /**
     * Seeds one photo album per month from the empatlawangkab.go.id posts in
     * assets/empatlawangkab/posts.json (cover photo + title as caption).
     */
    public function run(): void
    {
        $dir = __DIR__ . '/assets/empatlawangkab';
        $posts = collect(json_decode(file_get_contents($dir . '/posts.json'), true)['posts'])
            ->filter(fn ($post) => $post['image'] && is_file($dir . '/' . $post['image']));

        $months = $posts->groupBy(fn ($post) => Carbon::parse($post['published_at'])->setTimezone('Asia/Jakarta')->format('Y-m'))
            ->sortKeys(); // oldest album first, so the newest gets the highest id

        foreach ($months as $month => $items) {
            $title = 'Kegiatan Pemkab Empat Lawang ' . Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y');
            $paths = $items->map(fn ($post) => $this->upload($dir . '/' . $post['image']))->values();

            if ($existing = Gallery::where('title', $title)->first()) {
                $this->repair($existing, $items->values(), $paths);
                continue;
            }

            $gallery = Gallery::create([
                'title' => $title,
                'description' => 'Dokumentasi foto kegiatan Pemerintah Kabupaten Empat Lawang. Sumber: empatlawangkab.go.id.',
                'type' => 'photo',
                'cover_image' => $paths[0],
                'is_published' => true,
            ]);

            foreach ($items->values() as $i => $post) {
                GalleryItem::create([
                    'gallery_id' => $gallery->id,
                    'image_path' => $paths[$i],
                    'caption' => $post['title'],
                    'order' => $i,
                ]);
            }
        }
    }

    /** Fixed path per asset: re-running only writes files that are missing. */
    private function upload(string $localPath): string
    {
        $path = 'galleries/items/empatlawangkab-' . basename($localPath);
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents($localPath));
        }

        return $path;
    }

    /** Point albums seeded earlier (upload never landed, e.g. old dummy S3) at the local files. */
    private function repair(Gallery $gallery, $items, $paths): void
    {
        $missing = fn (?string $path) => ! $path || ! Storage::disk('public')->exists($path);

        if ($missing($gallery->cover_image)) {
            $gallery->update(['cover_image' => $paths[0]]);
        }

        foreach ($items as $i => $post) {
            $item = $gallery->items()->where('caption', $post['title'])->first();
            if ($item && $missing($item->image_path)) {
                $item->update(['image_path' => $paths[$i]]);
            }
        }
    }
}
