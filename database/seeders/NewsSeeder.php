<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Its 2 curated entries had no real image and were unsourced boilerplate
     * text, so they were removed — every News row now comes from
     * NewsExternalSeeder (real articles + real photos scraped from
     * empatlawangkab.go.id). Left as a no-op rather than deleted so
     * DatabaseSeeder's call list doesn't break.
     */
    public function run(): void
    {
        //
    }
}
