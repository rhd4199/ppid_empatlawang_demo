<?php

// Disdukcapil services. Requirements copied from Ditjen Dukcapil Kemendagri "Syarat dan Prosedur Layanan
// Pendaftaran Penduduk / Pencatatan Sipil" (2023), cross-checked with Disdukcapil Tanjab Timur's list
// (same national template) and the regulation texts. Research: workspace shared/riset-persyaratan/kependudukan/README.md.

$list = fn (array $items) => '<ol><li>' . implode('</li><li>', $items) . '</li></ol>';

$agency = 'Disdukcapil Kabupaten Empat Lawang';
$daftar = 'https://dukcapil.kemendagri.go.id/files/2023/1680205930Syarat%20dan%20Prosedur%20Layanan%20Pendaftaran%20Penduduk.pdf';
$capil = 'https://dukcapil.kemendagri.go.id/files/2023/1680205946Syarat%20dan%20Prosedur%20Layanan%20Pencatatan%20Sipil.pdf';
$free = 'Gratis (UU 24/2013 Pasal 79A: pengurusan dan penerbitan Dokumen Kependudukan tidak dipungut biaya)';
$fast = '1 jam, paling lama 24 jam sejak berkas dinyatakan lengkap (Permendagri 19/2018 Pasal 3)';
$noLimit = 'Tidak ada batas jam khusus dalam Permendagri 19/2018; tanyakan perkiraan waktu ke loket Disdukcapil';
$base = 'UU 23/2006 jo. UU 24/2013 tentang Administrasi Kependudukan; Perpres 96/2018; Permendagri 108/2019';
$verify = 'Petugas memverifikasi berkas dan data di SIAK.';

