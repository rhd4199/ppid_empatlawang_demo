<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\InformationRequest;
use Illuminate\Database\Seeder;

/**
 * Data contoh permohonan informasi & keberatan untuk Statistik Layanan Informasi
 * (halaman Laporan > Statistik). Dijalankan terpisah, tidak dipanggil DatabaseSeeder:
 *
 *   php artisan db:seed --class=LayananStatistikSeeder
 *
 * Idempoten: dijalankan berulang tidak menggandakan data (kunci: ticket_number).
 */
class LayananStatistikSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->requests() as $row) {
            InformationRequest::updateOrCreate(
                ['ticket_number' => $row['ticket_number']],
                $row
            );
        }

        foreach ($this->complaints() as $row) {
            Complaint::updateOrCreate(
                ['ticket_number' => $row['ticket_number']],
                $row
            );
        }

        $this->command->info('Seed statistik layanan: '
            . InformationRequest::count() . ' permohonan, '
            . Complaint::count() . ' keberatan.');
    }

    private function requests(): array
    {
        return [
            [
                'ticket_number' => 'REG-2026-001',
                'name' => 'Ahmad Fauzi',
                'nik' => '1611010203900001',
                'address' => 'Jl. Merdeka No. 12, Tebing Tinggi, Kabupaten Empat Lawang',
                'email' => 'ahmad.fauzi.el@gmail.com',
                'phone' => '081377450012',
                'info_requested' => 'Dokumen APBD Kabupaten Empat Lawang Tahun Anggaran 2025 beserta ringkasan penjabarannya.',
                'reason' => 'Bahan kajian transparansi anggaran daerah untuk kegiatan diskusi publik.',
                'delivery_method' => 'email',
                'status' => 'approved',
                'admin_note' => 'Dokumen APBD TA 2025 dikirim melalui email pemohon.',
                'created_at' => '2026-01-12 09:15:00',
                'updated_at' => '2026-01-19 14:05:00',
            ],
            [
                'ticket_number' => 'REG-2026-002',
                'name' => 'Siti Marlina',
                'nik' => '1611024506920002',
                'address' => 'Desa Lubuk Puding, Kecamatan Ulu Musi, Kabupaten Empat Lawang',
                'email' => 'siti.marlina92@gmail.com',
                'phone' => '082181230045',
                'info_requested' => 'Data penerima Bantuan Langsung Tunai Dana Desa (BLT-DD) Kecamatan Ulu Musi tahun 2025.',
                'reason' => 'Verifikasi data penerima bantuan di desa tempat tinggal pemohon.',
                'delivery_method' => 'ambil_langsung',
                'status' => 'approved',
                'admin_note' => 'Data agregat penerima diberikan; NIK dan alamat lengkap dikaburkan sesuai ketentuan data pribadi.',
                'created_at' => '2026-01-20 10:40:00',
                'updated_at' => '2026-01-29 11:20:00',
            ],
            [
                'ticket_number' => 'REG-2026-003',
                'name' => 'Rizky Pratama',
                'nik' => '1611011708950003',
                'address' => 'Jl. Lintas Sumatera KM 4, Tebing Tinggi, Kabupaten Empat Lawang',
                'email' => 'rizky.pratama.jurnalis@gmail.com',
                'phone' => '085366778890',
                'info_requested' => 'Daftar paket pengadaan barang dan jasa Dinas PUPR Kabupaten Empat Lawang tahun 2025 beserta nilai pagunya.',
                'reason' => 'Bahan liputan media lokal mengenai pembangunan infrastruktur daerah.',
                'delivery_method' => 'email',
                'status' => 'approved',
                'admin_note' => 'Daftar paket bersumber dari SiRUP LKPP dikirim via email.',
                'created_at' => '2026-02-03 08:55:00',
                'updated_at' => '2026-02-10 15:30:00',
            ],
            [
                'ticket_number' => 'REG-2026-004',
                'name' => 'Yuliana Sari',
                'nik' => '1611025512930004',
                'address' => 'Desa Muara Pinang Baru, Kecamatan Muara Pinang, Kabupaten Empat Lawang',
                'email' => 'yuliana.sari.el@gmail.com',
                'phone' => '081299887744',
                'info_requested' => 'Laporan Kinerja Instansi Pemerintah (LKjIP) Kabupaten Empat Lawang tahun 2024.',
                'reason' => 'Referensi penyusunan skripsi bidang administrasi publik.',
                'delivery_method' => 'email',
                'status' => 'approved',
                'admin_note' => 'Dokumen LKjIP 2024 dikirim dalam format PDF.',
                'created_at' => '2026-02-17 13:10:00',
                'updated_at' => '2026-02-23 09:45:00',
            ],
            [
                'ticket_number' => 'REG-2026-005',
                'name' => 'Bambang Setiawan',
                'nik' => '1611010109880005',
                'address' => 'Jl. Sudirman No. 8, Tebing Tinggi, Kabupaten Empat Lawang',
                'email' => 'bambang.setiawan88@gmail.com',
                'phone' => '081533221100',
                'info_requested' => 'Salinan dokumen hasil pemeriksaan internal terhadap seorang pejabat di lingkungan Pemerintah Kabupaten Empat Lawang.',
                'reason' => 'Ingin mengetahui hasil pemeriksaan yang sedang berjalan.',
                'delivery_method' => 'email',
                'status' => 'rejected',
                'admin_note' => 'Ditolak: informasi dikecualikan karena proses pemeriksaan masih berlangsung dan pengungkapannya dapat mengganggu proses penegakan hukum (Pasal 17 huruf a UU No. 14 Tahun 2008).',
                'created_at' => '2026-03-02 11:05:00',
                'updated_at' => '2026-03-11 16:00:00',
            ],
            [
                'ticket_number' => 'REG-2026-006',
                'name' => 'Dedi Kurniawan',
                'nik' => '1611012203910006',
                'address' => 'Desa Padang Tepong, Kecamatan Ulu Musi, Kabupaten Empat Lawang',
                'email' => 'dedi.kurniawan.el@gmail.com',
                'phone' => '082377665511',
                'info_requested' => 'Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Empat Lawang tahun 2026.',
                'reason' => 'Bahan masukan forum musyawarah perencanaan pembangunan desa.',
                'delivery_method' => 'ambil_langsung',
                'status' => 'approved',
                'admin_note' => 'Dokumen RKPD 2026 diserahkan langsung di Meja Layanan PPID.',
                'created_at' => '2026-03-16 09:30:00',
                'updated_at' => '2026-03-23 10:15:00',
            ],
            [
                'ticket_number' => 'REG-2026-007',
                'name' => 'Nurhayati',
                'nik' => '1611026408870007',
                'address' => 'Desa Talang Padang, Kecamatan Talang Padang, Kabupaten Empat Lawang',
                'email' => 'nurhayati.tp@gmail.com',
                'phone' => '085288990033',
                'info_requested' => 'Data jumlah guru dan tenaga kependidikan pada satuan pendidikan dasar di Kabupaten Empat Lawang tahun 2025.',
                'reason' => 'Bahan penelitian mengenai pemerataan tenaga pendidik.',
                'delivery_method' => 'email',
                'status' => 'approved',
                'admin_note' => 'Data rekapitulasi dari Dinas Pendidikan dikirim via email.',
                'created_at' => '2026-04-06 14:20:00',
                'updated_at' => '2026-04-13 13:40:00',
            ],
            [
                'ticket_number' => 'REG-2026-008',
                'name' => 'Hendra Gunawan',
                'nik' => '1611011012890008',
                'address' => 'Jl. Veteran No. 21, Tebing Tinggi, Kabupaten Empat Lawang',
                'email' => 'hendra.gunawan.law@gmail.com',
                'phone' => '081266554477',
                'info_requested' => 'Daftar lengkap nama, NIK, dan nomor rekening penerima bantuan sosial Kabupaten Empat Lawang tahun 2025.',
                'reason' => 'Pendataan mandiri oleh lembaga swadaya masyarakat.',
                'delivery_method' => 'email',
                'status' => 'rejected',
                'admin_note' => 'Ditolak sebagian: NIK dan nomor rekening merupakan data pribadi yang dikecualikan (Pasal 17 huruf h UU No. 14 Tahun 2008 jo. UU No. 27 Tahun 2022). Data agregat per kecamatan tersedia dan telah ditawarkan kepada pemohon.',
                'created_at' => '2026-04-21 10:00:00',
                'updated_at' => '2026-04-30 15:10:00',
            ],
            [
                'ticket_number' => 'REG-2026-009',
                'name' => 'Lestari Wulandari',
                'nik' => '1611024703940009',
                'address' => 'Desa Tanjung Raya, Kecamatan Pendopo, Kabupaten Empat Lawang',
                'email' => 'lestari.wulandari94@gmail.com',
                'phone' => '083177442200',
                'info_requested' => 'Laporan Realisasi Anggaran Dinas Kesehatan Kabupaten Empat Lawang semester I tahun 2026.',
                'reason' => 'Pemantauan pelaksanaan program kesehatan masyarakat.',
                'delivery_method' => 'email',
                'status' => 'approved',
                'admin_note' => 'Laporan realisasi semester I dikirim via email.',
                'created_at' => '2026-05-11 08:45:00',
                'updated_at' => '2026-05-18 11:55:00',
            ],
            [
                'ticket_number' => 'REG-2026-010',
                'name' => 'Muhammad Ilham',
                'nik' => '1611010506960010',
                'address' => 'Desa Lingge, Kecamatan Sikap Dalam, Kabupaten Empat Lawang',
                'email' => 'm.ilham.el@gmail.com',
                'phone' => '081399887766',
                'info_requested' => 'Dokumen Rencana Umum Pengadaan (RUP) Kabupaten Empat Lawang tahun anggaran 2026.',
                'reason' => 'Persiapan keikutsertaan usaha kecil dalam pengadaan pemerintah.',
                'delivery_method' => 'email',
                'status' => 'processed',
                'admin_note' => 'Sedang dikoordinasikan dengan Bagian Pengadaan Barang dan Jasa Setda.',
                'created_at' => '2026-08-24 09:05:00',
                'updated_at' => '2026-08-28 10:30:00',
            ],
            [
                'ticket_number' => 'REG-2026-011',
                'name' => 'Rina Anggraini',
                'nik' => '1611025909950011',
                'address' => 'Desa Muara Danau, Kecamatan Saling, Kabupaten Empat Lawang',
                'email' => 'rina.anggraini.el@gmail.com',
                'phone' => '085377001122',
                'info_requested' => 'Standar Operasional Prosedur pelayanan administrasi kependudukan Kabupaten Empat Lawang.',
                'reason' => 'Mengetahui persyaratan dan jangka waktu pengurusan dokumen kependudukan.',
                'delivery_method' => 'email',
                'status' => 'approved',
                'admin_note' => 'SOP Disdukcapil dikirim via email dan tautan unduhan situs PPID.',
                'created_at' => '2026-06-15 13:25:00',
                'updated_at' => '2026-06-19 09:10:00',
            ],
            [
                'ticket_number' => 'REG-2026-012',
                'name' => 'Agus Salim',
                'nik' => '1611011404860012',
                'address' => 'Jl. Kolonel Barlian No. 3, Tebing Tinggi, Kabupaten Empat Lawang',
                'email' => 'agus.salim.el@gmail.com',
                'phone' => '081255443300',
                'info_requested' => 'Data statistik layanan informasi publik PPID Kabupaten Empat Lawang tahun 2025.',
                'reason' => 'Bahan pembanding penyusunan laporan keterbukaan informasi organisasi.',
                'delivery_method' => 'email',
                'status' => 'pending',
                'admin_note' => null,
                'created_at' => '2026-09-01 15:40:00',
                'updated_at' => '2026-09-01 15:40:00',
            ],
        ];
    }

    private function complaints(): array
    {
        return [
            [
                'ticket_number' => 'ADU-2026-001',
                'request_ticket_number' => 'REG-2026-005',
                'name' => 'Bambang Setiawan',
                'email' => 'bambang.setiawan88@gmail.com',
                'phone' => '081533221100',
                'reason_complaint' => 'Keberatan atas penolakan permohonan informasi hasil pemeriksaan internal. Pemohon menilai informasi tersebut bukan termasuk informasi yang dikecualikan.',
                'status' => 'resolved',
                'admin_reply' => 'Keberatan ditolak. Atasan PPID menguatkan keputusan penolakan karena informasi masih dalam proses pemeriksaan sesuai Pasal 17 huruf a UU No. 14 Tahun 2008. Pemohon diberitahu haknya mengajukan sengketa ke Komisi Informasi Provinsi Sumatera Selatan.',
                'created_at' => '2026-03-16 10:20:00',
                'updated_at' => '2026-04-08 14:35:00',
            ],
            [
                'ticket_number' => 'ADU-2026-002',
                'request_ticket_number' => 'REG-2026-008',
                'name' => 'Hendra Gunawan',
                'email' => 'hendra.gunawan.law@gmail.com',
                'phone' => '081266554477',
                'reason_complaint' => 'Keberatan atas penghitaman data penerima bantuan sosial. Pemohon meminta data lengkap untuk keperluan verifikasi lapangan.',
                'status' => 'resolved',
                'admin_reply' => 'Keberatan dikabulkan sebagian. Data agregat penerima per kecamatan beserta nilai bantuan diberikan; NIK dan nomor rekening tetap dikecualikan sebagai data pribadi.',
                'created_at' => '2026-05-06 09:15:00',
                'updated_at' => '2026-05-27 16:05:00',
            ],
            [
                'ticket_number' => 'ADU-2026-003',
                'request_ticket_number' => 'REG-2026-010',
                'name' => 'Muhammad Ilham',
                'email' => 'm.ilham.el@gmail.com',
                'phone' => '081399887766',
                'reason_complaint' => 'Keberatan atas keterlambatan tanggapan permohonan dokumen Rencana Umum Pengadaan yang melebihi 10 hari kerja.',
                'status' => 'processed',
                'admin_reply' => null,
                'created_at' => '2026-09-04 11:00:00',
                'updated_at' => '2026-09-05 08:30:00',
            ],
        ];
    }
}
