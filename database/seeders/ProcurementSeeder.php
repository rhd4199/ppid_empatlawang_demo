<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Procurement;

class ProcurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Procurement::create([
            'title' => 'Pengadaan Laptop Dinas Kominfo',
            'slug' => 'pengadaan-laptop-kominfo',
            'category' => 'pengadaan_barang',
            'content' => 'Pengadaan 10 unit laptop untuk operasional...',
            'file_path' => 'procurements/spek-laptop.pdf',
            'status' => 'open'
        ]);

        Procurement::create([
            'title' => 'Jasa Pemeliharaan Jaringan Internet',
            'slug' => 'pemeliharaan-jaringan',
            'category' => 'jasa_lainnya',
            'content' => 'Jasa pemeliharaan jaringan FO...',
            'status' => 'closed'
        ]);

        Procurement::create([
            'title' => 'Perpres No. 16 Tahun 2018',
            'slug' => 'perpres-16-2018',
            'category' => 'regulasi_pengadaan',
            'content' => 'Peraturan Presiden tentang Pengadaan Barang/Jasa Pemerintah.',
            'file_path' => 'procurements/perpres-16-2018.pdf',
            'status' => 'published'
        ]);
    }
}
