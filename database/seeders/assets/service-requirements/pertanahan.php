<?php

// Land services (ATR/BPN). Requirements from PP 24/1997, UU 4/1996 and the ATR/BPN service FAQ (quoted by
// Hukumonline); PNBP from PP 128/2015 (text + explanation + Lampiran); taxes from PP 34/2016 and UU 1/2022.
// Research: workspace shared/riset-persyaratan/pertanahan/README.md.

$list = fn (array $items) => '<ol><li>' . implode('</li><li>', $items) . '</li></ol>';

$pp24 = 'https://peraturan.go.id/files/pp24-1997.pdf';
$transferFee = 'PNBP peralihan hak: (nilai tanah ÷ 1.000) + Rp50.000, nilai tanah = zona nilai tanah BPN (atau NJOP) × luas (PP 128/2015 Pasal 16 ayat (2))';
$taxes = 'BPHTB dibayar penerima hak (tarif paling tinggi 5% dari nilai perolehan dikurangi NPOPTKP, sesuai Perda Kabupaten Empat Lawang); PPh final 2,5% dibayar penjual/pemberi (PP 34/2016)';
$id = 'Fotokopi KTP dan KK pemohon/para pihak (dicocokkan dengan aslinya), surat kuasa bila dikuasakan.';
$form = 'Formulir permohonan bermeterai yang ditandatangani pemohon atau kuasanya.';

