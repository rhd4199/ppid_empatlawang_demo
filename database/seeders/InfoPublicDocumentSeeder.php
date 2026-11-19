<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Documents for /informasi-publik.
 * Berkala: real PDFs published by Pemkab Empat Lawang (empatlawangkab.go.id/dokumen, Google Drive links),
 * copied into assets/documents/; large ones (> 3 MB) link to the original instead.
 * Other categories: generated one-page PDFs (marked as sample) with general content.
 * Sources: workspace shared/dokumen-empatlawang/README.md.
 */
class InfoPublicDocumentSeeder extends Seeder
{
    /** Earlier placeholder rows (DocumentSeeder before Nov 2026) replaced by this seeder. */
    private const OLD_PATHS = [
        'documents/laporan-keuangan-2024.pdf', 'documents/profil-pimpinan.pdf', 'documents/peringatan-cuaca.pdf',
        'documents/panduan-evakuasi.pdf', 'documents/aset-daerah.pdf', 'documents/mou-2024.pdf', 'documents/sk-dikecualikan.pdf',
    ];

    private const DRIVE = 'https://drive.google.com/file/d/%s/view';

    private const SAMPLE = 'Dokumen contoh untuk demo situs PPID Kabupaten Empat Lawang - bukan dokumen resmi.';

    public function run(): void
    {
        Document::whereIn('file_path', self::OLD_PATHS)->delete();

        $berkala = 'informasi-publik-berkala';
        // [title, description, asset file or Drive id (external), date shown on the official site]
        foreach ([
            ['Laporan Keuangan Pemerintah Daerah (LKPD) Kabupaten Empat Lawang Tahun 2024', 'Laporan keuangan Pemerintah Kabupaten Empat Lawang tahun anggaran 2024.', 'lkpd-2024.pdf', '2025-01-06'],
            ['Opini BPK atas LKPD Kabupaten Empat Lawang Tahun 2024', 'Laporan hasil pemeriksaan dan opini Badan Pemeriksa Keuangan atas LKPD tahun anggaran 2024.', 'opini-bpk-2024.pdf', '2025-05-27'],
            ['Laporan Kinerja Instansi Pemerintah (LKjIP) Kabupaten Empat Lawang Tahun 2024', 'Laporan akuntabilitas kinerja tahunan Pemerintah Kabupaten Empat Lawang tahun 2024.', 'lkjip-2024.pdf', '2025-03-03'],
            ['Peraturan Daerah tentang APBD Tahun Anggaran 2024', 'Peraturan Daerah Kabupaten Empat Lawang tentang Anggaran Pendapatan dan Belanja Daerah TA 2024.', 'perda-apbd-2024.pdf', null],
            ['Peraturan Bupati tentang Penjabaran APBD Tahun Anggaran 2024', 'Peraturan Bupati Empat Lawang tentang penjabaran APBD TA 2024.', 'perbup-penjabaran-apbd-2024.pdf', null],
            ['Keputusan Bupati tentang Penetapan Pejabat Pengelola Keuangan Daerah 2024', 'Keputusan Kepala Daerah tentang penetapan Pejabat Pengelola Keuangan Daerah (PPKD) tahun 2024.', 'sk-ppkd-2024.pdf', '2025-01-07'],
            ['Rencana Umum Pengadaan (RUP) Barang/Jasa Tahun 2024', 'Daftar rencana umum pengadaan barang/jasa Pemerintah Kabupaten Empat Lawang tahun 2024.', 'rup-2024.pdf', '2024-12-27'],
            ['Laporan Keuangan PDAM Tirta Seguring Tahun 2024', 'Laporan keuangan BUMD Perusahaan Daerah Air Minum Tirta Seguring tahun 2024.', 'laporan-keuangan-pdam-tirta-seguring-2024.pdf', '2025-07-11'],
            ['Peraturan Daerah tentang Pertanggungjawaban Pelaksanaan APBD 2024', 'Perda Nomor 1 Tahun 2025 tentang pertanggungjawaban pelaksanaan APBD TA 2024 (berkas asli, 4,4 MB).', ['1KScOnRUbqdXxD91MyoOYAsM5HHvB1G5D'], '2025-08-20'],
            ['Peraturan Bupati tentang Perubahan Penjabaran APBD 2024', 'Perubahan keenam atas Peraturan Bupati tentang penjabaran APBD TA 2024 (berkas asli, 13,9 MB).', ['1MhYLDTmD64wuAzbpBtR9EuKAY8YF_9td'], '2024-12-24'],
            ['Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Empat Lawang Tahun 2025', 'Dokumen RKPD 2025 halaman 239-768 (berkas asli, 6,3 MB).', ['1nPKZdWSFi9dBQd3Ycr8gfHTzfVbyHkWa'], '2024-07-11'],
        ] as [$title, $description, $source, $date]) {
            $this->document($title, $berkala, $description, $date, ...(is_array($source)
                ? [null, sprintf(self::DRIVE, $source[0])]
                : ['documents/' . $source, null]));
        }

        foreach (self::generated() as [$category, $slug, $title, $description, $lines]) {
            $path = "documents/{$slug}.pdf";
            $this->document($title, "informasi-publik-{$category}", $description, null, $path, null);
            Storage::disk('public')->put($path, DocumentSeeder::placeholderPdf($title, $lines, self::SAMPLE));
        }
    }

