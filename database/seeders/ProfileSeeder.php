<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

/**
 * Initial content of the Profil PPID pages.
 *
 * Sources: ppid.empatlawangkab.go.id (Tentang, Visi & Misi, Maklumat), UU 14/2008,
 * Permendagri 2/2026 Pasal 56–63 (replaces Permendagri 3/2017), Perbup Empat Lawang 3/2018.
 * firstOrCreate: never overwrites content already edited by an admin.
 */
class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        Profile::firstOrCreate(['slug' => 'tentang-ppid'], [
            'title' => 'Tentang PPID',
            'content' => <<<'HTML'
<p>Informasi merupakan kebutuhan pokok setiap orang bagi pengembangan pribadi dan lingkungan sosialnya. Hak memperoleh informasi merupakan hak asasi manusia, dan keterbukaan informasi publik membuat penyelenggaraan negara makin dapat diawasi publik serta dipertanggungjawabkan.</p>
<p>Pemberlakuan Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik pada 30 April 2010 merupakan momentum penting dalam mendorong keterbukaan di Indonesia, khususnya di Kabupaten Empat Lawang. Setiap Badan Publik wajib menyediakan dan melayani permohonan informasi publik secara cepat, tepat waktu, biaya ringan, dan cara sederhana.</p>
<h3>Pengertian PPID</h3>
<p>Pejabat Pengelola Informasi dan Dokumentasi (PPID) adalah pejabat yang bertanggung jawab di bidang penyimpanan, pendokumentasian, penyediaan, dan/atau pelayanan informasi publik. PPID Pemerintah Kabupaten Empat Lawang merupakan ujung tombak pelayanan informasi kepada masyarakat, dibantu PPID Pelaksana pada setiap Perangkat Daerah.</p>
<h3>Dasar Hukum</h3>
<ol>
<li>Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik;</li>
<li>Undang-Undang Nomor 25 Tahun 2009 tentang Pelayanan Publik;</li>
<li>Undang-Undang Nomor 43 Tahun 2009 tentang Kearsipan;</li>
<li>Peraturan Pemerintah Nomor 61 Tahun 2010 tentang Pelaksanaan Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik;</li>
<li>Peraturan Komisi Informasi Nomor 1 Tahun 2021 tentang Standar Layanan Informasi Publik;</li>
<li>Peraturan Komisi Informasi Nomor 1 Tahun 2013 tentang Prosedur Penyelesaian Sengketa Informasi Publik;</li>
<li>Peraturan Menteri Dalam Negeri Nomor 2 Tahun 2026 tentang Pengelolaan Layanan Informasi Publik di Kementerian Dalam Negeri, Pemerintah Daerah, dan Pemerintah Desa;</li>
<li>Peraturan Bupati Empat Lawang Nomor 3 Tahun 2018 tentang Kedudukan, Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Dinas Komunikasi dan Informatika Kabupaten Empat Lawang.</li>
</ol>
<h3>Maklumat Pelayanan Informasi Publik</h3>
<p>PPID Kabupaten Empat Lawang menyatakan komitmen untuk:</p>
<ol>
<li>Memberikan pelayanan informasi yang prima berdasarkan Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik;</li>
<li>Memberikan kemudahan kepada publik dalam mendapatkan informasi secara sederhana dan berbiaya ringan;</li>
<li>Menyediakan dan memberikan informasi publik yang dikuasai secara akurat, benar, dan tidak menyesatkan;</li>
<li>Memberikan jawaban permohonan informasi publik dan tanggapan pernyataan keberatan sesuai jangka waktu yang telah ditetapkan;</li>
<li>Menyediakan Daftar Informasi Publik untuk informasi yang wajib disediakan dan diumumkan;</li>
<li>Bertindak proaktif dalam memenuhi kebutuhan informasi masyarakat;</li>
<li>Menyiapkan sarana dan prasarana yang inklusif, nyaman, dan tertata baik;</li>
<li>Bersikap adil, tidak diskriminatif, dan berperilaku sopan santun dalam memberikan layanan informasi publik;</li>
<li>Tidak melakukan pungutan biaya yang tidak sesuai dengan ketentuan peraturan perundang-undangan;</li>
<li>Melaporkan hasil kinerja atas pelaksanaan pelayanan informasi publik.</li>
</ol>
HTML,
            'type' => 'general',
        ]);

        // Layout must stay "<h3>Visi</h3>…<h3>Misi</h3><ol>…</ol>": parsed by visi_misi() in app/helpers.php.
        Profile::firstOrCreate(['slug' => 'visi-misi'], [
            'title' => 'Visi dan Misi',
            'content' => <<<'HTML'
<h3>Visi</h3>
<p>Terwujudnya pelayanan informasi yang transparan dan akuntabel untuk memenuhi hak pemohon informasi sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.</p>
<h3>Misi</h3>
<ol>
<li>Meningkatkan pengelolaan dan pelayanan informasi yang berkualitas, benar, dan bertanggung jawab.</li>
<li>Membangun dan mengembangkan sistem penyediaan dan layanan informasi.</li>
<li>Meningkatkan dan mengembangkan kompetensi dan kualitas SDM dalam bidang pelayanan informasi.</li>
<li>Mewujudkan keterbukaan informasi Pemerintah Kabupaten Empat Lawang dengan proses yang cepat, tepat, mudah, dan sederhana.</li>
</ol>
HTML,
            'type' => 'visi_misi',
        ]);

        Profile::firstOrCreate(['slug' => 'struktur-organisasi'], [
            'title' => 'Struktur Organisasi',
            'content' => <<<'HTML'
<p>Susunan lembaga pengelola informasi publik Pemerintah Kabupaten Empat Lawang mengacu pada Peraturan Menteri Dalam Negeri Nomor 2 Tahun 2026 (Pasal 56) dan ditetapkan dengan Keputusan Bupati.</p>
<table>
<thead><tr><th>Unsur</th><th>Dijabat oleh</th><th>Bertanggung jawab kepada</th></tr></thead>
<tbody>
<tr><td>Pembina</td><td>Bupati Empat Lawang</td><td>—</td></tr>
<tr><td>Atasan PPID</td><td>Sekretaris Daerah Kabupaten Empat Lawang</td><td>Pembina</td></tr>
<tr><td>Penanggung Jawab</td><td>Kepala Perangkat Daerah masing-masing</td><td>Pembina melalui Atasan PPID</td></tr>
<tr><td>PPID (PPID Utama)</td><td>Kepala Dinas Komunikasi dan Informatika Kabupaten Empat Lawang</td><td>Pembina melalui Atasan PPID</td></tr>
<tr><td>Tim Pertimbangan</td><td>Pejabat bidang hukum, komunikasi dan/atau pelayanan informasi yang ditunjuk Atasan PPID</td><td>Atasan PPID</td></tr>
<tr><td>PPID Pelaksana</td><td>Kepala Bagian Organisasi Sekretariat Daerah dan Sekretaris pada setiap Perangkat Daerah</td><td>Penanggung Jawab</td></tr>
<tr><td>PPID Pelaksana UPTD</td><td>Kepala Unit Pelaksana Teknis Daerah (bila ditetapkan)</td><td>Penanggung Jawab melalui PPID Pelaksana</td></tr>
<tr><td>Petugas Pelayanan Informasi Publik</td><td>Pejabat struktural, fungsional atau staf yang ditunjuk</td><td>PPID / PPID Pelaksana sesuai penugasan</td></tr>
</tbody>
</table>
HTML,
            'type' => 'struktur',
        ]);

        Profile::firstOrCreate(['slug' => 'tugas-fungsi'], [
            'title' => 'Tugas dan Fungsi',
            'content' => <<<'HTML'
<p>Tugas dan wewenang berikut mengacu pada Peraturan Menteri Dalam Negeri Nomor 2 Tahun 2026 Pasal 59 (PPID) dan Pasal 61 (PPID Pelaksana).</p>
<h3>Tugas PPID Utama</h3>
<ol>
<li>Menyusun dan melaksanakan kebijakan layanan informasi publik;</li>
<li>Menyusun dan menyampaikan laporan pelaksanaan kebijakan layanan informasi publik;</li>
<li>Mengoordinasikan dan mengonsolidasikan pengumpulan, penyimpanan, pendokumentasian, pengumuman, penyediaan, dan pelayanan informasi publik;</li>
<li>Melakukan verifikasi permohonan informasi publik;</li>
<li>Menentukan informasi publik yang dapat diakses publik dan layak dipublikasikan;</li>
<li>Melakukan pengujian konsekuensi atas informasi publik yang akan dikecualikan;</li>
<li>Mengelola dan memutakhirkan Daftar Informasi Publik;</li>
<li>Memantau, mengevaluasi, membina, dan mengawasi pelaksanaan kebijakan teknis oleh PPID Pelaksana dan Petugas Pelayanan Informasi Publik;</li>
<li>Memberikan jawaban atas permintaan informasi publik;</li>
<li>Menugaskan PPID Pelaksana menyusun Pertanyaan Umum beserta jawabannya;</li>
<li>Mengusulkan tim penyelesaian sengketa informasi publik kepada Atasan PPID;</li>
<li>Menyosialisasikan pengelolaan layanan informasi publik kepada internal Pemerintah Daerah dan masyarakat.</li>
</ol>
<h3>Wewenang PPID Utama</h3>
<ol>
<li>Menetapkan kebijakan teknis dan laporan layanan informasi publik;</li>
<li>Meminta dan memperoleh informasi publik serta klarifikasi dari PPID Pelaksana;</li>
<li>Memutuskan suatu informasi dapat diakses publik atau tidak berdasarkan uji konsekuensi, dengan persetujuan Atasan PPID;</li>
<li>Menolak permintaan informasi yang dikecualikan dengan pertimbangan tertulis, dengan persetujuan Atasan PPID;</li>
<li>Menugaskan PPID Pelaksana menyusun, mengelola, dan memutakhirkan Daftar Informasi Publik;</li>
<li>Menetapkan Pertanyaan Umum beserta jawabannya.</li>
</ol>
<h3>Tugas PPID Pelaksana</h3>
<ol>
<li>Membantu PPID melaksanakan tanggung jawab, tugas, dan kewenangannya;</li>
<li>Menunjuk Petugas Pelayanan Informasi Publik pada setiap unit kerja;</li>
<li>Mengoordinasikan dan meneruskan permintaan informasi publik kepada PPID Pelaksana UPTD sesuai kewenangannya;</li>
<li>Menyusun usulan Pertanyaan Umum beserta jawabannya kepada PPID;</li>
<li>Mengoordinasikan usulan Daftar Informasi Publik dan informasi yang dikecualikan di Perangkat Daerah;</li>
<li>Memberikan informasi kepada PPID mengenai status dan percepatan tindak lanjut permintaan informasi;</li>
<li>Menyusun laporan layanan informasi publik setiap 3 (tiga) bulan kepada Penanggung Jawab dan PPID;</li>
<li>Menyediakan sarana layanan informasi publik berbasis elektronik dan nonelektronik.</li>
</ol>
<h3>Wewenang PPID Pelaksana</h3>
<ol>
<li>Meminta PPID Pelaksana UPTD menanggapi dan menyediakan informasi publik yang diminta;</li>
<li>Mengoordinasikan percepatan tindak lanjut permintaan informasi, tanggapan keberatan, dan penyelesaian sengketa;</li>
<li>Mengoordinasikan penyusunan Daftar Informasi Publik dan informasi yang dikecualikan;</li>
<li>Mengoordinasikan pemantauan, evaluasi, pembinaan, dan pengawasan kinerja pengelolaan informasi publik.</li>
</ol>
HTML,
            'type' => 'tugas_fungsi',
        ]);
    }
}
