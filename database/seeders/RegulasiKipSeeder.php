<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class RegulasiKipSeeder extends Seeder
{
    /**
     * Seeds the national Keterbukaan Informasi Publik (KIP) regulations
     * (Monev checklist item D5 "Regulasi Keterbukaan Informasi Publik").
     * Links verified directly against peraturan.bpk.go.id (title on the
     * target page matches) as of 2026-09-07 — do not swap in an unverified
     * Details/{id} URL, the ids are not sequential/guessable.
     */
    public function run(): void
    {
        $items = [
            [
                'title' => 'UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik',
                'description' => 'Undang-Undang dasar yang mengatur hak masyarakat memperoleh informasi publik dan kewajiban Badan Publik menyediakan serta melayani permintaan informasi.',
                'url' => 'https://peraturan.bpk.go.id/Details/39047/uu-no-14-tahun-2008',
            ],
            [
                'title' => 'PP No. 61 Tahun 2010 tentang Pelaksanaan UU Keterbukaan Informasi Publik',
                'description' => 'Peraturan pelaksana UU No. 14 Tahun 2008, mengatur teknis klasifikasi informasi, mekanisme permohonan, dan penyelesaian sengketa informasi.',
                'url' => 'https://peraturan.bpk.go.id/Details/5084/pp-no-61-tahun-2010',
            ],
        ];

        foreach ($items as $item) {
            Document::firstOrCreate(
                ['title' => $item['title'], 'category' => 'informasi-publik-setiap-saat'],
                [
                    'description' => $item['description'],
                    'file_path' => null,
                    'external_url' => $item['url'],
                    'is_published' => true,
                ]
            );
        }
    }
}
