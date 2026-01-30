<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gallery;

class GallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gallery::create([
            'title' => 'Rapat Koordinasi PPID se-Sumatera Selatan',
            'description' => 'Dokumentasi rapat koordinasi...',
            'type' => 'photo',
            'file_path' => 'galleries/rakor.jpg'
        ]);

        Gallery::create([
            'title' => 'Video Profil Kabupaten Empat Lawang',
            'description' => 'Video profil daerah...',
            'type' => 'video',
            'url' => 'https://youtube.com/watch?v=sample'
        ]);
    }
}
