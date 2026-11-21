<?php

// ASN transfers (mutasi). Requirements and procedure from Peraturan BKN 5/2019 (Pasal 2–10) and its
// technical circular SE Kepala BKN 3/SE/VIII/2019; PPPK rules from KemenPANRB statements in the press
// (no regulation text found). Research: workspace shared/riset-persyaratan/kepegawaian/README.md.

$list = fn (array $items) => '<ol><li>' . implode('</li><li>', $items) . '</li></ol>';

$perbkn = 'https://peraturan.go.id/files/BN+391-2019.pdf';
$se = 'https://jdih.bkn.go.id/common/dokumen/SURAT%20EDARAN%20NO.3%20TAHUN%202019%20-%20PETUNJUK%20TEKNIS%20PERATURAN%20BKN%20NOMOR%205%20TAHUN%202019%20TENTANG%20TATA%20CARA%20PELAKSANAAN%20MUTASI.pdf';
$law = 'UU 20/2023 tentang ASN; PP 11/2017 jo. PP 17/2020 tentang Manajemen PNS; Peraturan BKN 5/2019 tentang Tata Cara Pelaksanaan Mutasi; SE Kepala BKN 3/SE/VIII/2019';
$berkas = [
    'Berstatus PNS (bukan CPNS).',
    'Surat permohonan mutasi dari PNS yang bersangkutan.',
    'Surat usul mutasi dari Pejabat Pembina Kepegawaian (PPK) instansi penerima, menyebut jabatan yang akan diduduki.',
    'Surat persetujuan mutasi dari PPK instansi asal, menyebut jabatan yang akan diduduki.',
    'Analisis jabatan dan analisis beban kerja untuk jabatan yang akan diduduki.',
    'Surat pernyataan instansi asal bahwa PNS tidak sedang dalam proses/menjalani hukuman disiplin dan/atau proses peradilan (ditandatangani PPK atau pejabat kepegawaian paling rendah JPT Pratama).',
    'Salinan/fotokopi sah SK pangkat dan/atau jabatan terakhir.',
    'Salinan/fotokopi sah penilaian kinerja (SKP) bernilai baik 2 tahun terakhir.',
    'Surat pernyataan tidak sedang tugas belajar atau ikatan dinas.',
    'Surat keterangan bebas temuan dari Inspektorat instansi asal (bila diminta).',
];
$free = 'Tidak ada biaya resmi';
$minimum = 'Mutasi dilakukan paling singkat 2 tahun dan paling lama 5 tahun (Peraturan BKN 5/2019 Pasal 2 ayat (3)).';

