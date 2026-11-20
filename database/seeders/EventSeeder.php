<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Real 2026 activities of Pemkab Empat Lawang, each confirmed by two sources
 * (empatlawangkab.go.id, klikempatlawang.id, koransn.com, infonews.web.id).
 * Research notes: workspace shared/riset-event-empatlawang/README.md.
 *
 * Times are wall-clock WIB as the /event page prints them; 08:00 where the sources give no time.
 * Upsert by slug, so production events entered by admins are never touched.
 */
class EventSeeder extends Seeder
{
    // Descriptions of the old fictitious demo events, removed on re-seed.
    private const DEMO_DESCRIPTIONS = [
        'Rapat koordinasi rutin seluruh kepala dinas dan OPD.',
        'Sosialisasi mengenai pentingnya keterbukaan informasi publik bagi masyarakat desa.',
        'Upacara bendera dalam rangka peringatan hari besar nasional.',
        'Pelatihan teknis pengelolaan website dan media sosial bagi operator OPD.',
        'Penyambutan kunjungan kerja Gubernur Sumatera Selatan dalam rangka peresmian infrastruktur.',
        'Pagelaran seni dan budaya asli daerah Empat Lawang.',
        'Musyawarah Perencanaan Pembangunan tingkat Kabupaten Empat Lawang.',
    ];

    public function run(): void
    {
        Event::whereIn('description', self::DEMO_DESCRIPTIONS)->delete();

        foreach ($this->events() as [$start, $title, $location, $description]) {
            $start = Carbon::parse($start);

            Event::updateOrCreate(
                ['slug' => Str::slug($start->format('Y-m-d') . ' ' . $title)],
                [
                    'title' => $title,
                    'location' => $location,
                    'description' => $description,
                    'start_date' => $start,
                    'end_date' => null,
                ]
            );
        }
    }

    private function events(): array
    {
        return [
            ['2026-02-13 08:00', 'Penyerahan Penghargaan Kabupaten Informatif dari Komisi Informasi Sumsel', 'Griya Agung, Palembang',
                'Kabupaten Empat Lawang menerima predikat Kabupaten Informatif dari Komisi Informasi Provinsi Sumatera Selatan. Penghargaan diserahkan langsung oleh Gubernur Sumatera Selatan, Herman Deru.'],
            ['2026-02-15 08:00', 'Pembukaan MTQ ke-XIX Tingkat Kabupaten Empat Lawang', 'Lapangan Sepak Bola Talang Jawa, Tebing Tinggi',
                'Bupati Empat Lawang, H. Joncik Muhammad, membuka Musabaqah Tilawatil Qur\'an (MTQ) ke-XIX tingkat Kabupaten Empat Lawang Tahun 2026.'],
            ['2026-03-12 08:00', 'Apel Gelar Pasukan Operasi Ketupat 2026', 'Lapangan Pemerintah Kabupaten Empat Lawang',
                'Apel gelar pasukan pengamanan arus mudik dan arus balik Idulfitri bersama TNI, Polri dan instansi terkait, dipimpin langsung oleh Bupati Empat Lawang.'],
            ['2026-04-14 08:00', 'Musrenbang RKPD Provinsi Sumatera Selatan Tahun 2027', 'Griya Agung, Palembang',
                'Bupati Empat Lawang menghadiri Musyawarah Perencanaan Pembangunan RKPD Provinsi Sumatera Selatan Tahun 2027 yang dibuka Gubernur Sumatera Selatan, untuk menyelaraskan program prioritas daerah dengan provinsi.'],
            ['2026-04-20 08:00', 'Rapat Paripurna DPRD Hari Jadi ke-19 Kabupaten Empat Lawang', 'Gedung Serba Guna Pemkab Empat Lawang',
                'DPRD Kabupaten Empat Lawang menggelar Rapat Paripurna peringatan Hari Jadi ke-19 Kabupaten Empat Lawang, dihadiri Gubernur Sumatera Selatan.'],
            ['2026-08-17 08:00', 'Upacara HUT ke-81 Kemerdekaan Republik Indonesia', 'Lapangan Kantor Bupati Empat Lawang',
                'Upacara peringatan HUT ke-81 Kemerdekaan RI tingkat Kabupaten Empat Lawang dengan Bupati Empat Lawang, H. Joncik Muhammad, sebagai inspektur upacara.'],
            ['2026-10-22 08:00', 'Upacara Hari Santri Nasional 2026', 'Lapangan Kantor Kementerian Agama Kabupaten Empat Lawang',
                'Upacara peringatan Hari Santri Nasional 2026 dengan Sekretaris Daerah, Fauzan Khoiri Denin, sebagai inspektur upacara.'],
            ['2026-10-22 08:00', 'Rakornas Tim Percepatan Akses Keuangan Daerah (TPAKD) 2026', 'Grand Ballroom JIExpo Kemayoran, Jakarta',
                'Bupati Empat Lawang menghadiri Rapat Koordinasi Nasional TPAKD 2026 untuk mendorong perluasan akses pembiayaan UMKM Empat Lawang.'],
            ['2026-10-28 09:00', 'Peluncuran Gerakan Ananda Bersinar (Bejeghum P4GN Bersinar)', 'Pendopoan Rumah Dinas Bupati Empat Lawang',
                'Pemkab Empat Lawang bersama TP-PKK dan BNNK Empat Lawang meluncurkan Gerakan Ananda Bersinar untuk pencegahan narkoba sejak dini. Desa Kemang Manis, Kecamatan Tebing Tinggi, ditetapkan sebagai desa pelopor.'],
            ['2026-11-10 07:45', 'Upacara Peringatan Hari Pahlawan 2026', 'Lapangan Pemerintah Kabupaten Empat Lawang',
                'Upacara peringatan Hari Pahlawan Tahun 2026 dengan Bupati Empat Lawang, H. Joncik Muhammad, sebagai inspektur upacara.'],
            ['2026-11-11 08:00', 'Audiensi Pembentukan ASLINMAS bersama Kejati dan DPMD Sumsel', 'Lingkungan Pemkab Empat Lawang',
                'Bupati Empat Lawang menerima audiensi Asisten Intelijen Kejati Sumsel dan Kepala DPMD Provinsi Sumsel tentang pembentukan Asosiasi Satuan Perlindungan Masyarakat (ASLINMAS) hingga tingkat desa dan kelurahan.'],
            ['2026-11-12 08:00', 'Pelantikan Pengurus PWI Kabupaten Empat Lawang 2026–2029', 'Pendopoan Rumah Dinas Bupati Empat Lawang',
                'Pelantikan pengurus Persatuan Wartawan Indonesia (PWI) Kabupaten Empat Lawang periode 2026–2029, dihadiri Bupati, Wakil Bupati, Ketua DPRD dan Forkopimda Empat Lawang.'],
            ['2026-11-16 08:00', 'Pelantikan MD KAHMI Kabupaten Empat Lawang 2026–2031', 'Pendopoan Rumah Dinas Bupati Empat Lawang',
                'Pelantikan pengurus Majelis Daerah Korps Alumni HMI (MD KAHMI) Kabupaten Empat Lawang periode 2026–2031 oleh Ketua MW KAHMI Sumsel, H. Joncik Muhammad.'],
        ];
    }
}
