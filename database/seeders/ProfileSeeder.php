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
            'title' => 'Visi dan Misi',
            'slug' => 'visi-misi',
            'content' => '<h3>Visi</h3><p>Mewujudkan Empat Lawang MADANI (Makmur, Aman, Damai, Agamis, Nasionalis, Indah).</p><h3>Misi</h3><ol><li>Meningkatkan kualitas SDM.</li><li>Mewujudkan tata kelola pemerintahan yang baik.</li></ol>',
            'type' => 'visi_misi'
        ]);
        
        Profile::create([
            'title' => 'Struktur Organisasi',
            'slug' => 'struktur',
            'content' => '<p>Struktur Organisasi PPID Kabupaten Empat Lawang terdiri dari Pembina, Atasan PPID, PPID Utama, dan PPID Pelaksana.</p>',
            'type' => 'struktur'
        ]);
    }
}
