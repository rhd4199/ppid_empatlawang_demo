<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProcurementSeeder extends Seeder
{
    /**
     * Seeds the "Regulasi/Aturan" tab of /pengadaan (Document category
     * pengadaan_regulasi). The legacy `procurements` table is unused.
     *
     * Every link was checked against peraturan.bpk.go.id on 2026-11-20 (page
     * title matches, status "Berlaku"); Peraturan LKPP 14/2018 is left out
     * because it is "Tidak Berlaku". Research: workspace
     * shared/riset-pengadaan-empatlawang/raw/bpk/<id>.html.
     * PDFs in database/seeders/assets/procurement/ are uploaded when present.
     * firstOrCreate per title → re-running never duplicates or overwrites
     * edits made in the admin panel.
     */
    public function run(): void
    {
        $items = [
            [
                'title' => 'Peraturan Presiden Nomor 16 Tahun 2018 tentang Pengadaan Barang/Jasa Pemerintah',
                'description' => 'Peraturan pokok pengadaan barang/jasa pemerintah yang dibiayai APBN/APBD: pelaku pengadaan, perencanaan, cara pengadaan (swakelola dan penyedia), hingga pengawasan. Telah diubah dengan Perpres 12/2021 dan Perpres 46/2025.',
                'url' => 'https://peraturan.bpk.go.id/Details/73586/perpres-no-16-tahun-2018',
            ],
            [
                'title' => 'Peraturan Presiden Nomor 12 Tahun 2021 tentang Perubahan atas Perpres Nomor 16 Tahun 2018',
                'description' => 'Perubahan pertama Perpres 16/2018, antara lain penguatan peran usaha mikro, kecil dan koperasi serta produk dalam negeri dalam pengadaan pemerintah.',
                'url' => 'https://peraturan.bpk.go.id/Details/161828/perpres-no-12-tahun-2021',
            ],
            [
                'title' => 'Peraturan Presiden Nomor 46 Tahun 2025 tentang Perubahan Kedua atas Perpres Nomor 16 Tahun 2018',
                'description' => 'Perubahan kedua Perpres 16/2018 tentang Pengadaan Barang/Jasa Pemerintah, berlaku sejak 30 April 2025.',
                'url' => 'https://peraturan.bpk.go.id/Details/318647/perpres-no-46-tahun-2025',
            ],
            [
                'title' => 'Peraturan LKPP Nomor 3 Tahun 2021 tentang Pedoman Swakelola',
                'description' => 'Pedoman pelaksanaan pengadaan barang/jasa secara swakelola: tipe swakelola, perencanaan, persiapan, pelaksanaan dan serah terima hasil pekerjaan.',
                'url' => 'https://peraturan.bpk.go.id/Details/169553/peraturan-lkpp-no-3-tahun-2021',
                'file' => 'peraturan-lkpp-3-2021.pdf',
            ],
            [
                'title' => 'Peraturan LKPP Nomor 12 Tahun 2021 tentang Pedoman Pelaksanaan Pengadaan Barang/Jasa Pemerintah Melalui Penyedia',
                'description' => 'Pedoman teknis pengadaan melalui penyedia: perencanaan, persiapan, pemilihan penyedia (tender, seleksi, e-purchasing, pengadaan langsung, penunjukan langsung) dan pelaksanaan kontrak.',
                'url' => 'https://peraturan.bpk.go.id/Details/169565/peraturan-lkpp-no-12-tahun-2021',
            ],
            [
                'title' => 'Peraturan LKPP Nomor 4 Tahun 2024 tentang Perubahan atas Peraturan LKPP Nomor 12 Tahun 2021',
                'description' => 'Perubahan atas Pedoman Pelaksanaan Pengadaan Barang/Jasa Pemerintah Melalui Penyedia (Peraturan LKPP 12/2021).',
                'url' => 'https://peraturan.bpk.go.id/Details/314291/peraturan-lkpp-no-4-tahun-2024',
            ],
            [
                'title' => 'Peraturan Bupati Empat Lawang Nomor 22 Tahun 2017 tentang Pedoman Pengadaan Barang/Jasa pada BLUD RSUD Tebing Tinggi',
                'description' => 'Pedoman pengadaan barang/jasa pada Badan Layanan Umum Daerah Rumah Sakit Umum Daerah Tebing Tinggi Kabupaten Empat Lawang.',
                'url' => 'https://peraturan.bpk.go.id/Details/35212/perbup-kab-empat-lawang-no-22-tahun-2017',
                'file' => 'perbup-empatlawang-22-2017.pdf',
            ],
            [
                'title' => 'Peraturan Bupati Empat Lawang Nomor 15 Tahun 2020 tentang Tata Cara Pengadaan Barang/Jasa di Desa',
                'description' => 'Tata cara pengadaan barang/jasa di desa yang dibiayai APBDes di lingkungan Pemerintah Kabupaten Empat Lawang, berlaku sejak 4 Mei 2020.',
                'url' => 'https://peraturan.bpk.go.id/Details/146970/perbup-kab-empat-lawang-no-15-tahun-2020',
                'file' => 'perbup-empatlawang-15-2020.pdf',
            ],
        ];

        $this->store($items, 'pengadaan_regulasi');
        $this->store(ProcurementInfo::items(), 'pengadaan_info');
    }

    /** firstOrCreate per title; created_at steps back so `latest()` keeps the list order. */
    private function store(array $items, string $category): void
    {
        foreach (array_values($items) as $i => $item) {
            $path = isset($item['file']) ? $this->upload($item['file']) : null;

            Document::firstOrCreate(
                ['title' => $item['title'], 'category' => $category],
                [
                    'description' => $item['description'],
                    'file_path' => $path,
                    'external_url' => $item['url'],
                    'is_published' => true,
                    'created_at' => now()->subMinutes($i),
                ]
            );
        }
    }

    /** Copies a bundled PDF to the public disk once; null when the asset is missing. */
    private function upload(string $file): ?string
    {
        $source = database_path('seeders/assets/procurement/' . $file);
        if (! is_file($source)) {
            return null;
        }

        $path = 'documents/pengadaan/' . $file;
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents($source));
        }

        return $path;
    }
}