return [
    'agency' => 'Kantor Pertanahan Kabupaten Empat Lawang (Kementerian ATR/BPN)',
    'checked_at' => '2026-11-21',
    'items' => [
    [
        'title' => 'Balik Nama Sertifikat Tanah karena Jual Beli',
        'slug' => 'balik-nama-sertifikat-jual-beli',
        'requirements' => $list([
            'Akta Jual Beli (AJB) yang dibuat di hadapan PPAT.',
            'Sertifikat hak atas tanah asli.',
            $form,
            $id,
            'Fotokopi SPPT PBB tahun berjalan.',
            'Bukti setor BPHTB (pembeli) dan bukti setor PPh final (penjual).',
        ]),
        'steps' => $list([
            'Penjual dan pembeli datang ke PPAT; PPAT mengecek sertifikat ke Kantor Pertanahan.',
            'Penjual membayar PPh final, pembeli membayar BPHTB ke Bapenda/BPKAD Kabupaten Empat Lawang.',
            'Penandatanganan AJB di hadapan PPAT dengan 2 orang saksi.',
            'PPAT menyampaikan AJB dan berkas ke Kantor Pertanahan paling lambat 7 hari kerja setelah AJB ditandatangani.',
            'Kantor Pertanahan mencatat peralihan hak; sertifikat atas nama pembeli diserahkan.',
        ]),
        'cost' => $transferFee . '. Pajak: ' . $taxes . '. Honor PPAT paling banyak 1% dari harga transaksi (PP 24/2016).',
        'duration' => 'Pendaftaran peralihan hak di Kantor Pertanahan; tanyakan perkiraan waktu ke PPAT atau loket (aplikasi Sentuh Tanahku)',
        'legal_basis' => 'PP 24/1997 tentang Pendaftaran Tanah (Pasal 37, 40); PP 128/2015 tentang Tarif PNBP ATR/BPN; PP 34/2016 tentang PPh atas Pengalihan Tanah/Bangunan; UU 1/2022 (BPHTB).',
        'source_url' => 'https://www.hukumonline.com/klinik/a/balik-nama-sertifikat-tanah-lt4c66b0f0dd1dd/',
        'keywords' => 'balik nama sertifikat, jual beli tanah, ajb, ppat, notaris, bphtb, pph tanah, beli tanah, beli rumah, shm',
    ],
    [
        'title' => 'Balik Nama Sertifikat Tanah karena Warisan',
        'slug' => 'balik-nama-sertifikat-waris',
        'requirements' => $list([
            'Sertifikat hak atas tanah asli.',
            'Surat kematian pemegang hak (akta kematian).',
            'Surat keterangan waris sesuai ketentuan (dan akta wasiat notariel bila ada); ahli waris lebih dari satu dan tanah jatuh ke satu orang: akta pembagian waris.',
            $form,
            'Fotokopi KTP dan KK para ahli waris (dicocokkan dengan aslinya), surat kuasa bila dikuasakan.',
            'Fotokopi SPPT PBB tahun berjalan dan bukti setor BPHTB waris.',
        ]),
        'steps' => $list([
            'Urus akta kematian (Disdukcapil) dan surat keterangan waris.',
            'Bayar BPHTB waris ke Bapenda/BPKAD Kabupaten Empat Lawang.',
            'Ajukan pendaftaran peralihan hak karena waris ke Kantor Pertanahan.',
            'Sertifikat dicatat atas nama ahli waris (semua ahli waris bersama, atau satu orang bila ada akta pembagian waris).',
        ]),
        'cost' => $transferFee . '. BPHTB waris sesuai Perda Kabupaten Empat Lawang. Warisan dikecualikan dari PPh final (PP 34/2016 Pasal 6) dengan surat keterangan bebas dari kantor pajak.',
        'duration' => 'Tanyakan perkiraan waktu ke loket Kantor Pertanahan',
        'legal_basis' => 'PP 24/1997 tentang Pendaftaran Tanah (Pasal 36, 42); PP 128/2015; PP 34/2016 Pasal 6; UU 1/2022 (BPHTB).',
        'source_url' => 'https://www.hukumonline.com/klinik/a/cara-memperoleh-pengganti-sertifikat-tanah-yang-hilang-lt53270d56b1c96/',
        'keywords' => 'balik nama waris, tanah warisan, sertifikat warisan, ahli waris, surat keterangan waris, turun waris',
    ],
    [
        'title' => 'Balik Nama Sertifikat Tanah karena Hibah',
        'slug' => 'balik-nama-sertifikat-hibah',
        'requirements' => $list([
            'Akta hibah yang dibuat di hadapan PPAT.',
            'Sertifikat hak atas tanah asli.',
            $form,
            $id,
            'Fotokopi SPPT PBB tahun berjalan dan bukti setor BPHTB (penerima hibah).',
        ]),
        'steps' => $list([
            'Pemberi dan penerima hibah datang ke PPAT; PPAT mengecek sertifikat.',
            'Penerima membayar BPHTB; pemberi mengurus PPh atau surat keterangan bebas PPh.',
            'Penandatanganan akta hibah di hadapan PPAT dengan 2 orang saksi.',
            'PPAT mendaftarkan akta hibah ke Kantor Pertanahan; sertifikat dicatat atas nama penerima hibah.',
        ]),
        'cost' => $transferFee . '. BPHTB sesuai Perda Kabupaten Empat Lawang. Hibah kepada keluarga sedarah garis lurus satu derajat (orang tua–anak) dikecualikan dari PPh final (PP 34/2016 Pasal 6) dengan surat keterangan bebas.',
        'duration' => 'Tanyakan perkiraan waktu ke PPAT atau loket Kantor Pertanahan',
        'legal_basis' => 'PP 24/1997 tentang Pendaftaran Tanah (Pasal 37); PP 128/2015; PP 34/2016 Pasal 6; UU 1/2022 (BPHTB).',
        'source_url' => $pp24,
        'keywords' => 'hibah tanah, akta hibah, tanah dari orang tua, pemberian tanah, ppat',
    ],
    [
        'title' => 'Pendaftaran Tanah Pertama Kali / PTSL (Sertifikat untuk Tanah Belum Bersertifikat)',
        'slug' => 'pendaftaran-tanah-pertama-ptsl',
        'requirements' => $list([
            'Fotokopi KTP dan KK pemohon.',
            'Bukti perolehan/alas hak: surat jual beli, hibah atau waris; girik/letter C/surat keterangan tanah desa dipakai sebagai petunjuk lokasi, bukan bukti hak (PP 18/2021 Pasal 96, sejak 2 Februari 2026).',
            'Surat pernyataan penguasaan fisik bidang tanah, diketahui kepala desa dan saksi.',
            'Tanda batas (patok) terpasang dan surat pernyataan batas yang disetujui tetangga berbatasan.',
            'Fotokopi SPPT PBB dan bukti bayar BPHTB bila terutang.',
        ]),
        'steps' => $list([
            'PTSL (program pemerintah, per desa yang ditetapkan): ikuti penyuluhan di desa, pasang patok, serahkan berkas ke panitia.',
            'Petugas mengukur bidang dan mengumpulkan data yuridis.',
            'Data diumumkan 14 hari di kantor panitia ajudikasi dan kantor desa untuk menampung keberatan.',
            'Sertifikat diterbitkan dan dibagikan.',
            'Desa tidak masuk PTSL: ajukan pendaftaran pertama kali (sporadik) sendiri di Kantor Pertanahan, dengan biaya pengukuran dan pendaftaran sesuai PP 128/2015.',
        ]),
        'cost' => 'PTSL: pengukuran dan penerbitan sertifikat ditanggung pemerintah; biaya persiapan di desa (patok, meterai, petugas desa) paling banyak Rp200.000 untuk Sumatera Selatan (SKB 3 Menteri 2017, Kategori IV). Pungutan di atas itu tanpa dasar hukum = pungli. BPHTB bila terutang.',
        'duration' => 'PTSL mengikuti jadwal program di desa; jalur sporadik sesuai standar layanan Kantor Pertanahan',
        'legal_basis' => 'PP 24/1997 (Pasal 24); PP 18/2021 (Pasal 96); SKB 3 Menteri No. 25/SKB/V/2017 tentang Pembiayaan Persiapan PTSL; PP 128/2015.',
        'source_url' => 'https://www.detik.com/properti/kepemilikan-rumah/d-8451108/hati-hati-pungli-ini-biaya-persiapan-buat-ikut-ptsl',
        'keywords' => 'ptsl, prona, sertifikat gratis, buat sertifikat tanah, tanah belum bersertifikat, girik, sporadik, skt, sk tanah, surat keterangan tanah, letter c',
    ],
    [
        'title' => 'Sertifikat Tanah Hilang atau Rusak (Sertifikat Pengganti)',
        'slug' => 'sertifikat-tanah-hilang-rusak',
        'requirements' => $list([
            $form,
            $id,
            'Fotokopi sertifikat (bila ada); sertifikat rusak: sertifikat yang rusak.',
            'Hilang: surat tanda lapor kehilangan dari kepolisian setempat.',
            'Hilang: surat pernyataan di bawah sumpah oleh pemegang hak/yang menghilangkan, diucapkan di hadapan Kepala Kantor Pertanahan atau pejabat yang ditunjuk.',
        ]),
        'steps' => $list([
            'Buat laporan kehilangan di kantor polisi.',
            'Ajukan permohonan sertifikat pengganti ke Kantor Pertanahan dan ucapkan sumpah.',
            'Kantor Pertanahan mengumumkan rencana penerbitan sertifikat pengganti 1 kali di surat kabar harian setempat (biaya pemohon).',
            'Tidak ada keberatan yang beralasan dalam 30 hari: sertifikat pengganti diterbitkan dan diumumkan bahwa sertifikat lama tidak berlaku.',
        ]),
        'cost' => 'PNBP penggantian blanko sertifikat Rp50.000 per bidang + sumpah dan naskah pengumuman Rp200.000 (PP 128/2015 Lampiran II.B angka 15 dan 19) + biaya iklan surat kabar',
        'duration' => 'Hilang: sekitar 40 hari kerja (standar layanan ATR/BPN); rusak lebih singkat',
        'legal_basis' => 'PP 24/1997 tentang Pendaftaran Tanah (Pasal 57–59); PP 128/2015.',
        'source_url' => 'https://www.hukumonline.com/klinik/a/cara-memperoleh-pengganti-sertifikat-tanah-yang-hilang-lt53270d56b1c96/',
        'keywords' => 'sertifikat hilang, sertifikat rusak, sertifikat pengganti, kehilangan sertifikat tanah, sertifikat terbakar, sertifikat banjir',
    ],
    [
        'title' => 'Pemecahan, Pemisahan atau Penggabungan Bidang Tanah',
        'slug' => 'pemecahan-penggabungan-bidang',
        'requirements' => $list([
            'Surat permohonan pemecahan/pemisahan/penggabungan.',
            'Sertifikat tanah asli (penggabungan: semua sertifikat, bidang berbatasan dan atas nama pemilik yang sama).',
            'Fotokopi KTP dan KK pemilik.',
            'Fotokopi SPPT PBB tahun berjalan.',
            'Tanah warisan: surat keterangan/akta waris dan surat kematian pemilik lama. Pengembang: site plan dari pemerintah daerah.',
            'Tanah dibebani hak tanggungan: persetujuan tertulis pemegang hak tanggungan (bank).',
        ]),
        'steps' => $list([
            'Ajukan permohonan ke Kantor Pertanahan.',
            'Petugas mengukur ulang dan membuat peta bidang baru.',
            'Diterbitkan surat ukur, buku tanah dan sertifikat baru untuk tiap bidang; pada pemecahan sertifikat induk tidak berlaku lagi.',
        ]),
        'cost' => 'PNBP pendaftaran pemecahan/pemisahan/penggabungan Rp50.000 per bidang (PP 128/2015 Lampiran II.B angka 12) + biaya pengukuran sesuai luas (PP 128/2015)',
        'duration' => 'Tergantung jadwal pengukuran; tanyakan ke loket Kantor Pertanahan',
        'legal_basis' => 'PP 24/1997 tentang Pendaftaran Tanah (Pasal 48–50); PP 128/2015.',
        'source_url' => 'https://jogja.antaranews.com/berita/833524/kenali-prosedur-dan-syarat-pemecahan-bidang-tanah',
        'keywords' => 'pecah sertifikat, pemecahan sertifikat, pisah sertifikat, gabung sertifikat, kavling, jual sebagian tanah',
    ],
    [
        'title' => 'Roya (Penghapusan Hak Tanggungan setelah Kredit Lunas)',
        'slug' => 'roya-hak-tanggungan',
        'requirements' => $list([
            'Sertifikat tanah asli.',
            'Sertifikat Hak Tanggungan yang sudah diberi catatan lunas oleh bank, atau pernyataan tertulis bank bahwa utang lunas/hak tanggungan dilepaskan (surat roya).',
            $form,
            'Fotokopi KTP dan KK pemilik.',
        ]),
        'steps' => $list([
            'Lunasi kredit dan minta surat keterangan lunas serta surat roya dari bank.',
            'Ajukan permohonan roya ke Kantor Pertanahan (banyak bank juga melayani roya elektronik).',
            'Kantor Pertanahan mencoret catatan hak tanggungan di buku tanah dan sertifikat; sertifikat hak tanggungan ditarik.',
        ]),
        'cost' => 'PNBP roya Rp50.000 per sertifikat hak tanggungan (PP 128/2015 Lampiran II.B angka 13); bila lewat notaris ada jasa notaris',
        'duration' => 'Sekitar 3–5 hari kerja',
        'legal_basis' => 'UU 4/1996 tentang Hak Tanggungan (Pasal 22); PP 128/2015.',
        'source_url' => 'https://peraturan.go.id/files/uu4-1996.pdf',
        'keywords' => 'roya, surat roya, kpr lunas, hapus hak tanggungan, sertifikat dari bank, agunan lunas',
    ],
]];
