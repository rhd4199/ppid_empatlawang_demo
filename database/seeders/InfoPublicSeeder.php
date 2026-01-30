<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InfoPublic;

class InfoPublicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InfoPublic::create([
            'title' => 'Laporan Keuangan 2024',
            'slug' => 'laporan-keuangan-2024',
            'category' => 'berkala',
            'description' => 'Laporan keuangan pemerintah daerah tahun anggaran 2024.',
            'published_date' => '2025-01-15'
        ]);

        InfoPublic::create([
            'title' => 'Peringatan Dini Bencana Banjir',
            'slug' => 'peringatan-dini-banjir',
            'category' => 'serta_merta',
            'description' => 'Informasi mengenai potensi banjir di wilayah Empat Lawang.',
            'published_date' => now()
        ]);

        InfoPublic::create([
            'title' => 'Daftar Aset Daerah',
            'slug' => 'daftar-aset-daerah',
            'category' => 'setiap_saat',
            'description' => 'Daftar inventaris aset milik pemerintah daerah.',
            'published_date' => '2024-12-01'
        ]);
    }
}
