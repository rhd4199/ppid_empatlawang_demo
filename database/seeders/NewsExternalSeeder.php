<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class NewsExternalSeeder extends Seeder
{
    /**
     * Seeds the 5 latest posts from empatlawangkab.go.id/blog as of 2026-09-07,
     * so the CMS has real sample content instead of an empty list.
     * Source: empatlawangkab.go.id (Kominfo Empat Lawang), reproduced as-is.
     */
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Semangat Kemerdekaan Membara, Bupati Empat Lawang Hadiri Lomba Drumband HUT RI ke-81',
                'image' => 'news1.webp',
                'published_at' => '2026-08-15',
                'content' => 'EMPAT LAWANG – Bupati Empat Lawang, Dr. H. Joncik Muhammad, S.Si., S.H., M.H., M.M., turut hadir dan menyaksikan secara langsung perlombaan drumband dalam rangka memeriahkan Hari Ulang Tahun (HUT) Republik Indonesia ke-81 di Kabupaten Empat Lawang, Sabtu (15/8/2026). Kegiatan yang dipusatkan di Lapangan Sepak Bola Talang Jawa tersebut berlangsung meriah. Antusiasme peserta dan masyarakat terlihat dari sorak-sorai serta semangat yang mengiringi jalannya perlombaan.</p><p>Kehadiran Bupati Joncik Muhammad bersama Ketua TP PKK Kabupaten Empat Lawang, Hj. Hepy Safriani, SKM., M.Kes., semakin menambah semarak kegiatan. Turut hadir Wakil Bupati Empat Lawang Arifai, S.H., beserta istri, yang disambut Sekretaris Daerah Fauzan Khoiri D., AP., M.M., jajaran SKPD, serta Forkopimda Kabupaten Empat Lawang.</p><p>Dalam kesempatan tersebut, Joncik Muhammad berharap perlombaan drumband tidak hanya menjadi hiburan dalam rangka memperingati HUT RI, tetapi juga mampu menumbuhkan semangat persatuan, sportivitas, dan kecintaan terhadap Tanah Air, khususnya di kalangan generasi muda. Menurutnya, kegiatan seperti ini juga menjadi momentum untuk memperkuat kebersamaan dan semangat gotong royong dalam mewujudkan Kabupaten Empat Lawang yang semakin baik dan berprestasi.</p><p>&ldquo;Semoga kegiatan seperti ini dapat terus dilaksanakan sebagai sarana menginspirasi generasi muda agar selalu menjaga nilai-nilai perjuangan dan terus menebar semangat kemerdekaan,&rdquo; ujar Joncik.</p><p>Perlombaan drumband tersebut menjadi salah satu rangkaian kegiatan untuk menyemarakkan HUT Kemerdekaan RI ke-81 sekaligus mempererat kebersamaan antara pemerintah daerah, peserta, dan masyarakat Kabupaten Empat Lawang. (Kominfo)',
            ],
            [
                'title' => 'Jelang HUT ke-81 RI, Pemkab Empat Lawang Tertibkan Pedagang di Jalan Abu Bakar Di Tebing Tinggi',
                'image' => 'news2.webp',
                'published_at' => '2026-08-12',
                'content' => 'EMPAT LAWANG – Pemerintah Kabupaten (Pemkab) Empat Lawang melakukan penataan terhadap aktivitas pedagang yang berjualan di sepanjang Jalan Abu Bakar Din, Kecamatan Tebing Tinggi, khususnya pedagang yang menggunakan trotoar dan badan jalan. Penataan dilakukan untuk menciptakan kawasan pasar yang lebih tertib dan rapi menjelang peringatan Hari Ulang Tahun (HUT) ke-81 Kemerdekaan Republik Indonesia.</p><p>Langkah tersebut juga bertujuan memastikan akses jalan tetap lancar selama pelaksanaan berbagai kegiatan menyambut 17 Agustus, termasuk lomba gerak jalan dan penampilan drum band.</p><p>Kepala Dinas Perindustrian dan Perdagangan (Disperindag) Kabupaten Empat Lawang, Sopian Haris, mengatakan penataan dilakukan agar para pedagang tetap dapat menjalankan aktivitas perdagangan tanpa mengganggu fungsi trotoar maupun arus lalu lintas.</p><p>&ldquo;Kita melakukan penataan agar kawasan pasar terlihat lebih rapi dan tertib. Pedagang yang menggunakan trotoar maupun badan jalan akan ditata sehingga tidak mengganggu pengguna jalan,&rdquo; kata Sopian Haris, Rabu (12/8/2026).</p><p>Menurutnya, penataan tersebut merupakan bagian dari upaya pemerintah menciptakan lingkungan pasar yang nyaman, baik bagi pedagang maupun masyarakat. Sementara itu, Kasat Pol PP Kabupaten Empat Lawang, Mgs. Nawawi, mengatakan pihaknya turut melakukan pengawasan dan penertiban di lapangan. Pengawasan dilakukan terutama untuk memastikan tidak ada aktivitas berjualan yang menghambat rangkaian kegiatan peringatan HUT Kemerdekaan RI.</p><p>&ldquo;Kita mengedepankan pendekatan persuasif. Para pedagang diberikan pemahaman agar berjualan pada lokasi yang telah ditentukan dan tidak menggunakan badan jalan secara berlebihan,&rdquo; ungkap Nawawi.</p><p>Ia menambahkan, penataan menjadi penting karena Jalan Abu Bakar Din merupakan salah satu jalur yang akan dilalui peserta kegiatan 17 Agustus. Dengan kondisi jalan yang lebih tertib, pelaksanaan lomba gerak jalan dan penampilan drum band diharapkan dapat berlangsung aman, lancar, dan nyaman bagi masyarakat.</p><p>Pemkab Empat Lawang berharap para pedagang dapat mendukung upaya penataan tersebut demi kepentingan bersama. Dengan demikian, aktivitas perdagangan tetap berjalan, kawasan pasar tetap hidup, dan seluruh rangkaian kegiatan dalam menyambut HUT ke-81 Kemerdekaan RI dapat berlangsung dengan tertib. (KOMINFO)',
            ],
            [
                'title' => 'TP-PKK Empat Lawang Hadiri "Islam Itu Indah", Perkuat Nilai Keagamaan dan Peran Keluarga',
                'image' => 'news3.webp',
                'published_at' => '2026-04-30',
                'content' => 'JAKARTA – Ketua Tim Penggerak PKK Kabupaten Empat Lawang, Hj. Heppy Sapriani, bersama jajaran anggota TP-PKK menghadiri program religi &ldquo;Islam Itu Indah&rdquo; yang disiarkan oleh salah satu stasiun televisi swasta nasional. Kehadiran rombongan ini menjadi bagian dari upaya memperluas wawasan keagamaan sekaligus mempererat silaturahmi melalui dakwah yang inspiratif dan penuh makna.</p><p>Ketua TP-PKK Empat Lawang Hj. Heppy Sapriani menyampaikan bahwa kegiatan tersebut memberikan banyak pelajaran berharga, terutama dalam memperkuat nilai-nilai keislaman dalam kehidupan sehari-hari. Menurutnya, program seperti &ldquo;Islam Itu Indah&rdquo; mampu menyampaikan ajaran agama dengan cara yang ringan, menarik, dan mudah dipahami oleh berbagai kalangan.</p><p>&ldquo;Dakwah yang dikemas secara modern mampu menjangkau semua lapisan masyarakat, termasuk generasi muda,&rdquo; ujarnya, Kamis (30/4/2026).</p><p>Ia berharap, sepulang dari kegiatan tersebut, para anggota TP-PKK dapat mengimplementasikan ilmu yang diperoleh serta menyebarkannya kepada masyarakat di Kabupaten Empat Lawang. Hal ini dinilai penting dalam memperkuat peran keluarga sebagai fondasi utama dalam membangun masyarakat yang religius dan harmonis.</p><p>&ldquo;Semoga ilmu yang kami dapatkan hari ini bisa menjadi bekal untuk meningkatkan pembinaan keluarga dan memperkuat nilai-nilai keagamaan di tengah masyarakat,&rdquo; tambahnya.</p><p>Kegiatan ini juga menjadi momentum bagi TP-PKK Empat Lawang untuk terus aktif mendukung pembangunan sumber daya manusia, khususnya di bidang keagamaan dan sosial. (KOMINFO)',
            ],
            [
                'title' => 'Empat Lawang Serius Benahi Sampah, Bupati Joncik Temui Wamen LH Dorong Sistem Modern',
                'image' => 'news4.webp',
                'published_at' => '2026-04-27',
                'content' => 'EMPAT LAWANG – Pemerintah Kabupaten (Pemkab) Empat Lawang menunjukkan keseriusan dalam membenahi persoalan persampahan. Hal ini terlihat dari langkah langsung Bupati Empat Lawang, H. Joncik Muhammad, yang melakukan audiensi dengan Wakil Menteri Lingkungan Hidup pada Senin (27/4/2026). Pertemuan tersebut menjadi momentum strategis untuk memperkuat sinergi antara pemerintah daerah dan pusat dalam meningkatkan kualitas lingkungan, khususnya dalam pengelolaan sampah yang lebih modern dan berkelanjutan.</p><p>Dalam audiensi itu, Bupati Joncik didampingi Ketua DPRD Empat Lawang Darli serta Kepala Dinas Lingkungan Hidup (DLH). Kehadiran para pemangku kepentingan ini menegaskan komitmen bersama untuk membangun sistem pengelolaan sampah yang terintegrasi. Fokus utama pembahasan mencakup penguatan sarana dan prasarana kebersihan, sekaligus mendorong perubahan pola pikir masyarakat agar mulai memilah sampah sejak dari sumbernya.</p><p>&ldquo;Kesadaran masyarakat menjadi kunci. Pengelolaan sampah tidak bisa hanya bergantung pada pemerintah. Harus dimulai dari rumah tangga dengan memilah sampah organik dan anorganik,&rdquo; tegas Joncik.</p><p>Selain itu, Bupati juga menyampaikan sejumlah kebutuhan mendesak, mulai dari penambahan armada pengangkut sampah, pembangunan tempat penampungan sementara (TPS), hingga penguatan fasilitas pengolahan akhir. Ia berharap dukungan pemerintah pusat dapat segera terealisasi guna mempercepat pembangunan infrastruktur persampahan di Empat Lawang.</p><p>&ldquo;Harapan kita, hasil pertemuan ini bisa segera ditindaklanjuti menjadi program nyata. Dengan kerja sama semua pihak, Empat Lawang bisa menjadi daerah percontohan dalam pengelolaan sampah modern berbasis partisipasi masyarakat,&rdquo; ujarnya.</p><p>Langkah ini menjadi sinyal kuat bahwa Pemkab Empat Lawang tidak hanya berwacana, tetapi mulai bergerak nyata menuju lingkungan yang lebih bersih, sehat, dan berkelanjutan.',
            ],
            [
                'title' => 'Rayakan HUT ke-19, Empat Lawang Gelar Jalan Sehat dan Kampanye Lingkungan Bersama',
                'image' => 'news5.webp',
                'published_at' => '2026-04-21',
                'content' => 'EMPAT LAWANG — Dalam rangka menyambut Hari Ulang Tahun (HUT) Kabupaten Empat Lawang ke-19, Hari Kartini, HUT PKK, serta HUT Persit, Pemerintah Kabupaten Empat Lawang bersama TP-PKK menggelar rapat persiapan kegiatan jalan sehat yang akan dilaksanakan pada Kamis, 23 April 2026.</p><p>Ketua TP-PKK Empat Lawang, Hj. Heppy Sapriani, menyampaikan bahwa kegiatan jalan sehat ini tidak hanya menjadi ajang olahraga bersama, tetapi juga momentum untuk mempererat kebersamaan masyarakat serta meningkatkan kepedulian terhadap lingkungan.</p><p>Usai kegiatan jalan sehat, rangkaian acara akan dilanjutkan dengan kegiatan penghijauan di kawasan kolam retensi. Dalam kegiatan tersebut, akan dilakukan penanaman pohon mahoni sebanyak 20 batang yang telah disiapkan oleh Pak Pabung. Penanaman akan dilakukan dengan jarak antar pohon sekitar 20 meter agar pertumbuhan tanaman dapat optimal dan tertata dengan baik. Selain pohon mahoni, juga akan disiapkan beberapa jenis pohon buah sebagai upaya memperkaya keberagaman tanaman dan memberikan manfaat jangka panjang bagi lingkungan sekitar.</p><p>Tidak hanya itu, kegiatan juga akan dilanjutkan dengan penebaran bibit ikan di kolam retensi sebagai bentuk pemanfaatan ekosistem perairan secara berkelanjutan. Rangkaian kegiatan ini direncanakan berlangsung di dua lokasi utama, yakni kawasan Pulau Emas dan kolam retensi, yang diharapkan menjadi pusat aktivitas masyarakat dalam suasana penuh kebersamaan dan kegembiraan.</p><p>Dalam rapat persiapan, Asisten I Setda Empat Lawang, Kuswinarto, menekankan pentingnya partisipasi masyarakat dalam menyukseskan kegiatan tersebut. Ia meminta para camat, khususnya Camat Tebing, untuk mengajak warganya agar setiap desa dan kelurahan dapat mengirimkan perwakilan dalam kegiatan jalan sehat ini.</p><p>&ldquo;Partisipasi masyarakat menjadi kunci suksesnya kegiatan ini. Kami berharap setiap desa dan kelurahan dapat turut ambil bagian,&rdquo; ujarnya.</p><p>Pemerintah Kabupaten Empat Lawang mengajak seluruh masyarakat untuk turut memeriahkan kegiatan ini sebagai wujud kebersamaan, semangat gotong royong, serta kepedulian terhadap lingkungan dan daerah. Dengan rangkaian kegiatan yang edukatif dan partisipatif ini, diharapkan peringatan hari-hari besar tersebut dapat memberikan dampak positif serta memperkuat rasa persatuan di tengah masyarakat Empat Lawang. (KOMINFO)',
            ],
        ];

        foreach ($posts as $post) {
            if (News::where('title', $post['title'])->exists()) {
                continue;
            }

            $localPath = __DIR__ . '/assets/news/' . $post['image'];
            $imagePath = null;

            if (is_file($localPath)) {
                $ext = pathinfo($post['image'], PATHINFO_EXTENSION);
                $storedName = 'news/' . Str::random(20) . '.' . $ext;
                Storage::disk('public')->put($storedName, file_get_contents($localPath));
                $imagePath = $storedName;
            }

            News::create([
                'title' => $post['title'],
                'slug' => Str::slug($post['title']),
                'content' => '<p>' . $post['content'] . '</p>',
                'image' => $imagePath,
                'author' => 'Kominfo Empat Lawang',
                'published_at' => $post['published_at'],
                'is_published' => true,
                'is_headline' => false,
            ]);
        }
    }
}
