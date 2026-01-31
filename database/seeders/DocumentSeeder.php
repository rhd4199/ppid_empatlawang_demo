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
            'file_path' => 'documents/sop-ppid.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Alur
        Document::create([
            'title' => 'Alur Permohonan Informasi',
            'category' => 'standar_layanan_alur',
            'description' => 'Bagan alur proses permohonan informasi publik.',
            'file_path' => 'documents/alur-permohonan.pdf',
            'is_published' => true
        ]);

        Document::create([
            'title' => 'Alur Pengajuan Keberatan',
            'category' => 'standar_layanan_alur',
            'description' => 'Bagan alur proses pengajuan keberatan informasi publik.',
            'file_path' => 'documents/alur-keberatan.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Tata Cara
        Document::create([
            'title' => 'Tata Cara Permohonan Informasi',
            'category' => 'standar_layanan_tata_cara',
            'description' => 'Panduan lengkap cara mengajukan permohonan informasi.',
            'file_path' => 'documents/tata-cara-permohonan.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Sengketa
        Document::create([
            'title' => 'Tata Cara Penyelesaian Sengketa',
            'category' => 'standar_layanan_sengketa',
            'description' => 'Prosedur penyelesaian sengketa informasi di Komisi Informasi.',
            'file_path' => 'documents/sengketa.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Maklumat
        Document::create([
            'title' => 'Maklumat Pelayanan PPID',
            'category' => 'standar_layanan_maklumat',
            'description' => 'Pernyataan kesanggupan pelayanan PPID.',
            'file_path' => 'documents/maklumat.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Biaya
        Document::create([
            'title' => 'Standar Biaya Perolehan Informasi',
            'category' => 'standar_layanan_biaya',
            'description' => 'Informasi mengenai biaya penyalinan dokumen (jika ada).',
            'file_path' => 'documents/biaya.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Permohonan (Formulir Offline)
        Document::create([
            'title' => 'Formulir Permohonan Informasi (Offline)',
            'category' => 'standar_layanan_permohonan',
            'description' => 'Unduh formulir ini jika ingin mengajukan permohonan secara langsung.',
            'file_path' => 'documents/form-permohonan.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Keberatan (Formulir Offline)
        Document::create([
            'title' => 'Formulir Pengajuan Keberatan (Offline)',
            'category' => 'standar_layanan_keberatan',
            'description' => 'Unduh formulir ini jika ingin mengajukan keberatan secara langsung.',
            'file_path' => 'documents/form-keberatan.pdf',
            'is_published' => true
        ]);

        // Laporan
        Document::create([
            'title' => 'Laporan Kinerja Instansi Pemerintah 2024',
            'category' => 'laporan_pemda',
            'description' => 'Laporan akuntabilitas kinerja instansi pemerintah tahun 2024.',
            'file_path' => 'documents/lakip-2024.pdf',
            'is_published' => true
        ]);

        Document::create([
            'title' => 'Laporan Tahunan PPID 2024',
            'category' => 'laporan_ppid',
            'description' => 'Laporan pelaksanaan layanan informasi publik tahun 2024.',
            'file_path' => 'documents/laporan-ppid-2024.pdf',
            'is_published' => true
        ]);

        // Informasi Publik - Berkala
        Document::create([
            'title' => 'Ringkasan Laporan Keuangan 2024',
            'category' => 'informasi-publik-berkala',
            'description' => 'Ringkasan laporan keuangan pemerintah daerah tahun anggaran 2024.',
            'file_path' => 'documents/laporan-keuangan-2024.pdf',
            'is_published' => true
        ]);
        
        Document::create([
            'title' => 'Profil Pimpinan Daerah',
            'category' => 'informasi-publik-berkala',
            'description' => 'Profil lengkap Bupati dan Wakil Bupati.',
            'file_path' => 'documents/profil-pimpinan.pdf',
            'is_published' => true
        ]);

        // Informasi Publik - Serta Merta
        Document::create([
            'title' => 'Peringatan Dini Cuaca Ekstrem',
            'category' => 'informasi-publik-serta-merta',
            'description' => 'Informasi mengenai potensi cuaca ekstrem dan banjir.',
            'file_path' => 'documents/peringatan-cuaca.pdf',
            'is_published' => true
        ]);

        Document::create([
            'title' => 'Panduan Evakuasi Bencana',
            'category' => 'informasi-publik-serta-merta',
            'description' => 'Prosedur evakuasi mandiri saat terjadi bencana alam.',
            'file_path' => 'documents/panduan-evakuasi.pdf',
            'is_published' => true
        ]);

        // Informasi Publik - Setiap Saat
        Document::create([
            'title' => 'Daftar Aset Daerah 2024',
            'category' => 'informasi-publik-setiap-saat',
            'description' => 'Daftar inventaris barang milik daerah.',
            'file_path' => 'documents/aset-daerah.pdf',
            'is_published' => true
        ]);

        Document::create([
            'title' => 'Dokumen Perjanjian Kerja Sama',
            'category' => 'informasi-publik-setiap-saat',
            'description' => 'Salinan dokumen perjanjian kerja sama dengan pihak ketiga.',
            'file_path' => 'documents/mou-2024.pdf',
            'is_published' => true
        ]);

        // Informasi Publik - Dikecualikan
        Document::create([
            'title' => 'Daftar Informasi Dikecualikan 2024',
            'category' => 'informasi-publik-dikecualikan',
            'description' => 'Keputusan PPID tentang daftar informasi yang dikecualikan.',
            'file_path' => 'documents/sk-dikecualikan.pdf',
            'is_published' => true
        ]);
    }
}
