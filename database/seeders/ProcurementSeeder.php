<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProcurementSeeder extends Seeder
{
    /**
     * The `Procurement` model/table is dead code — the actual "Pengadaan"
     * feature (ProcurementController, /pengadaan) reads from the `Document`
     * model (categories pengadaan_info/pengadaan_regulasi) instead, seeded
     * via DocumentSeeder. Nothing reads the procurements table, so this is
     * a no-op rather than seeding data nobody uses.
     */
    public function run(): void
    {
        //
    }
}
