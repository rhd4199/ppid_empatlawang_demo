<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\News;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Clear existing data
        News::truncate();

        // Specific Important News 1
        News::create([
            'title' => 'Kunjungan Kerja Bupati ke Kecamatan Tebing Tinggi',
            'slug' => 'kunjungan-kerja-bupati-ke-kecamatan-tebing-tinggi',
            'content' => '<p>Bupati Empat Lawang melakukan kunjungan kerja dalam rangka memantau pembangunan infrastruktur di Kecamatan Tebing Tinggi. Dalam kunjungan ini, Bupati didampingi oleh Kepala Dinas PU dan beberapa pejabat terkait.</p><p>Bupati menekankan pentingnya kualitas pembangunan jalan dan jembatan agar dapat bertahan lama dan memberikan manfaat maksimal bagi masyarakat.</p>',
            'author' => 'Humas',
            'published_at' => now(),
            'is_published' => true
        ]);

        // Specific Important News 2
        News::create([
            'title' => 'Sosialisasi Keterbukaan Informasi Publik Tahun 2026',
            'slug' => 'sosialisasi-keterbukaan-informasi-publik-tahun-2026',
            'content' => '<p>Dinas Kominfo Kabupaten Empat Lawang menggelar sosialisasi Undang-Undang Keterbukaan Informasi Publik (KIP) kepada seluruh Organisasi Perangkat Daerah (OPD).</p><p>Kegiatan ini bertujuan untuk meningkatkan pemahaman dan kepatuhan badan publik terhadap kewajiban menyediakan dan melayani permohonan informasi publik.</p>',
            'author' => 'PPID Utama',
            'published_at' => now()->subDays(2),
            'is_published' => true
        ]);

        // Generate 15 Dummy News
        for ($i = 0; $i < 15; $i++) {
            $title = $faker->sentence(8); // Generates a sentence with around 8 words
            // Remove dot at the end
            $title = rtrim($title, '.');
            
            News::create([
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::random(5),
                'content' => '<p>' . implode('</p><p>', $faker->paragraphs(5)) . '</p>',
                'author' => $faker->randomElement(['Admin PPID', 'Humas Pemkab', 'Diskominfo', 'Redaksi']),
                'published_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'is_published' => true,
                'image' => null // We don't have images yet, let the placeholder handle it
            ]);
        }
    }
}
