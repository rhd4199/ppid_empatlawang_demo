<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gallery;
use App\Models\GalleryItem;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class GallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Clear existing data
        // Disable foreign key checks to allow truncation
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        GalleryItem::truncate();
        Gallery::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create 10 Albums
        for ($i = 0; $i < 10; $i++) {
            $title = $faker->sentence(5);
            $seed = Str::random(5); // Seed for picsum to get consistent random image for this album
            
            // 50% chance of having a cover image
            $hasCover = $faker->boolean(80);
            $coverImage = $hasCover ? "https://picsum.photos/seed/{$seed}_cover/800/600" : null;

            $gallery = Gallery::create([
                'title' => rtrim($title, '.'),
                'description' => $faker->paragraph(),
                'type' => 'photo', // Default to photo for now
                'cover_image' => $coverImage,
                'url' => null, // Only for video type usually
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
            ]);

            // Create 10 Items per Album
            for ($j = 0; $j < 10; $j++) {
                $itemSeed = $seed . '_' . $j;
                GalleryItem::create([
                    'gallery_id' => $gallery->id,
                    'image_path' => "https://picsum.photos/seed/{$itemSeed}/800/600",
                    'caption' => $faker->sentence(3),
                    'created_at' => $gallery->created_at,
                    'updated_at' => $gallery->created_at,
                ]);
            }
        }
    }
}
