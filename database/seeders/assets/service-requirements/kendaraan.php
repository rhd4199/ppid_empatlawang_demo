<?php

// SIM (Perpol 5/2021 jo. Perpol 2/2023), vehicle registration (Perpol 7/2021) and Polri PNBP tariffs
// (PP 76/2020 Lampiran, cross-checked with Kompas TV and Hukumonline). Research: workspace
// shared/riset-persyaratan/kendaraan/README.md.

$list = fn (array $items) => '<ol><li>' . implode('</li><li>', $items) . '</li></ol>';

$satpas = 'Satpas SIM Satlantas Polres Empat Lawang';
$samsat = 'Samsat Kabupaten Empat Lawang (Polri, UPTB PPD Bapenda Sumsel, Jasa Raharja)';
$bpkb = 'Unit Regident BPKB Satlantas Polres Empat Lawang / Ditlantas Polda Sumsel';
$simLaw = 'Perpol 5/2021 tentang Penerbitan dan Penandaan SIM jo. Perpol 2/2023; PP 76/2020 tentang Jenis dan Tarif PNBP Polri';
$regLaw = 'Perpol 7/2021 tentang Registrasi dan Identifikasi Kendaraan Bermotor; PP 76/2020 tentang Jenis dan Tarif PNBP Polri';
$perpol7 = 'https://peraturan.go.id/files/bn476-2021.pdf';
$medical = 'Biaya tes kesehatan dan tes psikologi bukan PNBP; tanyakan tarifnya di Satpas';
$id = 'KTP-el asli dan fotokopinya (diwakilkan: surat kuasa bermeterai dan fotokopi KTP penerima kuasa).';

