<?php

namespace Database\Seeders;

use App\Models\SppgUnit;
use Illuminate\Database\Seeder;

class SppgUnitSeeder extends Seeder
{
    /**
     * SPPG (MBG kitchens) from BGN Radar MBG, saved in assets/sppg-units.json (skill bgn-sppg-scrape; no download here).
     * Keyed by BGN id. Re-running refreshes rows the admin never edited (updated_at == created_at); edited rows are kept.
     * Kitchens outside the regency that serve a school here are stored hidden.
     */
    public function run(): void
    {
        $rows = json_decode(file_get_contents(__DIR__ . '/assets/sppg-units.json'), true, flags: JSON_THROW_ON_ERROR);

        foreach ($rows as $row) {
            $data = [
                'code' => $row['code'],
                'name' => $row['name'],
                'kecamatan' => $row['kecamatan'],
                'village' => $row['village'],
                'address' => $row['address'],
                'status' => $row['status'],
                'institutions' => $row['institutions'],
                'source_url' => $row['source_url'],
                'checked_at' => $row['checked_at'],
                'is_published' => $row['in_regency'],
                'notes' => $row['in_regency'] ? null : 'Dapur berlokasi di luar Kabupaten Empat Lawang; melayani instansi di Empat Lawang.',
            ];

            $unit = SppgUnit::where('bgn_id', $row['bgn_id'])->first();
            if (! $unit) {
                SppgUnit::create($data + ['bgn_id' => $row['bgn_id']]);
            } elseif ($unit->updated_at->equalTo($unit->created_at)) {
                $unit->timestamps = false; // keep the "never edited" marker
                $unit->fill($data)->save();
            }
        }
    }
}
