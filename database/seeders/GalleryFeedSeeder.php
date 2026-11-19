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
            if (Gallery::where('title', $title)->exists()) {
                continue;
            }

            $paths = $items->map(fn ($post) => $this->upload($dir . '/' . $post['image']))->values();

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

    private function upload(string $localPath): string
    {
        $path = 'galleries/items/' . Str::random(20) . '.' . pathinfo($localPath, PATHINFO_EXTENSION);
        Storage::disk('public')->put($path, file_get_contents($localPath));

        return $path;
    }
}
