<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\GalleryItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class GalleryExternalSeeder extends Seeder
{
    /**
     * Seeds one "Dokumentasi Kegiatan Pemkab Empat Lawang" gallery with photos
     * scraped from empatlawangkab.go.id/blog (as of 2026-09-07), so the CMS
     * has real sample content instead of an empty gallery.
     */
    public function run(): void
    {
        $title = 'Dokumentasi Kegiatan Pemkab Empat Lawang';

        if (Gallery::where('title', $title)->exists()) {
            return;
        }

        $items = [
            ['file' => 'news/news1.webp', 'caption' => 'Semangat Kemerdekaan Membara, Bupati Empat Lawang Hadiri Lomba Drumband HUT RI ke-81'],
            ['file' => 'news/news2.webp', 'caption' => 'Jelang HUT ke-81 RI, Pemkab Empat Lawang Tertibkan Pedagang di Jalan Abu Bakar Din Tebing Tinggi'],
            ['file' => 'news/news3.webp', 'caption' => 'TP-PKK Empat Lawang Hadiri "Islam Itu Indah", Perkuat Nilai Keagamaan dan Peran Keluarga'],
            ['file' => 'news/news4.webp', 'caption' => 'Empat Lawang Serius Benahi Sampah, Bupati Joncik Temui Wamen LH Dorong Sistem Modern'],
            ['file' => 'news/news5.webp', 'caption' => 'Rayakan HUT ke-19, Empat Lawang Gelar Jalan Sehat dan Kampanye Lingkungan Bersama'],
            ['file' => 'gallery/gal1.webp', 'caption' => 'Empat Lawang Rayakan HUT ke-19, Tegaskan Komitmen Bangun Daerah Lebih Maju'],
            ['file' => 'gallery/gal2.webp', 'caption' => 'Lomba Foto Twibbon HUT ke-19 Empat Lawang Resmi Dibuka, Ajak Masyarakat Ramaikan Media Sosial'],
            ['file' => 'gallery/gal3.webp', 'caption' => 'Pemkab Empat Lawang Gelar Rapat Pembentukan Panitia HUT Empat Lawang'],
            ['file' => 'gallery/gal4.webp', 'caption' => 'Bupati Empat Lawang Hadiri Open House Gubernur Sumsel'],
            ['file' => 'gallery/gal5.webp', 'caption' => 'Musrenbang RKPD Kabupaten Empat Lawang Tahun Perencanaan 2027 Resmi Dimulai'],
            ['file' => 'gallery/gal6.webp', 'caption' => 'Pemuda Muhammadiyah Empat Lawang Gelar Musyawarah Besar untuk Perubahan'],
        ];

        $coverPath = $this->uploadToPublicDisk($items[0]['file'], 'galleries/covers');

        $gallery = Gallery::create([
            'title' => $title,
            'description' => 'Kumpulan dokumentasi foto kegiatan Pemerintah Kabupaten Empat Lawang.',
            'type' => 'photo',
            'cover_image' => $coverPath,
            'is_published' => true,
        ]);

        foreach ($items as $i => $item) {
            $path = $this->uploadToPublicDisk($item['file'], 'galleries/items');

            GalleryItem::create([
                'gallery_id' => $gallery->id,
                'image_path' => $path,
                'caption' => $item['caption'],
                'order' => $i,
            ]);
        }
    }

    private function uploadToPublicDisk(string $relativeAssetPath, string $folder): ?string
    {
        $localPath = __DIR__ . '/assets/' . $relativeAssetPath;

        if (! is_file($localPath)) {
            return null;
        }

        $ext = pathinfo($relativeAssetPath, PATHINFO_EXTENSION);
        $storedName = $folder . '/' . Str::random(20) . '.' . $ext;
        Storage::disk('public')->put($storedName, file_get_contents($localPath));

        return $storedName;
    }
}
