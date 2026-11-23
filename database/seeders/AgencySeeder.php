<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    /**
     * Perangkat daerah (OPD) from assets/agencies.json (research: workspace shared/riset-opd-empatlawang/agencies-2026-11.json).
     * Keyed by slug. Re-running refreshes rows the admin never edited (updated_at == created_at); edited rows are kept.
     * Heads are linked to existing officials by name; run OfficialSeeder first.
     */
    public function run(): void
    {
        $rows = json_decode(file_get_contents(__DIR__ . '/assets/agencies.json'), true, flags: JSON_THROW_ON_ERROR);

        $n = Agency::import($rows);
        $this->command?->info("Agencies: {$n['created']} new, {$n['updated']} refreshed, {$n['kept']} kept (edited by admin).");
    }
}
