<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\News;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        News::create([
            'title' => 'Kunjungan Kerja Bupati ke Kecamatan Tebing Tinggi',
            'slug' => 'kunjungan-kerja-bupati',
            'content' => '<p>Bupati Empat Lawang melakukan kunjungan kerja dalam rangka memantau pembangunan infrastruktur...</p>',
            'author' => 'Humas',
            'published_at' => now(),
            'is_published' => true
        ]);

        News::create([
            'title' => 'Sosialisasi Keterbukaan Informasi Publik',
            'slug' => 'sosialisasi-kip',
            'content' => '<p>Dinas Kominfo menggelar sosialisasi UU KIP kepada seluruh OPD...</p>',
            'author' => 'PPID Utama',
            'published_at' => now()->subDays(2),
            'is_published' => true
        ]);
    }
}
