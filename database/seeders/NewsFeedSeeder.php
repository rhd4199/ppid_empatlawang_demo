<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsFeedSeeder extends Seeder
{
    /**
     * Seeds the latest posts of empatlawangkab.go.id (Diskominfo Empat Lawang),
     * scraped into assets/empatlawangkab/posts.json with their cover photos.
     * Refresh the data with the workspace skill "empatlawang-news-scrape".
     */
    public function run(): void
    {
        $dir = __DIR__ . '/assets/empatlawangkab';
        $posts = json_decode(file_get_contents($dir . '/posts.json'), true)['posts'];

        foreach (array_reverse($posts) as $post) {
            $slug = Str::slug($post['title']);
            if (News::where('slug', $slug)->exists()) {
                continue;
            }

            $image = null;
            if ($post['image'] && is_file($dir . '/' . $post['image'])) {
                $image = 'news/' . Str::random(20) . '.' . pathinfo($post['image'], PATHINFO_EXTENSION);
                Storage::disk('public')->put($image, file_get_contents($dir . '/' . $post['image']));
            }

            // In the app timezone: public pages hide posts with published_at > now().
            $publishedAt = Carbon::parse($post['published_at'])->setTimezone(config('app.timezone'))->toDateTimeString();

            News::create([
                'title' => $post['title'],
                'slug' => $slug,
                'content' => $post['content'],
                'image' => $image,
                'author' => $post['author'],
                'published_at' => $publishedAt,
                'created_at' => $publishedAt, // admin & API lists sort by created_at
                'is_published' => true,
                'is_headline' => false,
            ]);
        }
    }
}