return [
    'agency' => $agency,
    'checked_at' => '2026-11-21',
    'items' => [
    [
        'title' => 'Ganti Alamat KTP-el (Pindah dalam Kabupaten Empat Lawang)',
        'slug' => 'ganti-alamat-ktp-pindah-dalam-kabupaten',
        'requirements' => $list([
            'Fotokopi Kartu Keluarga (KK).',
            'Surat pernyataan tidak keberatan dari pemilik rumah, bila menumpang KK, menyewa, kontrak atau kos.',
            'KTP-el dan/atau KIA lama untuk diganti dengan alamat baru.',
        ]),
        'steps' => $list([
            'Datang ke Disdukcapil dan isi formulir pindah (F-1.03).',
            'Serahkan fotokopi KK dan surat pernyataan (bila ada).',
            $verify,
            'Disdukcapil menerbitkan KK dengan alamat baru, lalu menarik dan mengganti KTP-el/KIA dengan alamat baru.',
            'Pindah dalam satu kabupaten tidak memerlukan SKPWNI.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => $base . ' (Pasal 25 ayat (3) Perpres 96/2018).',
        'source_url' => $daftar,
        'keywords' => 'ganti alamat, ubah alamat, pindah rumah, pindah domisili, pindah desa, pindah kecamatan, alamat ktp',
    ],
    [
        'title' => 'Pindah Keluar Kabupaten (Surat Keterangan Pindah / SKPWNI)',
        'slug' => 'pindah-keluar-skpwni',
        'requirements' => $list([
            'Fotokopi Kartu Keluarga (KK).',
            'Formulir pindah (F-1.03) yang diisi di Disdukcapil daerah asal.',
        ]),
        'steps' => $list([
            'Datang ke Disdukcapil Empat Lawang (daerah asal) dan isi formulir pindah (F-1.03).',
            'Serahkan fotokopi KK. Tidak perlu surat pengantar RT/RW.',
            $verify,
            'Disdukcapil menerbitkan SKPWNI; KK diterbitkan ulang untuk anggota keluarga yang tidak ikut pindah.',
            'KTP-el/KIA tidak ditarik di daerah asal; ditukar di Disdukcapil daerah tujuan.',
            'SKPWNI berlaku 100 hari kerja; laporkan kedatangan ke Disdukcapil daerah tujuan sebelum habis.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => $base . ' (Pasal 25 Perpres 96/2018; Pasal 30 Permendagri 108/2019 tentang masa berlaku SKP).',
        'source_url' => $daftar,
        'keywords' => 'skpwni, skp, surat pindah, pindah keluar, pindah luar kota, pindah provinsi, mutasi penduduk',
    ],
    [
        'title' => 'Pindah Datang ke Empat Lawang dari Kabupaten/Kota Lain',
        'slug' => 'pindah-datang',
        'requirements' => $list([
            'SKPWNI dari Disdukcapil daerah asal.',
            'KTP-el dan/atau KIA alamat lama untuk diganti.',
            'Surat pernyataan tidak keberatan dari pemilik rumah, bila menumpang KK, menyewa, kontrak atau kos.',
        ]),
        'steps' => $list([
            'Datang ke Disdukcapil Empat Lawang dan serahkan SKPWNI.',
            'Serahkan KTP-el/KIA lama dan surat pernyataan (bila ada).',
            $verify,
            'Disdukcapil menerbitkan KK, KTP-el dan/atau KIA dengan alamat baru, lalu memusnahkan KTP-el/KIA lama.',
            'Sudah tinggal di Empat Lawang tapi belum punya SKPWNI: Disdukcapil tujuan dapat membantu meminta SKPWNI ke daerah asal secara elektronik (isi F-1.03, lampirkan fotokopi KK).',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => $base . ' (Pasal 31 Permendagri 108/2019).',
        'source_url' => $daftar,
        'keywords' => 'pindah datang, pindah masuk, pendatang, warga baru, skpwni, surat pindah',
    ],
    [
        'title' => 'KTP-el Baru (Pertama Kali)',
        'slug' => 'ktp-el-baru',
        'requirements' => $list([
            'Telah berusia 17 tahun, sudah kawin, atau pernah kawin.',
            'Fotokopi Kartu Keluarga (KK).',
        ]),
        'steps' => $list([
            'Datang ke Disdukcapil dan isi formulir F-1.02.',
            'Serahkan fotokopi KK.',
            'Lakukan perekaman data KTP-el bila belum pernah merekam.',
            'Disdukcapil menerbitkan KTP-el baru.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => $base . ' (Pasal 15 Perpres 96/2018).',
        'source_url' => $daftar,
        'keywords' => 'ktp, e-ktp, ektp, ktp el, buat ktp, ktp pertama, perekaman ktp, umur 17',
    ],
    [
        'title' => 'KTP-el Hilang, Rusak atau Perubahan Data',
        'slug' => 'ktp-el-hilang-rusak',
        'requirements' => $list([
            'KTP-el hilang: surat keterangan kehilangan dari kepolisian.',
            'KTP-el rusak: KTP-el yang rusak.',
            'Perubahan data: KTP-el lama dan fotokopi bukti perubahan (mis. akta, buku nikah, ijazah).',
            'Karena pindah datang antar kabupaten/kota/provinsi: SKPWNI.',
        ]),
        'steps' => $list([
            'Datang ke Disdukcapil dan isi formulir F-1.02.',
            'Serahkan surat kehilangan / KTP-el rusak / bukti perubahan sesuai kondisi.',
            $verify,
            'Disdukcapil menerbitkan KTP-el baru; KTP-el lama ditarik dan dimusnahkan.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => $base . ' (Pasal 15 Perpres 96/2018).',
        'source_url' => $daftar,
        'keywords' => 'ktp hilang, ktp rusak, ganti ktp, cetak ulang ktp, ktp patah, ktp salah data, perbaikan ktp, e-ktp hilang',
    ],
    [
        'title' => 'Kartu Keluarga Baru (Menikah, Pisah KK, Ganti Kepala Keluarga)',
        'slug' => 'kk-baru',
        'requirements' => $list([
            'Membentuk keluarga baru: fotokopi buku nikah/kutipan akta perkawinan atau akta perceraian; bila belum tercatat, SPTJM perkawinan/perceraian belum tercatat (F-1.05).',
            'Ganti kepala keluarga: fotokopi akta kematian (bila kepala keluarga meninggal) dan fotokopi KK lama.',
            'Pisah KK dalam satu alamat: fotokopi KK lama; berumur minimal 17 tahun atau sudah/pernah kawin (dibuktikan dengan KTP-el).',
        ]),
        'steps' => $list([
            'Datang ke Disdukcapil dan isi formulir F-1.02.',
            'Serahkan dokumen sesuai alasan penerbitan KK.',
            $verify,
            'Disdukcapil menerbitkan KK baru.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => $base . ' (Pasal 11 Perpres 96/2018; Pasal 10 Permendagri 108/2019).',
        'source_url' => $daftar,
        'keywords' => 'kk, kartu keluarga, kk baru, buat kk, pecah kk, pisah kk, kk menikah, kepala keluarga',
    ],
    [
        'title' => 'Perubahan Data Kartu Keluarga',
        'slug' => 'kk-perubahan-data',
        'requirements' => $list([
            'KK lama.',
            'Fotokopi bukti perubahan Peristiwa Kependudukan atau Peristiwa Penting (mis. akta kelahiran, akta kematian, buku nikah, ijazah, SKPWNI).',
        ]),
        'steps' => $list([
            'Datang ke Disdukcapil, isi formulir F-1.02 dan formulir perubahan elemen data (F-1.06).',
            'Serahkan KK lama dan fotokopi bukti perubahan.',
            $verify,
            'Disdukcapil menerbitkan KK baru dengan data yang sudah diubah.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => $base . ' (Pasal 12 Perpres 96/2018; Pasal 15 Permendagri 108/2019).',
        'source_url' => $daftar,
        'keywords' => 'ubah kk, perbaikan kk, tambah anggota kk, tambah anak kk, salah nama kk, ganti pekerjaan, ganti pendidikan, status kawin',
    ],
    [
        'title' => 'Kartu Keluarga Hilang atau Rusak',
        'slug' => 'kk-hilang-rusak',
        'requirements' => $list([
            'Surat keterangan kehilangan dari kepolisian (KK hilang) atau KK yang rusak.',
            'Fotokopi KTP-el.',
        ]),
        'steps' => $list([
            'Datang ke Disdukcapil dan isi formulir F-1.02.',
            'Serahkan surat kehilangan atau KK yang rusak.',
            'Disdukcapil menerbitkan KK pengganti.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => $base . ' (Pasal 13 Perpres 96/2018).',
        'source_url' => $daftar,
        'keywords' => 'kk hilang, kk rusak, cetak ulang kk, kartu keluarga hilang, kk sobek',
    ],
    [
        'title' => 'Akta Kelahiran',
        'slug' => 'akta-kelahiran',
        'requirements' => $list([
            'Fotokopi surat keterangan kelahiran dari rumah sakit/puskesmas/fasilitas kesehatan/dokter/bidan, atau dari kepala desa/lurah bila lahir di rumah atau tempat lain (asli hanya diperlihatkan).',
            'Fotokopi buku nikah/kutipan akta perkawinan orang tua atau bukti lain yang sah.',
            'Fotokopi KK tempat anak terdaftar atau akan didaftarkan.',
            'Tidak punya surat keterangan kelahiran: SPTJM kebenaran data kelahiran (F-2.03) dengan 2 saksi.',
            'Tidak punya buku nikah: SPTJM kebenaran sebagai pasangan suami istri (F-2.04) dengan 2 saksi.',
            'Anak yang tidak diketahui asal-usul/orang tuanya: berita acara dari kepolisian.',
        ]),
        'steps' => $list([
            'Laporkan kelahiran paling lambat 60 hari sejak lahir.',
            'Datang ke Disdukcapil dan isi formulir F-2.01 (fotokopi KTP-el saksi tidak perlu).',
            'Serahkan berkas persyaratan.',
            $verify,
            'Disdukcapil menerbitkan Kutipan Akta Kelahiran.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => 'UU 23/2006 jo. UU 24/2013 (Pasal 27: lapor paling lambat 60 hari); Perpres 96/2018; Permendagri 108/2019.',
        'source_url' => $capil,
        'keywords' => 'akta lahir, akte kelahiran, akte lahir, akta anak, bayi baru lahir, surat lahir',
    ],
    [
        'title' => 'Akta Kematian',
        'slug' => 'akta-kematian',
        'requirements' => $list([
            'Fotokopi surat kematian dari dokter atau kepala desa/lurah (asli hanya diperlihatkan); atau surat keterangan kepolisian bila identitas tidak jelas; atau salinan penetapan pengadilan bila hilang/jenazah tidak ditemukan.',
            'Fotokopi KK/KTP-el yang meninggal dunia.',
        ]),
        'steps' => $list([
            'Laporkan kematian paling lambat 30 hari sejak tanggal kematian. Pelapor boleh anak, ahli waris, keluarga lain, termasuk ketua RT.',
            'Datang ke Disdukcapil dan isi formulir F-2.01 (fotokopi KTP-el saksi tidak perlu).',
            'Serahkan berkas persyaratan.',
            'Disdukcapil menerbitkan Kutipan Akta Kematian.',
            'Perbarui KK keluarga yang ditinggalkan (lihat layanan Perubahan Data KK / Ganti Kepala Keluarga).',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => 'UU 23/2006 jo. UU 24/2013 (Pasal 44: lapor paling lambat 30 hari); Perpres 96/2018; Permendagri 108/2019.',
        'source_url' => $capil,
        'keywords' => 'akta mati, akte kematian, surat kematian, meninggal dunia, wafat, almarhum',
    ],
    [
        'title' => 'Akta Perkawinan (Non-Muslim)',
        'slug' => 'akta-perkawinan',
        'requirements' => $list([
            'Fotokopi surat keterangan telah terjadinya perkawinan dari pemuka agama atau penghayat kepercayaan (asli hanya diperlihatkan).',
            'Pas foto berwarna suami dan istri ukuran 4x6, masing-masing 1 lembar.',
            'KTP-el asli dan KK asli.',
            'Janda/duda cerai mati: fotokopi akta kematian pasangan; cerai hidup: fotokopi akta perceraian.',
            'Menikah di bawah 19 tahun: fotokopi penetapan pengadilan tentang dispensasi perkawinan.',
        ]),
        'steps' => $list([
            'Laporkan perkawinan paling lambat 60 hari sejak tanggal perkawinan.',
            'Datang ke Disdukcapil dan isi formulir F-2.01 (fotokopi KTP-el 2 saksi tidak perlu).',
            'Serahkan berkas persyaratan.',
            'Disdukcapil menerbitkan Kutipan Akta Perkawinan, KTP-el berstatus kawin dan KK yang dimutakhirkan.',
            'Pasangan beragama Islam mencatatkan perkawinan di KUA Kecamatan, bukan di Disdukcapil.',
        ]),
        'cost' => $free,
        'duration' => $fast,
        'legal_basis' => 'UU 23/2006 jo. UU 24/2013 (Pasal 34: lapor paling lambat 60 hari; Islam di KUA); Perpres 96/2018; Permendagri 108/2019 (Pasal 50).',
        'source_url' => $capil,
        'keywords' => 'akta nikah, akte perkawinan, catatan sipil nikah, pernikahan kristen, katolik, hindu, buddha, konghucu, penghayat',
    ],
    [
        'title' => 'Akta Perceraian',
        'slug' => 'akta-perceraian',
        'requirements' => $list([
            'Fotokopi salinan putusan pengadilan yang berkekuatan hukum tetap (asli hanya diperlihatkan).',
            'Kutipan akta perkawinan asli (bila tidak ada: surat pernyataan tidak memiliki kutipan akta perkawinan).',
            'KTP-el asli dan KK asli.',
        ]),
        'steps' => $list([
            'Laporkan perceraian paling lambat 60 hari sejak putusan pengadilan berkekuatan hukum tetap.',
            'Datang ke Disdukcapil dan isi formulir F-2.01.',
            'Serahkan berkas; kutipan akta perkawinan, KK dan KTP-el lama ditarik.',
            'Disdukcapil menerbitkan Kutipan Akta Perceraian serta KK dan KTP-el baru berstatus cerai hidup.',
        ]),
        'cost' => $free,
        'duration' => $noLimit,
        'legal_basis' => 'UU 23/2006 jo. UU 24/2013 (Pasal 40: lapor paling lambat 60 hari); Perpres 96/2018; Permendagri 108/2019.',
        'source_url' => $capil,
        'keywords' => 'akta cerai, akte perceraian, cerai, talak, putusan cerai, status cerai',
    ],
    [
        'title' => 'Perubahan Nama (Berdasarkan Penetapan Pengadilan Negeri)',
        'slug' => 'perubahan-nama',
        'requirements' => $list([
            'Fotokopi salinan penetapan Pengadilan Negeri tentang perubahan nama (asli tidak ditarik).',
            'Kutipan akta Pencatatan Sipil yang akan diberi catatan (mis. akta kelahiran).',
            'Fotokopi KK.',
        ]),
        'steps' => $list([
            'Ajukan permohonan ganti nama ke Pengadilan Negeri tempat tinggal pemohon sampai terbit penetapan.',
            'Laporkan ke Disdukcapil yang menerbitkan akta paling lambat 30 hari sejak menerima salinan penetapan.',
            'Isi formulir F-2.01 dan serahkan berkas.',
            'Disdukcapil membuat catatan pinggir perubahan nama pada register dan kutipan akta; lanjutkan perubahan data KK dan KTP-el.',
        ]),
        'cost' => 'Gratis di Disdukcapil (UU 24/2013 Pasal 79A); biaya perkara di Pengadilan Negeri mengikuti ketentuan pengadilan',
        'duration' => $noLimit,
        'legal_basis' => 'UU 23/2006 jo. UU 24/2013 (Pasal 52); Perpres 96/2018; Permendagri 108/2019.',
        'source_url' => $capil,
        'keywords' => 'ganti nama, ubah nama, perubahan nama, penetapan pengadilan, salah nama akta',
    ],
    [
        'title' => 'Kartu Identitas Anak (KIA)',
        'slug' => 'kia',
        'requirements' => $list([
            'Fotokopi kutipan akta kelahiran anak (tunjukkan aslinya).',
            'KK asli dan KTP-el asli kedua orang tua/wali (diperlihatkan; tidak perlu diserahkan bila sudah mengisi F-1.02).',
            'Anak usia 5–17 tahun kurang 1 hari: pas foto berwarna 2x3 sebanyak 2 lembar.',
            'KIA hilang: surat keterangan kehilangan dari kepolisian. KIA rusak: KIA yang rusak. Pindah datang: SKPWNI.',
        ]),
        'steps' => $list([
            'Orang tua/wali datang ke Disdukcapil dan isi formulir F-1.02.',
            'Serahkan berkas persyaratan.',
            'Disdukcapil menerbitkan KIA (KIA lama dimusnahkan bila penggantian).',
            'Masa berlaku: anak di bawah 5 tahun sampai usia 5 tahun; anak 5 tahun ke atas sampai 17 tahun kurang 1 hari.',
        ]),
        'cost' => $free,
        'duration' => $noLimit,
        'legal_basis' => 'Permendagri 2/2016 tentang Kartu Identitas Anak (Pasal 3–7); UU 23/2006 jo. UU 24/2013.',
        'source_url' => $daftar,
        'keywords' => 'kia, kartu anak, ktp anak, identitas anak, kia hilang, kia rusak',
    ],
]];
