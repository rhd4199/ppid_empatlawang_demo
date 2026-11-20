<?php

namespace Database\Seeders;

/**
 * "Informasi Pengadaan" tab of /pengadaan (Document category pengadaan_info), seeded by ProcurementSeeder.
 *
 * Sources checked 2026-11-20 (research: workspace shared/riset-pengadaan-empatlawang/raw/):
 * - tender counts: spse.inaproc.id/empatlawangkab/lelang data (dt-lelang-2025.json, dt-lelang-2026.json);
 * - announcements: spse.inaproc.id/empatlawangkab/pengumuman/<id> (pengumuman-<id>.html);
 * - rup-2024.pdf: "Daftar Rencana Umum Pengadaan Barang/Jasa 2024" from the PPID site (27-12-2024).
 * The 2019 LPSE server-migration notice is left out: outdated.
 */
class ProcurementInfo
{
    public static function items(): array
    {
        $spse = 'https://spse.inaproc.id/empatlawangkab';

        return [
            [
                'title' => 'Daftar Tender/Seleksi Kabupaten Empat Lawang Tahun Anggaran 2026',
                'description' => 'Sampai 20 November 2026 tercatat 10 paket tender/seleksi TA 2026 di SPSE Empat Lawang, semuanya sudah selesai, antara lain Jl. Lingkar Kota (pagu Rp15,7 M) dan Gedung CathLab - DAK (Rp2,2 M). Daftar lengkap dan status terbaru di LPSE.',
                'url' => $spse . '/lelang',
            ],
            [
                'title' => 'Daftar Tender/Seleksi Kabupaten Empat Lawang Tahun Anggaran 2025',
                'description' => '82 paket tender/seleksi TA 2025: 72 selesai, 6 tender gagal, 2 seleksi gagal, 2 batal. Sebagian besar pekerjaan konstruksi jalan, irigasi, jembatan dan rehabilitasi ruang kelas.',
                'url' => $spse . '/lelang',
            ],
            [
                'title' => 'Rencana Umum Pengadaan (RUP) Barang/Jasa Tahun Anggaran 2024',
                'description' => 'Daftar Rencana Umum Pengadaan barang/jasa Pemerintah Kabupaten Empat Lawang tahun 2024 (penyedia dan swakelola) per perangkat daerah.',
                'url' => 'https://sirup.inaproc.id/sirup/caripaketctr/index',
                'file' => 'rup-2024.pdf',
            ],
            [
                'title' => 'SiRUP — Sistem Informasi Rencana Umum Pengadaan',
                'description' => 'Cari rencana paket pengadaan tahun berjalan: pilih tahun anggaran dan K/L/PD "Kab. Empat Lawang". RUP diumumkan di SiRUP sebelum proses pemilihan penyedia.',
                'url' => 'https://sirup.inaproc.id/sirup/caripaketctr/index',
            ],
            [
                'title' => 'LPSE Kabupaten Empat Lawang (SPSE)',
                'description' => 'Layanan Pengadaan Secara Elektronik Kabupaten Empat Lawang: pengumuman tender/seleksi, pendaftaran penyedia, jadwal dan hasil pemilihan.',
                'url' => $spse,
            ],
            [
                'title' => 'Katalog Elektronik (e-Katalog) INAPROC',
                'description' => 'Belanja produk barang/jasa pemerintah secara e-purchasing melalui Katalog Elektronik versi 6 di INAPROC.',
                'url' => 'https://katalog.inaproc.id/',
            ],
            [
                'title' => 'Pendaftaran Akun INAPROC Non Penyedia (PPK, PP, Bendahara)',
                'description' => 'Pengumuman LPSE 13 Maret 2025: PPK, Pejabat Pengadaan dan Bendahara wajib mendaftar di akun.inaproc.id, lalu mengirim formulir terlampir ke kantor LPSE untuk verifikasi akses.',
                'url' => $spse . '/pengumuman/10001386000',
                'file' => 'form-pembuatan-akun-inaproc.doc',
            ],
            [
                'title' => 'Persyaratan Pendaftaran Penyedia dan Penggantian Email Penyedia',
                'description' => 'Formulir dan persyaratan bagi pelaku usaha yang mendaftar sebagai penyedia atau mengganti email/NPWP akun SPSE (berkas scan PDF).',
                'url' => $spse . '/pengumuman/1138585',
                'file' => 'permohonan-ganti-email-dan-pendaftaran-penyedia.pdf',
            ],
            [
                'title' => 'Persyaratan Pendaftaran Penyedia Katalog Elektronik',
                'description' => 'Tahapan bagi pelaku usaha: punya akun SPSE aktif (ADP), mengisi data kualifikasi di SIKaP (sikap.lkpp.go.id), lalu mendaftar pada etalase produk Katalog Elektronik.',
                'url' => $spse . '/pengumuman/1139585',
            ],
        ];
    }
}
