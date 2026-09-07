<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;

class NewsSeeder extends Seeder
{
    /**
     * Real curated news only — no truncate (would wipe NewsExternalSeeder's
     * data too) and no Faker-generated lorem-ipsum filler.
     */
    public function run(): void
    {
        News::firstOrCreate(['slug' => 'kunjungan-kerja-bupati-ke-kecamatan-tebing-tinggi'], [
            'title' => 'Kunjungan Kerja Bupati ke Kecamatan Tebing Tinggi',
            'content' => '<p>Bupati Empat Lawang melakukan kunjungan kerja dalam rangka memantau pembangunan infrastruktur di Kecamatan Tebing Tinggi. Dalam kunjungan ini, Bupati didampingi oleh Kepala Dinas PU dan beberapa pejabat terkait.</p><p>Bupati menekankan pentingnya kualitas pembangunan jalan dan jembatan agar dapat bertahan lama dan memberikan manfaat maksimal bagi masyarakat.</p>',
            'author' => 'Humas',
            'published_at' => now(),
            'is_published' => true,
        ]);

        News::firstOrCreate(['slug' => 'sosialisasi-keterbukaan-informasi-publik-tahun-2026'], [
            'title' => 'Sosialisasi Keterbukaan Informasi Publik Tahun 2026',
            'content' => '<p>Dinas Kominfo Kabupaten Empat Lawang menggelar sosialisasi Undang-Undang Keterbukaan Informasi Publik (KIP) kepada seluruh Organisasi Perangkat Daerah (OPD).</p><p>Kegiatan ini bertujuan untuk meningkatkan pemahaman dan kepatuhan badan publik terhadap kewajiban menyediakan dan melayani permohonan informasi publik.</p>',
            'author' => 'PPID Utama',
            'published_at' => now()->subDays(2),
            'is_published' => true,
        ]);
    }
}
