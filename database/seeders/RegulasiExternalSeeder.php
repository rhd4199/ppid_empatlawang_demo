<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class RegulasiExternalSeeder extends Seeder
{
    /**
     * Seeds real "Peraturan, Keputusan, dan Kebijakan Badan Publik" documents
     * (Monev checklist item 10) from empatlawangkab.go.id/dokumen (as of
     * 2026-09-07). Files are hosted on Google Drive by the source site, so
     * these use external_url (link-out) instead of a re-uploaded file.
     */
    public function run(): void
    {
        $items = [
            [
                'title' => 'Peraturan Daerah tentang Pertanggungjawaban Pelaksanaan APBD 2024',
                'date' => '2025-08-20',
                'url' => 'https://drive.google.com/file/d/1KScOnRUbqdXxD91MyoOYAsM5HHvB1G5D/view?usp=drive_link',
            ],
            [
                'title' => 'Laporan Keuangan BUMD/Perusahaan Daerah 2024',
                'date' => '2025-07-11',
                'url' => 'https://drive.google.com/file/d/1OK8eoAXCPnNvTt46ayg8W7C7dl1CCSrR/view?usp=drive_link',
            ],
            [
                'title' => 'Opini Badan Pemeriksa Keuangan 2024',
                'date' => '2025-05-27',
                'url' => 'https://drive.google.com/file/d/1Dl6G5LCipe_-eIVlZHlXXBOVWmZEeSwN/view?usp=drive_link',
            ],
            [
                'title' => 'Laporan Akuntabilitas Kinerja Tahunan Pemerintah Daerah (LAKIP) 2024',
                'date' => '2025-03-03',
                'url' => 'https://drive.google.com/file/d/1d_V_va2y4uQwIzbwzGo7Mb5ZzZCLqT72/view?usp=drive_link',
            ],
            [
                'title' => 'Keputusan Kepala Daerah tentang Penetapan Pejabat Pengelola Keuangan Daerah 2024',
                'date' => '2025-01-07',
                'url' => 'https://drive.google.com/file/d/1PdlcGP1p53Zxe7P-4u9a3iFSK2vQ0CAI/view?usp=drive_link',
            ],
            [
                'title' => 'Laporan Keuangan Pemerintah Daerah (LKPD) 2024',
                'date' => '2025-01-06',
                'url' => 'https://drive.google.com/file/d/17byQxoWWBbMQP9NALa1EcWr3w0YKYjJp/view?usp=drive_link',
            ],
            [
                'title' => 'Daftar Rencana Umum Pengadaan Barang/Jasa 2024',
                'date' => '2024-12-27',
                'url' => 'https://drive.google.com/file/d/1hEMsQRXJmUDsjMboK-Zc3Ff6QPRLK0-E/view?usp=drive_link',
            ],
            [
                'title' => 'Peraturan Daerah tentang Perubahan APBD 2024',
                'date' => '2024-12-24',
                'url' => 'https://drive.google.com/file/d/1MhYLDTmD64wuAzbpBtR9EuKAY8YF_9td/view?usp=drive_link',
            ],
            [
                'title' => 'Informasi Penetapan Perda Pertanggungjawaban Pelaksanaan APBD',
                'date' => '2024-08-28',
                'url' => 'https://drive.google.com/file/d/1tlFjvpU0XzsizFhNmuX2bg7bXqaLWIr5/view?usp=sharing',
            ],
            [
                'title' => 'Daftar Tindak Lanjut Dukungan Pemerintah Daerah atas Kebijakan Prioritas Nasional 2024',
                'date' => '2024-07-11',
                'url' => 'https://drive.google.com/file/d/1nPKZdWSFi9dBQd3Ycr8gfHTzfVbyHkWa/view?usp=drive_link',
            ],
        ];

        foreach ($items as $item) {
            Document::firstOrCreate(
                ['title' => $item['title'], 'category' => 'informasi-publik-setiap-saat'],
                [
                    'description' => 'Dokumen resmi Pemerintah Kabupaten Empat Lawang, diterbitkan ' . \Carbon\Carbon::parse($item['date'])->translatedFormat('d F Y') . '.',
                    'file_path' => null,
                    'external_url' => $item['url'],
                    'is_published' => true,
                    'created_at' => $item['date'],
                    'updated_at' => $item['date'],
                ]
            );
        }
    }
}
