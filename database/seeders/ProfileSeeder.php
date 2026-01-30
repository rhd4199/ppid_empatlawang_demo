<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profile;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Profile::create([
            'title' => 'Tentang PPID',
            'slug' => 'tentang-ppid',
            'content' => '<p>PPID (Pejabat Pengelola Informasi dan Dokumentasi) Kabupaten Empat Lawang bertugas untuk menyediakan akses informasi publik bagi masyarakat sesuai dengan amanat UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik.</p>',
            'type' => 'general'
        ]);

        Profile::create([
            'title' => 'Visi dan Misi',
            'slug' => 'visi-misi',
            'content' => '<h3>Visi</h3><p>Mewujudkan Empat Lawang MADANI (Makmur, Aman, Damai, Agamis, Nasionalis, Indah).</p><h3>Misi</h3><ol><li>Meningkatkan kualitas SDM.</li><li>Mewujudkan tata kelola pemerintahan yang baik.</li></ol>',
            'type' => 'visi_misi'
        ]);
        
        Profile::create([
            'title' => 'Struktur Organisasi',
            'slug' => 'struktur-organisasi',
            'content' => '<p>Struktur Organisasi PPID Kabupaten Empat Lawang terdiri dari Pembina, Atasan PPID, PPID Utama, dan PPID Pelaksana.</p>',
            'type' => 'struktur'
        ]);

        Profile::create([
            'title' => 'Tugas dan Fungsi',
            'slug' => 'tugas-fungsi',
            'content' => '<h3>Tugas PPID</h3><ul><li>Menyediakan, menyimpan, mendokumentasikan, dan mengamankan Informasi Publik.</li><li>Melayani permohonan Informasi Publik sesuai dengan peraturan yang berlaku.</li></ul><h3>Fungsi</h3><p>Pengelolaan dan pelayanan informasi publik di lingkungan Pemerintah Kabupaten Empat Lawang.</p>',
            'type' => 'tugas_fungsi'
        ]);
    }
}
