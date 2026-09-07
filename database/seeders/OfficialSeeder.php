<?php

namespace Database\Seeders;

use App\Models\Official;
use Illuminate\Database\Seeder;

class OfficialSeeder extends Seeder
{
    /**
     * Source: id.wikipedia.org (Kabupaten Empat Lawang, Joncik Muhammad), as of 2026.
     * No photos seeded — admin uploads via the CMS.
     */
    public function run(): void
    {
        $officials = [
            [
                'name' => 'Joncik Muhammad',
                'position' => 'Bupati Empat Lawang',
                'bio' => 'Bupati Empat Lawang dari Partai Amanat Nasional (PAN), menjabat dua periode: 2018-2023 dan periode berjalan sejak 16 Juni 2025. Sebelumnya menjabat Ketua Komisi II DPRD Provinsi Sumatera Selatan (2014-2018).',
                'order' => 1,
            ],
            [
                'name' => "A. Rifa'i",
                'position' => 'Wakil Bupati Empat Lawang',
                'bio' => 'Wakil Bupati Empat Lawang periode berjalan, mendampingi Bupati Joncik Muhammad.',
                'order' => 2,
            ],
            [
                'name' => 'Fauzan Khoiri Denin',
                'position' => 'Sekretaris Daerah Kabupaten Empat Lawang',
                'bio' => 'Sekretaris Daerah (Sekda) Kabupaten Empat Lawang.',
                'order' => 3,
            ],
        ];

        foreach ($officials as $data) {
            Official::firstOrCreate(
                ['name' => $data['name'], 'position' => $data['position']],
                $data + ['is_published' => true]
            );
        }
    }
}
