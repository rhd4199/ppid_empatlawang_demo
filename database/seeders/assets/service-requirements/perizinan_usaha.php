<?php

// Business licensing for micro/small businesses (NIB, SPP-IRT, halal, BPOM MD, PBG). Facts from regulation
// texts (PP 28/2025, Perbadan POM 4/2024, PP 42/2024 jo. PP 40/2026, PP 16/2021) and official pages
// (oss.go.id, bpjph.halal.go.id, pom.go.id). Research: workspace shared/riset-persyaratan/perizinan/README.md.

$list = fn (array $items) => '<ol><li>' . implode('</li><li>', $items) . '</li></ol>';

$oss = 'https://s3.oss.go.id/oss/cms/Pendaftaran-Hak-Akses-Usaha-Mikro-dan-Kecil-%28UMK%29_V2-1-536283acebe7b49f0bb1aa1fa907a329.pdf';

return [
    'agency' => 'DPMPTSP Kabupaten Empat Lawang',
    'checked_at' => '2026-11-21',
    'items' => [
    [
        'title' => 'NIB (Nomor Induk Berusaha) untuk Usaha Mikro dan Kecil Perorangan',
        'slug' => 'nib-umk-perorangan',
        'agency' => 'Sistem OSS (oss.go.id); dibantu DPMPTSP Kabupaten Empat Lawang',
        'requirements' => $list([
            'NIK sesuai KTP-el yang terdaftar di Dukcapil.',
            'Nomor HP (WhatsApp) atau email aktif untuk kode verifikasi.',
            'Data usaha: nama dan alamat usaha, kegiatan usaha (kode KBLI), modal dan jumlah tenaga kerja.',
            'Usaha mikro: modal paling banyak Rp1 miliar (di luar tanah dan bangunan); usaha kecil: lebih dari Rp1 miliar sampai Rp5 miliar.',
        ]),
        'steps' => $list([
            'Buka oss.go.id (atau aplikasi OSS di HP), pilih DAFTAR, pilih skala usaha UMK lalu jenis pelaku usaha Orang Perseorangan.',
            'Isi NIK dan nomor HP/email, masukkan kode verifikasi, buat kata sandi.',
            'Lengkapi profil sesuai KTP-el, lalu masuk ke akun.',
            'Pilih menu Perizinan Berusaha, isi data usaha, pilih KBLI, setujui pernyataan mandiri.',
            'Usaha risiko rendah: NIB langsung terbit dan bisa diunduh. Risiko menengah/tinggi: lanjutkan pemenuhan Sertifikat Standar/izin sesuai arahan OSS.',
            'Kesulitan: datang ke DPMPTSP Kabupaten Empat Lawang untuk dibantu.',
        ]),
        'cost' => 'Gratis',
        'duration' => 'Risiko rendah: NIB terbit langsung setelah data lengkap (hari itu juga)',
        'legal_basis' => 'UU 6/2023 (Cipta Kerja); PP 28/2025 tentang Penyelenggaraan Perizinan Berusaha Berbasis Risiko (mencabut PP 5/2021).',
        'source_url' => $oss,
        'keywords' => 'nib, izin usaha, oss, buat izin usaha, siup, iumk, umkm, usaha mikro, warung, toko, dagang, kbli',
    ],
    [
        'title' => 'SPP-IRT / PIRT (Izin Produksi Pangan Industri Rumah Tangga)',
        'slug' => 'spp-irt-pirt',
        'agency' => 'DPMPTSP (terbit lewat OSS) dan Dinas Kesehatan Kabupaten Empat Lawang',
        'requirements' => $list([
            'NIB.',
            'Produksi di rumah tinggal/ruko yang ditinggali pemilik, dengan peralatan manual sampai semi otomatis.',
            'Jenis pangan termasuk daftar yang diizinkan (Lampiran I Perbadan POM 4/2024). Tidak bisa untuk: BTP, pangan wajib SNI, pangan berklaim, pangan yang perlu penanganan khusus (mis. beku/dingin), minuman beralkohol.',
            'Pernyataan pemenuhan komitmen, data pangan olahan, data label dan rancangan label (diisi di OSS).',
            'Tidak wajib SPP-IRT: pangan umur simpan kurang dari 7 hari dan pangan siap saji.',
        ]),
        'steps' => $list([
            'Masuk OSS dengan akun NIB, ajukan SPP-IRT per produk (beda jenis, rasa, kemasan atau label = permohonan terpisah).',
            'Isi data produk dan unggah rancangan label; data lengkap dan benar → SPP-IRT terbit paling lama 1 hari.',
            'Dalam 3 bulan sejak terbit penuhi komitmen: ikut penyuluhan keamanan pangan (sertifikat nilai minimal 60 dari Dinas Kesehatan/BPOM), pemeriksaan sarana produksi dan pemeriksaan label oleh Dinas Kesehatan.',
            'Belum memenuhi komitmen: diberi perpanjangan paling lama 3 bulan; tetap tidak dipenuhi → SPP-IRT dibekukan lalu dibatalkan.',
            'SPP-IRT berlaku 5 tahun; ajukan perpanjangan paling lambat 1 bulan sebelum habis.',
        ]),
        'cost' => 'Penerbitan lewat OSS tanpa biaya; jadwal dan biaya penyuluhan tanyakan ke Dinas Kesehatan',
        'duration' => 'Paling lama 1 hari sejak permohonan lengkap diterima (Perbadan POM 4/2024 Pasal 13)',
        'legal_basis' => 'Peraturan BPOM 4/2024 tentang Pedoman Penerbitan SPP-IRT (mencabut Perbadan POM 22/2018); PP 86/2019 tentang Keamanan Pangan; PP 28/2025.',
        'source_url' => 'https://peraturan.go.id/files/peraturan-bpom-no-4-tahun-2024.pdf',
        'keywords' => 'pirt, p-irt, spp-irt, izin makanan rumahan, izin produksi pangan, keripik, kue kering, sambal, kopi bubuk, dinkes, penyuluhan keamanan pangan',
    ],
    [
        'title' => 'Sertifikat Halal Gratis (Self Declare / SEHATI) untuk UMK',
        'slug' => 'sertifikat-halal-self-declare',
        'agency' => 'BPJPH Kementerian Agama (ptsp.halal.go.id) melalui Pendamping PPH',
        'requirements' => $list([
            'NIB dengan skala usaha mikro atau kecil; hasil penjualan tahunan paling banyak Rp15 miliar.',
            'Produk berupa barang, tidak berisiko, bahan sudah dipastikan halal (bersertifikat halal atau termasuk bahan yang dikecualikan).',
            'Proses produksi sederhana (rumahan, manual/semi otomatis), paling banyak 1 fasilitas produksi dan 1 outlet.',
            'Tempat dan alat produksi terpisah dari produk tidak halal; tidak memakai bahan berbahaya.',
            'Produk hewan sembelihan hanya bila dagingnya dari rumah potong bersertifikat halal.',
            'Dokumen di SIHALAL: surat permohonan, surat pernyataan self declare, akad/ikrar kehalalan produk dan bahan.',
        ]),
        'steps' => $list([
            'Buka ptsp.halal.go.id, buat akun SIHALAL (memakai NIB).',
            'Isi data permohonan dan pilih Pendamping Proses Produk Halal (PPH) di wilayah usaha.',
            'Pendamping PPH berkunjung untuk verifikasi dan validasi kehalalan produk.',
            'BPJPH memverifikasi hasil pendampingan dan menerbitkan Surat Tanda Terima Dokumen (STTD).',
            'Komite Fatwa Produk Halal menetapkan kehalalan; sertifikat halal elektronik terbit dan diunduh di SIHALAL.',
        ]),
        'cost' => 'Gratis selama kuota program SEHATI tersedia (kuota 2026: 1,35 juta sertifikat)',
        'duration' => 'Tergantung jadwal pendampingan dan sidang fatwa; pantau status di SIHALAL',
        'legal_basis' => 'UU 33/2014 jo. UU 6/2023 (Jaminan Produk Halal); PP 42/2024 jo. PP 40/2026; Keputusan Kepala BPJPH 146/2025 (juknis self declare).',
        'source_url' => 'https://bpjph.halal.go.id/detail/ini-kriteria-pelaku-umk-yang-dapat-mengajukan-sertifikat-halal-gratis-program-sehati-2026',
        'keywords' => 'halal, sertifikat halal, sehati, halal gratis, self declare, sihalal, label halal, mui, umkm halal',
    ],
    [
        'title' => 'Sertifikat Halal Reguler (Pemeriksaan LPH)',
        'slug' => 'sertifikat-halal-reguler',
        'agency' => 'BPJPH Kementerian Agama (ptsp.halal.go.id) dan Lembaga Pemeriksa Halal (LPH)',
        'requirements' => $list([
            'NIB.',
            'Untuk produk yang tidak memenuhi kriteria self declare (perlu diperiksa/diuji kehalalannya).',
            'Memenuhi kriteria Sistem Jaminan Produk Halal: komitmen dan tanggung jawab, bahan, proses produk halal (tempat dan alat terpisah dari yang tidak halal), produk, serta pemantauan dan evaluasi (audit internal minimal 1 kali setahun).',
            'Data produk dan bahan yang dipakai, diisi di SIHALAL.',
        ]),
        'steps' => $list([
            'Daftar dan ajukan permohonan di SIHALAL (ptsp.halal.go.id), pilih LPH.',
            'Bayar tarif layanan; LPH memeriksa/menguji produk.',
            'Hasil pemeriksaan disidangkan untuk ketetapan fatwa halal MUI.',
            'BPJPH menerbitkan sertifikat halal elektronik yang diunduh di SIHALAL.',
        ]),
        'cost' => 'UMK: Rp650.000 (pendaftaran dan penetapan Rp300.000 + pemeriksaan LPH Rp350.000); usaha menengah/besar lebih tinggi',
        'duration' => 'Tergantung pemeriksaan LPH dan sidang fatwa; pantau status di SIHALAL',
        'legal_basis' => 'UU 33/2014 jo. UU 6/2023; PP 42/2024 jo. PP 40/2026; PMK 57/2021 dan Keputusan Kepala BPJPH 141/2021 (tarif layanan).',
        'source_url' => 'https://bpjph.halal.go.id/read/pelaku-umk-urus-sertifikasi-halal-mudah-dan-gratis',
        'keywords' => 'halal reguler, sertifikat halal berbayar, lph, audit halal, biaya sertifikat halal, rumah makan halal, katering halal',
    ],
    [
        'title' => 'Izin Edar BPOM MD (Pangan Olahan Produksi Dalam Negeri)',
        'slug' => 'izin-edar-bpom-md',
        'agency' => 'BPOM (registrasi pangan olahan daring)',
        'requirements' => $list([
            'Untuk pangan olahan yang tidak boleh atau tidak cukup memakai SPP-IRT (mis. produk pabrik, berklaim, wajib SNI, umur simpan lama dengan proses khusus).',
            'NIB dan akun registrasi pangan olahan BPOM.',
            'Bukti sarana produksi memenuhi Cara Produksi Pangan Olahan yang Baik (CPPOB): hasil audit sarana atau sertifikat CPPOB; UMK dapat menerapkan CPPOB bertahap.',
            'Data produk: komposisi, proses produksi, hasil uji, dan rancangan label.',
        ]),
        'steps' => $list([
            'Buat akun perusahaan di layanan registrasi pangan olahan BPOM.',
            'Penuhi pemeriksaan sarana produksi (CPPOB).',
            'Ajukan pendaftaran produk dan unggah data/label.',
            'BPOM mengevaluasi; disetujui → Nomor Izin Edar BPOM MD terbit dan wajib dicantumkan di label.',
            'Butuh pendampingan: BPOM punya program pendampingan UMK (SAPA SEMESTA UMK Pangan Olahan).',
        ]),
        'cost' => 'UMK pangan olahan: tarif PNBP Rp0 (gratis); non-UMK sesuai tarif PNBP BPOM',
        'duration' => 'Sesuai jalur evaluasi BPOM; tanyakan ke Balai/Loka POM terdekat',
        'legal_basis' => 'Peraturan BPOM 27/2017 tentang Pendaftaran Pangan Olahan beserta perubahannya; PP 86/2019 tentang Keamanan Pangan.',
        'source_url' => 'https://www.pom.go.id/berita/kepala-bpom-ungkap-biaya-registrasi-pangan-0-rupiah-untuk-umk',
        'keywords' => 'bpom, md, izin edar, nomor bpom, pom md, registrasi pangan, cppob, daftar bpom',
    ],
    [
        'title' => 'PBG (Persetujuan Bangunan Gedung, pengganti IMB) dan SLF',
        'slug' => 'pbg-pengganti-imb',
        'agency' => 'Pemerintah Kabupaten Empat Lawang (DPMPTSP / Dinas PUPR) melalui SIMBG',
        'requirements' => $list([
            'Data pemohon/pemilik bangunan (atau kuasanya).',
            'Data bangunan gedung: fungsi, luas, jumlah lantai, tinggi, lokasi.',
            'Dokumen rencana teknis bangunan (diperiksa dalam konsultasi perencanaan).',
        ]),
        'steps' => $list([
            'Daftar dan ajukan permohonan PBG di SIMBG sebelum mulai membangun.',
            'Lengkapi data pemohon, data bangunan dan dokumen rencana teknis.',
            'Ikuti jadwal konsultasi perencanaan dari sekretariat SIMBG; dokumen rencana teknis diperiksa dan disetujui.',
            'Bayar retribusi PBG sesuai perhitungan SIMBG dan Perda Kabupaten Empat Lawang; PBG terbit.',
            'Setelah bangunan selesai, ajukan SLF (Sertifikat Laik Fungsi) di SIMBG sebelum bangunan dipakai.',
        ]),
        'cost' => 'Retribusi PBG sesuai Perda Pajak Daerah dan Retribusi Daerah Kabupaten Empat Lawang (dihitung per luas bangunan di SIMBG)',
        'duration' => 'Mengikuti jadwal konsultasi di SIMBG; tanyakan ke DPMPTSP/Dinas PUPR',
        'legal_basis' => 'UU 28/2002 tentang Bangunan Gedung jo. UU 6/2023 (Cipta Kerja); PP 16/2021 tentang Peraturan Pelaksanaan UU Bangunan Gedung; PP 28/2025.',
        'source_url' => 'https://www.hukumonline.com/klinik/a/catat-ini-3-dokumen-penting-terkait-bangunan-gedung-lt6058aae3aad6d',
        'keywords' => 'pbg, imb, izin mendirikan bangunan, izin bangun rumah, slf, laik fungsi, simbg, izin bangunan, ruko',
    ],
]];
