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

        SppgUnit::import($rows);
    }
}
