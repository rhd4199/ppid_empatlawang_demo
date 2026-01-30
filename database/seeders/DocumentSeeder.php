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
        // Standar Layanan - SOP
        Document::create([
            'title' => 'SOP Pelayanan Informasi Publik',
            'category' => 'standar_layanan_sop',
            'description' => 'Standar Operasional Prosedur pelayanan informasi publik.',
            'file_path' => 'documents/sop-ppid.pdf'
        ]);

        // Standar Layanan - Alur
        Document::create([
            'title' => 'Alur Permohonan Informasi',
            'category' => 'standar_layanan_alur',
            'description' => 'Bagan alur proses permohonan informasi publik.',
            'file_path' => 'documents/alur-permohonan.pdf'
        ]);

        Document::create([
            'title' => 'Alur Pengajuan Keberatan',
            'category' => 'standar_layanan_alur',
            'description' => 'Bagan alur proses pengajuan keberatan informasi publik.',
            'file_path' => 'documents/alur-keberatan.pdf'
        ]);

        // Standar Layanan - Tata Cara
        Document::create([
            'title' => 'Tata Cara Permohonan Informasi',
            'category' => 'standar_layanan_tata_cara',
            'description' => 'Panduan lengkap cara mengajukan permohonan informasi.',
            'file_path' => 'documents/tata-cara-permohonan.pdf'
        ]);

        // Standar Layanan - Sengketa
        Document::create([
            'title' => 'Tata Cara Penyelesaian Sengketa',
            'category' => 'standar_layanan_sengketa',
            'description' => 'Prosedur penyelesaian sengketa informasi di Komisi Informasi.',
            'file_path' => 'documents/sengketa.pdf'
        ]);

        // Standar Layanan - Maklumat
        Document::create([
            'title' => 'Maklumat Pelayanan PPID',
            'category' => 'standar_layanan_maklumat',
            'description' => 'Pernyataan kesanggupan pelayanan PPID.',
            'file_path' => 'documents/maklumat.pdf'
        ]);

        // Standar Layanan - Biaya
        Document::create([
            'title' => 'Standar Biaya Perolehan Informasi',
            'category' => 'standar_layanan_biaya',
            'description' => 'Informasi mengenai biaya penyalinan dokumen (jika ada).',
            'file_path' => 'documents/biaya.pdf'
        ]);

        // Laporan
        Document::create([
            'title' => 'Laporan Kinerja Instansi Pemerintah 2024',
            'category' => 'laporan_pemda',
            'description' => 'Laporan akuntabilitas kinerja instansi pemerintah tahun 2024.',
            'file_path' => 'documents/lakip-2024.pdf'
        ]);

        Document::create([
            'title' => 'Laporan Tahunan PPID 2024',
            'category' => 'laporan_ppid',
            'description' => 'Laporan pelaksanaan layanan informasi publik tahun 2024.',
            'file_path' => 'documents/laporan-ppid-2024.pdf'
        ]);
    }
}
