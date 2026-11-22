<?php

// Demo WhatsApp inbox (WaInboxDemoSeeder): citizens' chats with PPID Empat Lawang.
// Scenario: label, handoff (bot could not answer → officer), thread [[dir, text, sender]], sender 'bot' | 'officer' | null (citizen).
// {name} = citizen's first name, {village} = a village/kelurahan, {year} = year of the chat.

return [
    'names' => [
        'Ahmad Fauzi', 'Siti Aminah', 'Rudi Hartono', 'Nurhayati', 'Dedi Kurniawan', 'Yuliana', 'Hendra Saputra', 'Rina Marlina',
        'Agus Salim', 'Sri Wahyuni', 'Bambang Irawan', 'Fitri Handayani', 'Joni Iskandar', 'Lestari', 'Rahmat Hidayat', 'Desi Ratnasari',
        'Mulyadi', 'Eka Pratiwi', 'Syahrial', 'Wulan Sari', 'Irwan Effendi', 'Novi Anggraini', 'Zulkarnain', 'Maya Puspita',
        'Hasan Basri', 'Ratna Dewi', 'Feri Gunawan', 'Yuni Kartika', 'Arman Jaya', 'Indah Permata', 'Herman', 'Dian Lestari',
        'Saparudin', 'Elvi Susanti', 'Romi Pratama', 'Nia Ramadhani', 'Edi Suhendra', 'Sumarni', 'Taufik Hidayat', 'Ayu Wandira',
        'Darmawan', 'Leni Marlina', 'Firdaus', 'Juwita', 'Sopian', 'Rosmala', 'Andi Wijaya', 'Ernawati', 'Kms. Yusuf', 'Halimah',
        'Ujang Suparman', 'Mardiana', 'Ilham Akbar', 'Nopi Yanti', 'Robi Sanjaya', 'Asmawati', 'Yopi Kurniadi', 'Putri Amelia',
    ],
    'villages' => [
        'Tebing Tinggi', 'Pendopo', 'Muara Pinang', 'Lintang Kanan', 'Ulu Musi', 'Talang Padang', 'Pasemah Air Keruh', 'Sikap Dalam',
        'Saling', 'Pendopo Barat', 'Kel. Pasar Tebing Tinggi', 'Desa Jokoh', 'Desa Batu Raja', 'Desa Tanjung Kupang', 'Desa Muara Danau',
    ],
    'scenarios' => [
        ['label' => 'umum', 'thread' => [
            ['in', 'Assalamualaikum, kantor PPID buka jam berapa ya?'],
            ['out', "Wa'alaikumsalam, Bapak/Ibu. Layanan PPID Kabupaten Empat Lawang buka Senin–Kamis 08.00–16.00 WIB dan Jumat 08.00–11.30 WIB. Sabtu, Minggu dan hari libur tutup.", 'bot'],
            ['in', 'Baik terima kasih'],
        ]],
        ['label' => 'umum', 'thread' => [
            ['in', 'Kak alamat kantor PPID dimana? Saya dari {village}'],
            ['out', 'Kantor PPID Kabupaten Empat Lawang berada di Kompleks Perkantoran Pemkab Empat Lawang, Tebing Tinggi (Dinas Kominfo). Peta lokasi: https://ppid.empatlawangkab.go.id/kontak', 'bot'],
        ]],
        ['label' => 'umum', 'thread' => [
            ['in', 'Permisi, minta informasi ke PPID itu bayar tidak?'],
            ['out', 'Layanan informasi publik di PPID GRATIS, Bapak/Ibu. Biaya hanya bila meminta salinan cetak dalam jumlah banyak (biaya penggandaan), selebihnya bisa dikirim lewat email/WA.', 'bot'],
            ['in', 'Oke mantap 👍'],
        ]],
        ['label' => 'permohonan', 'handoff' => 'Permintaan dokumen / permohonan informasi', 'thread' => [
            ['in', 'Selamat pagi. Saya mahasiswa UIN Raden Fatah, mau minta data APBD Empat Lawang {year} untuk skripsi. Bisa dikirim lewat email?'],
            ['out', 'Selamat pagi, terima kasih sudah menghubungi PPID. Ringkasan APBD {year} bisa diunduh di menu Informasi Berkala. Untuk rincian, silakan isi Permohonan Informasi di https://ppid.empatlawangkab.go.id/permohonan-informasi dan lampirkan KTP/KTM, nanti kami kirim ke email.', 'officer'],
            ['in', 'Sudah saya isi pak, atas nama {name}'],
            ['out', 'Baik, sudah kami terima. Dokumen akan dikirim maksimal 10 hari kerja. Terima kasih.', 'officer'],
        ], 'notes' => 'Mahasiswa, minta APBD untuk skripsi — sudah isi form online.'],
        ['label' => 'permohonan', 'handoff' => 'Permintaan dokumen / permohonan informasi', 'thread' => [
            ['in', 'Pak mau tanya, LKjIP Dinas Kesehatan tahun {year} ada di website tidak?'],
            ['out', 'Selamat siang. LKjIP perangkat daerah ada di menu Informasi Publik > Berkala. Bila LKjIP Dinkes belum tersedia, kami teruskan ke PPID Pelaksana Dinkes. Mohon tunggu kabarnya ya.', 'officer'],
            ['in', 'Siap pak ditunggu'],
        ]],
        ['label' => 'permohonan', 'handoff' => 'Permintaan dokumen / permohonan informasi', 'thread' => [
            ['in', 'Saya dari LSM Pemantau Anggaran, minta salinan DPA Dinas PUPR tahun {year} dan daftar paket pekerjaan jalan.'],
            ['in', 'Mohon dibalas'],
        ], 'notes' => 'LSM — minta DPA PUPR + paket jalan. Teruskan ke PPID Pelaksana PUPR.'],
        ['label' => 'permohonan', 'handoff' => 'Permintaan dokumen / permohonan informasi', 'thread' => [
            ['in', 'Assalamualaikum min, ada data jumlah penduduk per kecamatan Empat Lawang tidak? Untuk tugas sekolah'],
            ['out', "Wa'alaikumsalam. Data penduduk per kecamatan bisa dilihat di publikasi BPS \"Kabupaten Empat Lawang Dalam Angka\" (empatlawangkab.bps.go.id). Semoga membantu tugasnya ya.", 'officer'],
            ['in', 'Makasih banyak kak'],
        ]],
        ['label' => 'permohonan', 'thread' => [
            ['in', 'Pak, bisa minta SK Bupati tentang tim PPID terbaru?'],
            ['out', 'SK PPID ada di menu Profil > Dasar Hukum dan Informasi Publik > Setiap Saat. Bila belum ketemu, silakan ajukan lewat form Permohonan Informasi.', 'bot'],
        ]],
        ['label' => 'keberatan', 'handoff' => 'Keberatan / sengketa informasi', 'thread' => [
            ['in', 'Saya sudah ajukan permohonan informasi dari bulan lalu tapi belum ada jawaban. Saya mau ajukan keberatan.'],
            ['out', 'Mohon maaf atas keterlambatannya, Bapak/Ibu. Keberatan bisa diajukan lewat menu Pengajuan Keberatan di website (paling lambat 30 hari kerja sejak jawaban/tenggat). Boleh kami minta nomor registrasi permohonannya untuk kami cek?', 'officer'],
            ['in', 'Nomor registrasinya REG-{year}-0{n}'],
            ['out', 'Terima kasih, sudah kami cek dan teruskan ke atasan PPID. Jawaban akan dikirim paling lambat 7 hari kerja.', 'officer'],
        ], 'notes' => 'Keberatan karena permohonan tidak dijawab. Sudah diteruskan ke Atasan PPID.'],
        ['label' => 'keberatan', 'handoff' => 'Keberatan / sengketa informasi', 'thread' => [
            ['in', 'Jawaban permohonan saya tidak lengkap, data yang dikirim cuma ringkasan. Bagaimana cara keberatan?'],
        ]],
        ['label' => 'aduan', 'handoff' => 'Aduan / laporan warga', 'thread' => [
            ['in', 'Lapor pak, jalan di {village} rusak parah sudah 2 tahun tidak diperbaiki. Kalau hujan tidak bisa dilewati motor.'],
            ['out', 'Terima kasih laporannya. Untuk kerusakan jalan, silakan laporkan juga lewat SP4N-LAPOR! (lapor.go.id) agar tercatat dan diteruskan ke Dinas PUPR. Boleh kirim foto dan titik lokasinya?', 'officer'],
            ['in', 'Ini fotonya pak, lokasinya dekat masjid desa'],
            ['out', 'Sudah kami terima dan teruskan ke PPID Pelaksana Dinas PUPR. Terima kasih atas kepeduliannya.', 'officer'],
        ], 'notes' => 'Aduan jalan rusak, foto sudah dikirim. Diteruskan ke PUPR.'],
        ['label' => 'aduan', 'handoff' => 'Aduan / laporan warga', 'thread' => [
            ['in', 'Mau mengadu, lampu jalan di depan rumah saya mati sudah sebulan'],
        ]],
        ['label' => 'aduan', 'handoff' => 'Aduan / laporan warga', 'thread' => [
            ['in', 'Pak saya mau lapor ada pungutan waktu urus surat di kantor desa. Lapornya kemana ya?'],
            ['out', 'Untuk dugaan pungutan liar, laporan bisa disampaikan ke Inspektorat Kabupaten Empat Lawang, Ombudsman RI Perwakilan Sumsel, atau SP4N-LAPOR! (lapor.go.id). Identitas pelapor dirahasiakan.', 'officer'],
            ['in', 'Baik pak terima kasih infonya'],
        ]],
        ['label' => 'umum', 'thread' => [
            ['in', 'Syarat bikin KTP baru apa aja ya?'],
            ['out', 'KTP-el dilayani Disdukcapil Kabupaten Empat Lawang. Syarat umumnya: sudah berusia 17 tahun/sudah menikah dan fotokopi Kartu Keluarga. Daftar lengkap: https://ppid.empatlawangkab.go.id/persyaratan', 'bot'],
        ]],
        ['label' => 'umum', 'thread' => [
            ['in', 'Kak mau tanya syarat urus akta kelahiran anak'],
            ['out', 'Akta kelahiran diurus di Disdukcapil: surat keterangan lahir dari bidan/RS, KK, KTP kedua orang tua, buku nikah/akta perkawinan. Rincian ada di https://ppid.empatlawangkab.go.id/persyaratan', 'bot'],
            ['in', 'Kalau buku nikah hilang gimana kak?'],
            ['out', 'Bisa minta duplikat buku nikah di KUA tempat menikah dulu, atau akta dicatat dengan keterangan "anak seorang ibu" sesuai aturan Disdukcapil. Sebaiknya konfirmasi langsung ke Disdukcapil Empat Lawang ya.', 'officer'],
        ]],
        ['label' => 'umum', 'thread' => [
            ['in', 'Pak ada info penerimaan PPPK Empat Lawang tahun ini?'],
            ['out', 'Informasi seleksi ASN/PPPK diumumkan resmi oleh BKPSDM Kabupaten Empat Lawang dan di sscasn.bkn.go.id. Mohon waspada penipuan yang menjanjikan kelulusan dengan bayaran.', 'officer'],
        ]],
        ['label' => 'umum', 'handoff' => 'Bot tidak yakin dengan maksud pertanyaan', 'thread' => [
            ['in', 'Bansos PKH bulan ini sudah cair belum pak? Saya di {village}'],
            ['out', 'Mohon maaf, jadwal pencairan PKH ditentukan Kemensos. Status penerima bisa dicek di cekbansos.kemensos.go.id atau tanyakan ke pendamping PKH / Dinas Sosial Empat Lawang.', 'officer'],
            ['in', 'Oh iya pak terima kasih'],
        ]],
        ['label' => 'umum', 'handoff' => 'Bot tidak yakin dengan maksud pertanyaan', 'thread' => [
            ['in', 'Pak, beasiswa Empat Lawang Cerdas tahun ini dibuka kapan?'],
        ]],
        ['label' => 'umum', 'thread' => [
            ['in', 'Cara urus izin usaha warung kecil gimana ya? NIB itu apa'],
            ['out', 'NIB (Nomor Induk Berusaha) diurus online dan gratis di oss.go.id dengan NIK dan email. Untuk usaha mikro cukup NIB. Bantuan bisa ke DPMPTSP Empat Lawang. Rincian: https://ppid.empatlawangkab.go.id/persyaratan', 'bot'],
            ['in', 'Wah gratis ya, makasih min'],
        ]],
        ['label' => 'umum', 'handoff' => 'Bot tidak yakin dengan maksud pertanyaan', 'thread' => [
            ['in', 'Halo'],
            ['out', 'Halo, selamat datang di layanan WhatsApp PPID Kabupaten Empat Lawang. Ada yang bisa kami bantu?', 'bot'],
            ['in', 'Saya mau tanya soal tanah warisan yang belum dibagi, bisa dibantu?'],
            ['out', 'Untuk sengketa/pembagian waris, silakan berkonsultasi ke Kantor Pertanahan (BPN) Empat Lawang untuk pendaftaran tanahnya, dan ke Pengadilan Agama Lahat bila ada perselisihan. PPID melayani permintaan informasi publik badan publik.', 'officer'],
        ]],
        ['label' => 'umum', 'thread' => [
            ['in', 'Min jadwal sholat ied di lapangan pemkab jam berapa?'],
            ['out', 'Mohon maaf, informasi tersebut belum tersedia di kami. Silakan pantau pengumuman resmi di website empatlawangkab.go.id atau media sosial Pemkab Empat Lawang.', 'bot'],
        ]],
        ['label' => 'umum', 'thread' => [
            ['in', 'Terima kasih pelayanannya cepat 🙏'],
            ['out', 'Sama-sama, Bapak/Ibu. Senang bisa membantu 🙏', 'bot'],
        ]],
        ['label' => 'permohonan', 'handoff' => 'Permintaan dokumen / permohonan informasi', 'thread' => [
            ['in', 'Selamat siang, saya wartawan media online. Minta data realisasi Dana Desa {year} per kecamatan.'],
            ['out', 'Selamat siang. Data realisasi Dana Desa dikelola Dinas PMD. Silakan ajukan permohonan informasi resmi lewat website agar tercatat, nanti kami koordinasikan dengan PPID Pelaksana PMD.', 'officer'],
            ['in', 'Baik sudah saya ajukan barusan'],
        ]],
        ['label' => 'umum', 'failed' => true, 'thread' => [
            ['in', 'Pak kapan pembuatan SIM keliling ke {village}?'],
            ['out', 'Jadwal SIM keliling ditetapkan Satlantas Polres Empat Lawang, biasanya diumumkan di media sosial Polres. Silakan pantau di sana ya.', 'officer'],
        ]],
    ],
];
