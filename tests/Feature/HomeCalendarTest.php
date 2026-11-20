<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_calendar_shows_current_month_with_marked_events(): void
    {
        $this->travelTo('2026-02-10 08:00:00'); // Feb 2026: 28 days, starts on Sunday

        Event::create(['title' => 'Rapat Koordinasi PPID', 'slug' => 'rapat', 'start_date' => '2026-02-14 09:30:00', 'location' => 'Aula Setda']);
        Event::create(['title' => 'Sosialisasi KIP', 'slug' => 'sosialisasi', 'start_date' => '2026-02-03 10:00:00']);
        Event::create(['title' => 'Agenda Bulan Lalu', 'slug' => 'lalu', 'start_date' => '2026-01-31 09:00:00']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-days="28"', $html);
        $this->assertStringContainsString('Agenda Februari 2026', $html);
        $this->assertSame(28, substr_count($html, 'class="home-cal-num"'));
        $this->assertSame(6, substr_count($html, 'home-cal-day is-blank')); // Sen–Sab kosong sebelum Minggu 1 Feb
        $this->assertSame(2, substr_count($html, 'home-cal-day has-event'));
        $this->assertSame(1, substr_count($html, 'home-cal-day is-today"><span class="home-cal-num">10<')); // hari ini
        $this->assertSame(2, substr_count($html, 'class="home-agenda-item'));
        $this->assertSame(1, substr_count($html, 'home-agenda-item is-past')); // 3 Feb sudah lewat
        $this->assertStringContainsString('09:30 · Aula Setda', $html);
        $this->assertStringContainsString('Rapat Koordinasi PPID', $html);
        $this->assertStringNotContainsString('Agenda Bulan Lalu', $html);
    }

    public function test_home_calendar_empty_month(): void
    {
        $this->get('/')->assertOk()->assertSee('Belum ada agenda bulan ini.');
    }
}
