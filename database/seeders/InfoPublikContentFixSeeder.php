<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class InfoPublikContentFixSeeder extends Seeder
{
    /**
     * Some "Informasi Publik" checklist items don't need their own file —
     * the content already lives on a dedicated page elsewhere on the site
     * (Kontak, Profil), or is better shown as a written section than a PDF
     * download. This points/fills those in instead of leaving dead
     * placeholder documents (draft, no file, no content).
     */
    public function run(): void
    {
        // Duplicates an existing page — link out instead of requiring a file.
        $linkOuts = [
            'Alamat Kantor Badan Publik' => '/kontak',
            'Tugas Pokok dan Fungsi Badan Publik' => '/profil/tugas-fungsi',
            'Struktur Organisasi Pemerintahan Kabupaten Empat Lawang' => '/profil/struktur-organisasi',
        ];

        foreach ($linkOuts as $title => $url) {
            Document::where('title', $title)->update([
                'external_url' => $url,
                'is_published' => true,
            ]);
        }

        // No dedicated page needed — the description itself (rich text) is the content.
        Document::where('title', 'Peringatan Dini Cuaca Ekstrem')->update([
            'file_path' => null,
            'description' => '<p>Badan Penanggulangan Bencana Daerah (BPBD) Kabupaten Empat Lawang menyampaikan informasi peringatan dini cuaca ekstrem dan potensi bencana kepada masyarakat melalui kanal berikut:</p>'
                . '<ul>'
                . '<li>Siaran resmi BMKG Wilayah II Palembang untuk prakiraan cuaca dan peringatan dini cuaca ekstrem Sumatera Selatan.</li>'
                . '<li>Media sosial resmi Pemkab Empat Lawang dan BPBD Empat Lawang.</li>'
                . '<li>Pengumuman melalui perangkat desa/kelurahan dan camat setempat saat status siaga ditetapkan.</li>'
                . '</ul>'
                . '<p>Masyarakat yang berada di wilayah rawan bencana (longsor, banjir, angin kencang) diimbau untuk selalu memantau kanal-kanal tersebut, terutama pada musim penghujan.</p>',
            'is_published' => true,
        ]);

        Document::where('title', 'Panduan Evakuasi Bencana')->update([
            'file_path' => null,
            'description' => '<p>Prosedur evakuasi mandiri saat terjadi bencana alam di wilayah Kabupaten Empat Lawang:</p>'
                . '<ol>'
                . '<li><strong>Tetap tenang</strong> dan segera hentikan aktivitas, ikuti arahan petugas/perangkat desa setempat.</li>'
                . '<li><strong>Matikan aliran listrik dan gas</strong> di rumah sebelum meninggalkan lokasi, bila situasi memungkinkan.</li>'
                . '<li><strong>Bawa dokumen penting dan kebutuhan darurat</strong> (obat-obatan, air minum, makanan ringan) dalam tas siaga bencana.</li>'
                . '<li><strong>Menuju titik kumpul/jalur evakuasi</strong> yang telah ditentukan oleh BPBD, hindari daerah rawan longsor/banjir susulan.</li>'
                . '<li><strong>Laporkan kondisi</strong> ke posko BPBD Kabupaten Empat Lawang atau melalui aparat desa/kelurahan setempat.</li>'
                . '<li>Ikuti instruksi resmi untuk kembali ke rumah — jangan kembali sebelum dinyatakan aman oleh petugas berwenang.</li>'
                . '</ol>'
                . '<p>Untuk kondisi darurat, masyarakat dapat menghubungi kanal kontak resmi PPID/BPBD Kabupaten Empat Lawang yang tercantum pada halaman Kontak.</p>',
            'is_published' => true,
        ]);
    }
}
