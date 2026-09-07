<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Official;
use Illuminate\Database\Seeder;

class InfoBerkalaChecklistSeeder extends Seeder
{
    /**
     * Seeds editable placeholder documents for Monev checklist items under
     * "Informasi Berkala" that aren't covered yet (LHKPN per pejabat, alamat
     * kantor, tupoksi, struktur org Kab/Kota). Draft (unpublished, no file)
     * until admin uploads the real document via the new WYSIWYG edit page.
     */
    public function run(): void
    {
        $placeholders = [
            [
                'title' => 'Alamat Kantor Badan Publik',
                'description' => 'Alamat lengkap kantor Pemerintah Kabupaten Empat Lawang dan PPID.',
            ],
            [
                'title' => 'Tugas Pokok dan Fungsi Badan Publik',
                'description' => 'Uraian tugas pokok dan fungsi Pemerintah Kabupaten Empat Lawang.',
            ],
            [
                'title' => 'Struktur Organisasi Pemerintahan Kabupaten Empat Lawang',
                'description' => 'Bagan struktur organisasi Pemerintah Kabupaten Empat Lawang (bukan struktur PPID).',
            ],
        ];

        foreach ($placeholders as $item) {
            Document::firstOrCreate(
                ['title' => $item['title'], 'category' => 'informasi-publik-berkala'],
                [
                    'description' => $item['description'],
                    'file_path' => null,
                    'is_published' => false,
                ]
            );
        }

        // LHKPN per pejabat (satu dokumen per Official yang sudah ada)
        foreach (Official::all() as $official) {
            Document::firstOrCreate(
                [
                    'title' => "LHKPN {$official->position} - {$official->name}",
                    'category' => 'informasi-publik-berkala',
                ],
                [
                    'description' => "Laporan Harta Kekayaan Pejabat Negara (LHKPN) atas nama {$official->name} ({$official->position}), sebagaimana telah diperiksa dan diverifikasi KPK.",
                    'file_path' => null,
                    'is_published' => false,
                ]
            );
        }
    }
}
