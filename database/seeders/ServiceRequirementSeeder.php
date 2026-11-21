<?php

namespace Database\Seeders;

use App\Models\ServiceRequirement;
use Illuminate\Database\Seeder;

class ServiceRequirementSeeder extends Seeder
{
    /**
     * Researched requirements, one file per category in assets/service-requirements/ (items keyed by slug).
     * Re-running refreshes rows the admin never edited; rows changed in admin (updated_at > created_at) are kept.
     */
    public function run(): void
    {
        foreach (['kependudukan', 'perizinan_usaha', 'kendaraan', 'pertanahan', 'kepegawaian'] as $category) {
            $file = require __DIR__ . "/assets/service-requirements/$category.php";

            foreach ($file['items'] as $data) {
                $data += [
                    'category' => $category,
                    'agency' => $file['agency'],
                    'checked_at' => $file['checked_at'],
                    'is_published' => true,
                ];

                $item = ServiceRequirement::where('slug', $data['slug'])->first();
                if (!$item) {
                    ServiceRequirement::create($data);
                } elseif ($item->updated_at->equalTo($item->created_at)) {
                    $item->timestamps = false; // keep the "never edited" marker
                    $item->fill($data)->save();
                }
            }
        }
    }
}