    private function document(string $title, string $category, string $description, ?string $date, ?string $path, ?string $url): void
    {
        $document = Document::updateOrCreate(['title' => $title, 'category' => $category], [
            'description' => $description, 'file_path' => $path, 'external_url' => $url, 'is_published' => true,
        ]);
        if ($date) {
            $document->forceFill(['created_at' => $date, 'updated_at' => $date])->saveQuietly();
        }

        $asset = __DIR__ . '/assets/' . $path;
        if ($path && is_file($asset) && ! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents($asset));
        }
    }

    /** [category, file slug, title, description, PDF lines] */
    private static function generated(): array
    {
        return [
            ['serta-merta', 'kesiapsiagaan-banjir-longsor', 'Panduan Kesiapsiagaan Banjir dan Tanah Longsor',
                'Langkah sebelum, saat, dan sesudah banjir atau tanah longsor bagi warga Kabupaten Empat Lawang.', [
                    '## Sebelum kejadian',
                    '- Kenali daerah rawan di sekitar tempat tinggal (bantaran sungai, lereng curam).',
                    '- Siapkan tas siaga: dokumen penting, obat-obatan, senter, air minum, dan makanan ringan.',
                    '- Pantau informasi cuaca resmi BMKG dan pengumuman BPBD Kabupaten Empat Lawang.',
                    '## Saat kejadian',
                    '- Segera menuju tempat yang lebih tinggi atau titik kumpul yang ditentukan desa.',
                    '- Matikan listrik dan gas; jangan menyeberangi arus air yang deras.',
                    '- Dahulukan anak-anak, lansia, ibu hamil, dan penyandang disabilitas.',
                    '## Sesudah kejadian',
                    '- Kembali ke rumah hanya setelah dinyatakan aman oleh petugas.',
                    '- Laporkan kerusakan dan korban kepada aparat desa/kelurahan atau BPBD.',
                    '- Waspadai penyakit pascabanjir: jaga kebersihan air minum dan lingkungan.',
                ]],
            ['serta-merta', 'peringatan-dini-cuaca', 'Peringatan Dini Cuaca Ekstrem Musim Hujan',
                'Cara memantau peringatan dini cuaca ekstrem dari BMKG dan tindakan yang dianjurkan.', [
                    'Memasuki musim hujan, wilayah Kabupaten Empat Lawang berpotensi mengalami hujan lebat disertai',
                    'petir dan angin kencang yang dapat memicu banjir, tanah longsor, dan pohon tumbang.',
                    '## Sumber informasi resmi',
                    '- Situs dan aplikasi InfoBMKG (prakiraan cuaca per kecamatan dan peringatan dini).',
                    '- Akun media sosial resmi BMKG dan BPBD Kabupaten Empat Lawang.',
                    '## Tindakan yang dianjurkan',
                    '- Hindari beraktivitas di sungai, tebing, dan di bawah pohon besar saat hujan lebat.',
                    '- Bersihkan saluran air di lingkungan rumah.',
                    '- Sebarkan informasi peringatan dini hanya dari sumber resmi untuk mencegah hoaks.',
                ]],
            ['setiap-saat', 'daftar-informasi-publik-2026', 'Daftar Informasi Publik (DIP) Tahun 2026',
                'Daftar informasi publik yang dikuasai PPID Kabupaten Empat Lawang dan cara memperolehnya.', [
                    'Daftar Informasi Publik (DIP) memuat informasi yang berada di bawah penguasaan Badan Publik,',
                    'sesuai Pasal 7 UU Nomor 14 Tahun 2008 dan Perki Nomor 1 Tahun 2021.',
                    '## Informasi yang wajib disediakan dan diumumkan secara berkala',
                    '- Profil, struktur organisasi, tugas dan fungsi Pemerintah Kabupaten Empat Lawang.',
                    '- Ringkasan program/kegiatan, laporan keuangan, dan laporan kinerja.',
                    '- Ringkasan laporan layanan informasi publik.',
                    '## Informasi yang wajib tersedia setiap saat',
                    '- Daftar Informasi Publik, peraturan dan keputusan, serta prosedur kerja layanan.',
                    '- Rencana strategis, rencana kerja, dan dokumen anggaran.',
                    '- Data perjanjian kerja sama dan pengadaan barang/jasa.',
                    '## Cara memperoleh',
                    '- Diunduh langsung melalui situs PPID, atau diajukan melalui formulir permohonan informasi.',
                ]],
            ['setiap-saat', 'regulasi-keterbukaan-informasi', 'Daftar Regulasi Keterbukaan Informasi Publik',
                'Peraturan yang menjadi dasar layanan informasi publik di Kabupaten Empat Lawang.', [
                    '- UU Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik.',
                    '- UU Nomor 25 Tahun 2009 tentang Pelayanan Publik.',
                    '- UU Nomor 43 Tahun 2009 tentang Kearsipan.',
                    '- PP Nomor 61 Tahun 2010 tentang Pelaksanaan UU Keterbukaan Informasi Publik.',
                    '- Perki Nomor 1 Tahun 2013 tentang Prosedur Penyelesaian Sengketa Informasi Publik.',
                    '- Perki Nomor 1 Tahun 2021 tentang Standar Layanan Informasi Publik.',
                    '- Permendagri Nomor 2 Tahun 2026 tentang Pengelolaan Layanan Informasi Publik.',
                ]],
            ['dikecualikan', 'klasifikasi-informasi-dikecualikan', 'Klasifikasi Informasi yang Dikecualikan',
                'Jenis informasi yang dikecualikan berdasarkan Pasal 17 UU Nomor 14 Tahun 2008, ditetapkan melalui uji konsekuensi.', [
                    'Informasi dikecualikan ditetapkan PPID melalui uji konsekuensi dengan persetujuan Atasan PPID.',
                    '## Berdasarkan Pasal 17 UU Nomor 14 Tahun 2008, informasi yang dapat dikecualikan:',
                    '- Menghambat proses penegakan hukum.',
                    '- Mengganggu perlindungan hak atas kekayaan intelektual dan persaingan usaha tidak sehat.',
                    '- Membahayakan pertahanan dan keamanan negara.',
                    '- Mengungkapkan kekayaan alam Indonesia.',
                    '- Merugikan ketahanan ekonomi nasional.',
                    '- Merugikan kepentingan hubungan luar negeri.',
                    '- Mengungkap isi akta otentik yang bersifat pribadi dan wasiat seseorang.',
                    '- Mengungkap rahasia pribadi.',
                    '- Memorandum atau surat antar/intra Badan Publik yang menurut sifatnya dirahasiakan.',
                    '- Informasi yang tidak boleh diungkapkan berdasarkan undang-undang.',
                ]],
        ];
    }
}
