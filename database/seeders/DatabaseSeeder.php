<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            UserSeeder::class,
            OfficialSeeder::class,
            ProfileSeeder::class,
            PpidSettingSeeder::class,
            // InfoPublicSeeder::class,
            DocumentSeeder::class,
            InfoBerkalaChecklistSeeder::class,
            InfoPublikContentFixSeeder::class,
            NewsSeeder::class,
            NewsExternalSeeder::class,
            GallerySeeder::class,
            GalleryExternalSeeder::class,
            EventSeeder::class,
            ProcurementSeeder::class,
        ]);
    }
}
