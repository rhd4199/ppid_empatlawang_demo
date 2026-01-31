<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing events
        Event::truncate();

        $events = [
            [
                'title' => 'Rapat Koordinasi Mingguan',
                'description' => 'Rapat koordinasi rutin seluruh kepala dinas dan OPD.',
                'location' => 'Ruang Rapat Utama Kantor Bupati',
                'time' => '08:00',
                'duration' => 2 // hours
            ],
            [
                'title' => 'Sosialisasi Keterbukaan Informasi Publik',
                'description' => 'Sosialisasi mengenai pentingnya keterbukaan informasi publik bagi masyarakat desa.',
                'location' => 'Gedung Serbaguna Kecamatan Tebing Tinggi',
                'time' => '09:00',
                'duration' => 4
            ],
            [
                'title' => 'Upacara Peringatan Hari Besar Nasional',
                'description' => 'Upacara bendera dalam rangka peringatan hari besar nasional.',
                'location' => 'Lapangan Pemkab Empat Lawang',
                'time' => '07:30',
                'duration' => 2
            ],
            [
                'title' => 'Pelatihan Operator Website OPD',
                'description' => 'Pelatihan teknis pengelolaan website dan media sosial bagi operator OPD.',
                'location' => 'Lab Komputer Diskominfo',
                'time' => '13:00',
                'duration' => 3
            ],
            [
                'title' => 'Kunjungan Kerja Gubernur',
                'description' => 'Penyambutan kunjungan kerja Gubernur Sumatera Selatan dalam rangka peresmian infrastruktur.',
                'location' => 'Pendopoan Rumah Dinas Bupati',
                'time' => '10:00',
                'duration' => 5
            ],
            [
                'title' => 'Festival Budaya Empat Lawang',
                'description' => 'Pagelaran seni dan budaya asli daerah Empat Lawang.',
                'location' => 'Alun-alun Kota',
                'time' => '19:00',
                'duration' => 4
            ],
            [
                'title' => 'Musrenbang Kabupaten',
                'description' => 'Musyawarah Perencanaan Pembangunan tingkat Kabupaten Empat Lawang.',
                'location' => 'Hotel Kito',
                'time' => '08:30',
                'duration' => 6
            ]
        ];

        // Create events for current month (distributed)
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Ensure an event exists for TODAY for better demo
        $todayEvent = $events[0];
        $todayStart = Carbon::now()->setTimeFromTimeString('09:00');
        Event::create([
            'title' => $todayEvent['title'] . ' (HARI INI)',
            'slug' => Str::slug($todayEvent['title'] . '-' . Str::random(5)),
            'description' => $todayEvent['description'],
            'location' => $todayEvent['location'],
            'start_date' => $todayStart,
            'end_date' => $todayStart->copy()->addHours($todayEvent['duration'])
        ]);

        for ($i = 0; $i < 10; $i++) {
            $eventTemplate = $events[array_rand($events)];
            $randomDay = rand(1, $endDate->daysInMonth);
            $eventStart = $startDate->copy()->day($randomDay)->setTimeFromTimeString($eventTemplate['time']);
            
            Event::create([
                'title' => $eventTemplate['title'],
                'slug' => Str::slug($eventTemplate['title'] . '-' . Str::random(5)),
                'description' => $eventTemplate['description'],
                'location' => $eventTemplate['location'],
                'start_date' => $eventStart,
                'end_date' => $eventStart->copy()->addHours($eventTemplate['duration'])
            ]);
        }

        // Create events for next few months
        for ($m = 1; $m <= 3; $m++) {
            $monthStart = Carbon::now()->addMonths($m)->startOfMonth();
            $monthEnd = Carbon::now()->addMonths($m)->endOfMonth();
            
            for ($i = 0; $i < 5; $i++) {
                $eventTemplate = $events[array_rand($events)];
                $randomDay = rand(1, $monthEnd->daysInMonth);
                $eventStart = $monthStart->copy()->day($randomDay)->setTimeFromTimeString($eventTemplate['time']);

                Event::create([
                    'title' => $eventTemplate['title'] . ' (Bulan Depan)',
                    'slug' => Str::slug($eventTemplate['title'] . '-' . Str::random(5)),
                    'description' => $eventTemplate['description'],
                    'location' => $eventTemplate['location'],
                    'start_date' => $eventStart,
                    'end_date' => $eventStart->copy()->addHours($eventTemplate['duration'])
                ]);
            }
        }
    }
}