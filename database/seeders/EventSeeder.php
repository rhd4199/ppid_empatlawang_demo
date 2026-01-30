<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::create([
            'title' => 'Bimbingan Teknis PPID',
            'slug' => 'bimtek-ppid',
            'description' => 'Bimbingan teknis pengelolaan informasi publik bagi operator OPD.',
            'location' => 'Aula Kantor Bupati Empat Lawang',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(4)
        ]);

        Event::create([
            'title' => 'Festival Empat Lawang',
            'slug' => 'festival-empat-lawang',
            'description' => 'Festival budaya tahunan...',
            'location' => 'Lapangan Merdeka Tebing Tinggi',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonth()->addDays(3)
        ]);
    }
}
