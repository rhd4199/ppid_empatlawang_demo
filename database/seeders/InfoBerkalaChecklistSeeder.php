<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Official;
use Illuminate\Database\Seeder;

class InfoBerkalaChecklistSeeder extends Seeder
{
    /**
     * Seeds LHKPN placeholder documents per pejabat for the Monev checklist
     * item "Informasi Berkala" (7e). Draft (unpublished, no file) until admin
     * uploads the real LHKPN document via the WYSIWYG edit page.
     *
     * Other checklist items under this section (alamat kantor, tupoksi,
     * struktur organisasi, profil pimpinan) already have a dedicated page
     * elsewhere on the site (Kontak, Profil, Pejabat) — those are NOT
     * duplicated here as documents, see MONEV-LINKS.md for the direct links.
     */
    public function run(): void
    {
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