return [
    'agency' => $samsat,
    'checked_at' => '2026-11-21',
    'items' => [
    [
        'title' => 'SIM Baru (SIM A dan SIM C)',
        'slug' => 'sim-baru',
        'agency' => $satpas,
        'requirements' => $list([
            'Usia minimal 17 tahun untuk SIM A dan SIM C (SIM CI 18 tahun, SIM CII 19 tahun; CI/CII hanya peningkatan dari SIM C yang sudah dipakai 12 bulan).',
            'Formulir pendaftaran (manual di Satpas atau bukti daftar online).',
            'KTP-el asli dan fotokopinya.',
            'Fotokopi sertifikat pendidikan dan pelatihan mengemudi dari sekolah mengemudi terakreditasi (paling lama 6 bulan), atau surat hasil verifikasi kompetensi mengemudi bagi yang belajar sendiri.',
            'Surat keterangan sehat jasmani dari dokter (berlaku 14 hari) dan surat lulus tes psikologi (berlaku 6 bulan).',
            'Bukti bayar PNBP.',
        ]),
        'steps' => $list([
            'Datang ke Satpas, isi formulir dan serahkan berkas.',
            'Ikuti tes kesehatan dan tes psikologi bila belum punya suratnya.',
            'Bayar PNBP di loket bank.',
            'Perekaman biometrik (sidik jari, wajah).',
            'Ikuti ujian teori, lalu ujian praktik.',
            'Lulus: SIM dicetak; berlaku 5 tahun.',
        ]),
        'cost' => 'PNBP SIM A Rp120.000, SIM C Rp100.000 (SIM CI/CII Rp100.000) — PP 76/2020 Lampiran angka I. ' . $medical . '.',
        'duration' => 'Selesai di hari yang sama bila lulus semua ujian; tidak lulus dapat mengulang sesuai jadwal Satpas',
        'legal_basis' => $simLaw . ' (Pasal 4, 8, 9, 11, 12 Perpol 5/2021).',
        'source_url' => 'https://peraturan.bpk.go.id/Download/210908/Perpol%20Nomor%205%20Tahun%202021.pdf',
        'keywords' => 'sim, buat sim, sim a, sim c, sim motor, sim mobil, ujian sim, tes sim, sim baru, surat izin mengemudi',
    ],
    [
        'title' => 'Perpanjangan SIM',
        'slug' => 'sim-perpanjangan',
        'agency' => $satpas,
        'requirements' => $list([
            'SIM lama yang masih berlaku (perpanjangan harus sebelum masa berlaku habis).',
            'KTP-el asli dan fotokopinya.',
            'Surat keterangan sehat jasmani dan surat lulus tes psikologi.',
            'Bukti bayar PNBP.',
        ]),
        'steps' => $list([
            'Datang ke Satpas (atau layanan SIM keliling/gerai bila tersedia) sebelum SIM habis masa berlakunya.',
            'Serahkan berkas, ikuti tes kesehatan dan psikologi.',
            'Bayar PNBP, perekaman biometrik, SIM baru dicetak (berlaku 5 tahun lagi).',
            'SIM sudah lewat masa berlaku: tidak bisa diperpanjang, harus mengajukan SIM baru (ikut ujian lagi).',
        ]),
        'cost' => 'PNBP perpanjangan SIM A Rp80.000, SIM C Rp75.000 — PP 76/2020 Lampiran angka II. ' . $medical . '.',
        'duration' => 'Umumnya selesai di hari yang sama',
        'legal_basis' => $simLaw . ' (Pasal 4 ayat (1) dan (3) Perpol 5/2021).',
        'source_url' => 'https://peraturan.bpk.go.id/Download/210908/Perpol%20Nomor%205%20Tahun%202021.pdf',
        'keywords' => 'perpanjang sim, sim mati, sim habis, sim kadaluarsa, sim expired, sim keliling',
    ],
    [
        'title' => 'SIM Hilang atau Rusak',
        'slug' => 'sim-hilang-rusak',
        'agency' => $satpas,
        'requirements' => $list([
            'SIM hilang: surat tanda penerimaan laporan kehilangan dari Polri.',
            'SIM rusak: SIM lama yang rusak.',
            'KTP-el asli dan fotokopinya.',
            'Surat keterangan sehat jasmani dan surat lulus tes psikologi.',
            'Bukti bayar PNBP.',
        ]),
        'steps' => $list([
            'SIM hilang: buat laporan kehilangan di kantor polisi terdekat.',
            'Datang ke Satpas dengan berkas, ikuti tes kesehatan dan psikologi.',
            'Bayar PNBP dan perekaman biometrik; SIM pengganti dicetak.',
        ]),
        'cost' => 'PNBP mengikuti tarif penerbitan SIM sesuai golongan (PP 76/2020); pastikan jenis tarif di loket Satpas. ' . $medical . '.',
        'duration' => 'Umumnya selesai di hari yang sama',
        'legal_basis' => $simLaw . ' (Pasal 6 ayat (3) huruf f dan Pasal 9 ayat (3) Perpol 5/2021).',
        'source_url' => 'https://peraturan.bpk.go.id/Download/210908/Perpol%20Nomor%205%20Tahun%202021.pdf',
        'keywords' => 'sim hilang, sim rusak, ganti sim, sim patah, laporan kehilangan sim',
    ],
    [
        'title' => 'Pajak Tahunan Kendaraan (Pengesahan STNK)',
        'slug' => 'pajak-tahunan-stnk',
        'requirements' => $list([
            $id,
            'STNK asli.',
            'Bayar PKB (beserta opsen) dan SWDKLLJ.',
        ]),
        'steps' => $list([
            'Datang ke Samsat (atau Samsat keliling) sebelum jatuh tempo pajak.',
            'Serahkan KTP dan STNK ke loket pendaftaran.',
            'Bayar PKB, opsen PKB dan SWDKLLJ sesuai penetapan; terlambat dikenai denda.',
            'STNK disahkan (cap/paraf) dan diserahkan bersama bukti bayar.',
            'Bisa juga lewat Samsat Online bila kendaraan terdata, tidak diblokir, dan pajak serta SWDKLLJ sudah dibayar.',
        ]),
        'cost' => 'PKB dan opsen PKB sesuai nilai kendaraan (lihat STNK/notice pajak) + SWDKLLJ Jasa Raharja; pengesahan tahunan tanpa PNBP STNK',
        'duration' => 'Umumnya selesai di hari yang sama',
        'legal_basis' => $regLaw . ' (Pasal 61 Perpol 7/2021); UU 1/2022 (HKPD) tentang PKB dan opsen PKB.',
        'source_url' => $perpol7,
        'keywords' => 'pajak motor, pajak mobil, bayar pajak kendaraan, stnk tahunan, pkb, samsat, samsat keliling, pajak telat, denda pajak',
    ],
    [
        'title' => 'Perpanjangan STNK 5 Tahunan (Ganti Pelat)',
        'slug' => 'stnk-5-tahunan',
        'requirements' => $list([
            $id,
            'STNK asli.',
            'BPKB asli.',
            'Hasil cek fisik kendaraan (gesek nomor rangka dan mesin) — kendaraan dibawa ke Samsat.',
        ]),
        'steps' => $list([
            'Bawa kendaraan ke Samsat dan lakukan cek fisik.',
            'Serahkan berkas ke loket pendaftaran.',
            'Bayar PKB, opsen PKB, SWDKLLJ, PNBP STNK dan PNBP TNKB.',
            'STNK baru dan pelat nomor (TNKB) baru diserahkan.',
        ]),
        'cost' => 'PNBP STNK: roda 2/3 Rp100.000, roda 4+ Rp200.000; PNBP TNKB: roda 2/3 Rp60.000, roda 4+ Rp100.000 per pasang (PP 76/2020 Lampiran angka IV dan VI) + PKB, opsen dan SWDKLLJ',
        'duration' => 'Umumnya selesai di hari yang sama; pelat dapat menyusul bila stok habis',
        'legal_basis' => $regLaw . ' (Pasal 11 dan Pasal 62 Perpol 7/2021).',
        'source_url' => $perpol7,
        'keywords' => 'stnk 5 tahun, ganti plat, ganti pelat, plat nomor, tnkb, perpanjang stnk, cek fisik',
    ],
    [
        'title' => 'Balik Nama Kendaraan Bekas (Motor/Mobil)',
        'slug' => 'balik-nama-kendaraan',
        'requirements' => $list([
            'KTP-el pemilik baru (KTP pemilik lama tidak diperlukan).',
            'BPKB asli dan STNK asli.',
            'Bukti pemindahtanganan: kuitansi jual beli bermeterai dan/atau surat pelepasan hak (atau akta hibah/waris).',
            'Hasil cek fisik kendaraan.',
            'Pemilik baru di luar wilayah registrasi kendaraan: surat pengantar mutasi (lihat layanan Mutasi Kendaraan).',
        ]),
        'steps' => $list([
            'Cek fisik kendaraan di Samsat.',
            'Ajukan penggantian BPKB atas nama pemilik baru (layanan BPKB), bayar PNBP BPKB.',
            'Ajukan perubahan data STNK di Samsat: bayar PKB, opsen PKB, SWDKLLJ, PNBP STNK dan TNKB.',
            'Terima BPKB, STNK dan pelat atas nama pemilik baru.',
        ]),
        'cost' => 'PNBP BPKB ganti kepemilikan: roda 2/3 Rp225.000, roda 4+ Rp375.000; STNK Rp100.000/Rp200.000; TNKB Rp60.000/Rp100.000 (PP 76/2020). BBNKB kendaraan bekas Rp0 (UU 1/2022 Pasal 12: BBNKB hanya untuk penyerahan pertama); PKB, opsen dan SWDKLLJ tetap dibayar',
        'duration' => 'STNK umumnya hari yang sama; BPKB menyusul sesuai antrean unit BPKB',
        'legal_basis' => $regLaw . ' (Pasal 29, 30 dan 58 Perpol 7/2021); UU 1/2022 tentang Hubungan Keuangan Pusat dan Daerah (Pasal 12).',
        'source_url' => $perpol7,
        'keywords' => 'balik nama, bbn, bbnkb, motor bekas, mobil bekas, ganti nama bpkb, beli kendaraan bekas, jual beli motor',
    ],
    [
        'title' => 'Mutasi Kendaraan ke Luar Daerah',
        'slug' => 'mutasi-kendaraan',
        'requirements' => $list([
            $id,
            'BPKB asli dan STNK asli.',
            'Bukti bayar PNBP mutasi keluar daerah.',
            'Hasil cek fisik kendaraan.',
        ]),
        'steps' => $list([
            'Di daerah asal: cek fisik, ajukan mutasi keluar dan bayar PNBP mutasi.',
            'Terima dokumen mutasi dan surat keterangan pengganti STNK (berlaku paling lama 60 hari).',
            'Di daerah tujuan: daftarkan kendaraan dengan dokumen mutasi; bayar PKB, PNBP STNK, TNKB dan BPKB sesuai ketentuan; terima STNK, pelat dan BPKB baru.',
            'Mutasi masuk ke Empat Lawang: bawa dokumen mutasi dari daerah asal ke Samsat Empat Lawang.',
        ]),
        'cost' => 'PNBP surat mutasi ke luar daerah: roda 2/3 Rp150.000, roda 4+ Rp250.000 (PP 76/2020 Lampiran angka IX) + biaya pendaftaran di daerah tujuan',
        'duration' => 'Tergantung pengiriman dokumen antar-Polda; surat pengganti STNK berlaku 60 hari',
        'legal_basis' => $regLaw . ' (Pasal 13, 28 ayat (3), 40 Perpol 7/2021).',
        'source_url' => $perpol7,
        'keywords' => 'mutasi kendaraan, cabut berkas, pindah plat, mutasi motor, mutasi mobil, kendaraan luar daerah, plat luar',
    ],
    [
        'title' => 'BPKB Hilang',
        'slug' => 'bpkb-hilang',
        'agency' => $bpkb,
        'requirements' => $list([
            $id,
            'Surat Tanda Penerimaan Laporan kehilangan dari Polri dan Berita Acara Pemeriksaan dari Penyidik Polri.',
            'STNK asli.',
            'Surat pernyataan bermeterai bahwa BPKB yang hilang tidak terkait kasus pidana/perdata.',
            'Bukti pengumuman di media cetak 3 kali berturut-turut, sebulan sekali (bulan pertama media lokal, bulan kedua dan ketiga media nasional).',
            'Hasil cek fisik kendaraan dan bukti bayar PNBP.',
        ]),
        'steps' => $list([
            'Laporkan kehilangan ke kantor polisi; jalani pemeriksaan oleh penyidik.',
            'Pasang pengumuman kehilangan di media cetak selama 3 bulan.',
            'Cek fisik kendaraan, lalu ajukan penggantian BPKB dengan semua berkas.',
            'Bayar PNBP; BPKB pengganti diterbitkan setelah penelitian berkas.',
            'BPKB rusak: cukup formulir, identitas, BPKB yang rusak, PNBP (tanpa laporan polisi dan pengumuman).',
        ]),
        'cost' => 'PNBP penerbitan BPKB: roda 2/3 Rp225.000, roda 4+ Rp375.000 (PP 76/2020 Lampiran angka VIII); pastikan jenis tarif di loket + biaya pemasangan pengumuman di media',
        'duration' => 'Minimal sekitar 3 bulan karena pengumuman media cetak 3 bulan berturut-turut',
        'legal_basis' => $regLaw . ' (Pasal 32 Perpol 7/2021).',
        'source_url' => $perpol7,
        'keywords' => 'bpkb hilang, bpkb rusak, ganti bpkb, buku kendaraan hilang, kehilangan bpkb',
    ],
]];
