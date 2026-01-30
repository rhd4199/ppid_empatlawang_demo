<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Document;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Document::create([
            'title' => 'SOP Pelayanan Informasi Publik',
            'category' => 'standar_layanan',
            'description' => 'Standar Operasional Prosedur pelayanan informasi publik di lingkungan Pemkab Empat Lawang.',
            'file_path' => 'documents/sop-ppid.pdf'
        ]);

        Document::create([
            'title' => 'Laporan Kinerja Instansi Pemerintah 2024',
            'category' => 'laporan_pemda',
            'description' => 'Laporan akuntabilitas kinerja instansi pemerintah tahun 2024.',
            'file_path' => 'documents/lakip-2024.pdf'
        ]);
    }
}