return [
    'agency' => 'BKPSDM Kabupaten Empat Lawang',
    'checked_at' => '2026-11-21',
    'items' => [
    [
        'title' => 'Mutasi PNS antar Perangkat Daerah di Lingkungan Pemkab Empat Lawang',
        'slug' => 'mutasi-pns-dalam-kabupaten',
        'requirements' => $list([
            'Berstatus PNS Pemerintah Kabupaten Empat Lawang.',
            'Surat permohonan (bila atas permintaan sendiri) dengan alasan dan jabatan tujuan.',
            'Salinan SK pangkat/jabatan terakhir dan penilaian kinerja (SKP) bernilai baik 2 tahun terakhir.',
            'Tidak sedang dalam proses/menjalani hukuman disiplin atau proses peradilan.',
            'Tersedia kebutuhan jabatan (sesuai analisis jabatan dan beban kerja) di perangkat daerah tujuan.',
        ]),
        'steps' => $list([
            'Unit kepegawaian (BKPSDM) menyusun perencanaan mutasi.',
            'Perencanaan dibahas Tim Penilai Kinerja PNS (atau Baperjakat bila tim belum terbentuk) untuk pertimbangan.',
            'BKPSDM mengusulkan mutasi kepada Bupati selaku PPK.',
            'Bupati menetapkan keputusan pengangkatan PNS dalam jabatan di perangkat daerah tujuan.',
            $minimum,
        ]),
        'cost' => $free,
        'duration' => 'Mengikuti jadwal pertimbangan Tim Penilai Kinerja/Baperjakat',
        'legal_basis' => $law . ' (Pasal 5 dan Pasal 10 Peraturan BKN 5/2019).',
        'source_url' => $perbkn,
        'keywords' => 'mutasi pns, pindah opd, pindah dinas, pindah tugas asn, pindah kantor, rotasi pegawai',
    ],
    [
        'title' => 'Mutasi PNS antar Kabupaten/Kota dalam Provinsi Sumatera Selatan',
        'slug' => 'mutasi-pns-antar-kabupaten-sumsel',
        'requirements' => $list($berkas),
        'steps' => $list([
            'PNS mengajukan permohonan; PPK (Bupati/Wali Kota) instansi penerima membuat usul mutasi kepada PPK instansi asal.',
            'PPK instansi asal memberikan surat persetujuan mutasi (2 rangkap: untuk PPK penerima dan PNS).',
            'PPK instansi penerima mengusulkan ke Kantor Regional BKN melalui Gubernur Sumatera Selatan (lewat aplikasi kepegawaian BKN) untuk pertimbangan teknis — paling lama 15 hari kerja.',
            'Gubernur menetapkan keputusan mutasi.',
            'PPK penerima menetapkan pengangkatan dalam jabatan dan PPK asal memberhentikan dari jabatan, paling lama 30 hari sejak keputusan mutasi.',
        ]),
        'cost' => $free,
        'duration' => 'Pertimbangan teknis BKN paling lama 15 hari kerja; SK jabatan paling lama 30 hari setelah keputusan mutasi',
        'legal_basis' => $law . ' (Pasal 3, 4, 6 Peraturan BKN 5/2019).',
        'source_url' => $perbkn,
        'keywords' => 'mutasi antar kabupaten, pindah kabupaten, pindah tugas pns, mutasi pns sumsel, surat lolos butuh, surat melepas, pindah ikut suami, pindah ikut istri',
    ],
    [
        'title' => 'Mutasi PNS antar Provinsi atau ke Kabupaten/Kota di Provinsi Lain',
        'slug' => 'mutasi-pns-antar-provinsi',
        'requirements' => $list($berkas),
        'steps' => $list([
            'PPK instansi penerima (di provinsi lain) membuat usul mutasi kepada PPK instansi asal.',
            'PPK instansi asal memberikan surat persetujuan mutasi.',
            'PPK instansi penerima mengusulkan ke Menteri Dalam Negeri melalui gubernur, tembusan Kepala BKN, untuk pertimbangan teknis BKN (paling lama 15 hari kerja).',
            'Menteri Dalam Negeri menetapkan keputusan mutasi.',
            'PPK penerima menetapkan pengangkatan dalam jabatan (paling lama 30 hari sejak keputusan mutasi).',
        ]),
        'cost' => $free,
        'duration' => 'Pertimbangan teknis BKN paling lama 15 hari kerja; keputusan Mendagri setelahnya',
        'legal_basis' => $law . ' (Pasal 3, 4, 7 Peraturan BKN 5/2019).',
        'source_url' => $se,
        'keywords' => 'mutasi antar provinsi, pindah provinsi, pindah luar sumsel, mutasi pns luar daerah, surat lolos butuh',
    ],
    [
        'title' => 'Mutasi PNS Daerah ke Instansi Pusat (atau Sebaliknya)',
        'slug' => 'mutasi-pns-instansi-pusat',
        'requirements' => $list($berkas),
        'steps' => $list([
            'PPK instansi penerima membuat usul mutasi kepada PPK instansi asal.',
            'PPK instansi asal memberikan surat persetujuan mutasi.',
            'Ke instansi pusat: PPK pusat mengusulkan ke Kepala BKN yang menetapkan keputusan mutasi. Dari pusat ke daerah: PPK daerah mengusulkan ke Kepala Kantor Regional BKN.',
            'BKN memverifikasi kebutuhan jabatan; keputusan paling lama 15 hari kerja.',
            'PPK penerima menetapkan pengangkatan dalam jabatan.',
        ]),
        'cost' => $free,
        'duration' => 'Keputusan BKN paling lama 15 hari kerja sejak usul diterima',
        'legal_basis' => $law . ' (Pasal 3, 4, 8 Peraturan BKN 5/2019).',
        'source_url' => $se,
        'keywords' => 'mutasi ke pusat, pindah kementerian, pindah instansi pusat, mutasi bkn',
    ],
    [
        'title' => 'Pindah Tugas PPPK',
        'slug' => 'pindah-tugas-pppk',
        'requirements' => $list([
            'PPPK terikat perjanjian kerja pada instansi dan jabatan sesuai formasi seleksinya.',
            'Pindah antar unit kerja dalam satu instansi (mis. antar sekolah/puskesmas di Pemkab Empat Lawang) dimungkinkan sepanjang jabatannya tetap sama, atas kebutuhan organisasi.',
            'Pindah ke instansi atau daerah lain belum diperbolehkan; jalurnya mengikuti seleksi PPPK/CPNS baru pada formasi yang diinginkan.',
        ]),
        'steps' => $list([
            'Tanyakan ketersediaan kebutuhan jabatan di unit tujuan ke BKPSDM.',
            'Perpindahan antar unit ditetapkan oleh PPK (Bupati) berdasarkan kebutuhan organisasi.',
        ]),
        'cost' => $free,
        'duration' => 'Tergantung kebutuhan organisasi',
        'legal_basis' => 'UU 20/2023 tentang ASN; PP 49/2018 tentang Manajemen PPPK; kebijakan Kementerian PANRB (belum ada peraturan teknis mutasi PPPK yang ditemukan).',
        'source_url' => 'https://lombokpost.jawapos.com/nasional/1506782327/pppk-kini-bisa-mutasi-tanpa-harus-seleksi-ulang-ini-syaratnya',
        'keywords' => 'pppk pindah, mutasi pppk, pindah tugas pppk, p3k pindah, guru pppk pindah, pppk paruh waktu',
    ],
]];
