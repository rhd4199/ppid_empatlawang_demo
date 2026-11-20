<?php

namespace Tests\Feature;

use App\Models\Event;
use Database\Seeders\EventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_real_events_show_on_event_page(): void
    {
        $this->travelTo('2026-11-20 10:00');
        Event::create(['title' => 'Musrenbang Kabupaten', 'slug' => 'musrenbang-kabupaten-abcde', 'start_date' => '2026-11-03 08:30',
            'description' => 'Musyawarah Perencanaan Pembangunan tingkat Kabupaten Empat Lawang.']); // old demo row
        Event::create(['title' => 'Agenda Admin', 'slug' => 'agenda-admin', 'start_date' => '2026-11-25 09:00']);

        $this->seed(EventSeeder::class);
        $this->seed(EventSeeder::class);

        $this->assertSame(14, Event::count()); // 13 seeded, no duplicates, admin row kept, demo row removed
        $this->assertDatabaseMissing('events', ['slug' => 'musrenbang-kabupaten-abcde']);

        $this->get('/event')->assertOk()
            ->assertSee('November 2026')
            ->assertSee('07:45 Upacara Peringatan Hari Pahlawan 2026')
            ->assertSee('Pelantikan Pengurus PWI Kabupaten Empat Lawang 2026–2029')
            ->assertSee('Pendopoan Rumah Dinas Bupati Empat Lawang')
            ->assertSeeInOrder(['Pembukaan MTQ ke-XIX', 'Upacara HUT ke-81 Kemerdekaan', 'Pelantikan MD KAHMI'])
            ->assertSee('Agenda Admin')
            ->assertDontSee('Festival Budaya Empat Lawang');
    }
}
