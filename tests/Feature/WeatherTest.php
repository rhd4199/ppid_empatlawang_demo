<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    private function rss(string ...$items): string
    {
        $xml = '';
        foreach ($items as $i => $desc) {
            [$title, $text] = explode('|', $desc);
            $xml .= "<item><title>$title</title><link>https://www.bmkg.go.id/alerts/nowcast/id/x{$i}_alert.xml</link><description>$text</description></item>";
        }

        return "<?xml version=\"1.0\"?><rss><channel><title>Peringatan Dini Cuaca BMKG</title>$xml</channel></rss>";
    }

    private function slot(string $time, int $t): array
    {
        return ['local_datetime' => $time, 't' => $t, 'hu' => 80, 'weather_desc' => 'Hujan Ringan', 'ws' => 5, 'wd' => 'W', 'image' => 'https://api-apps.bmkg.go.id/i.svg'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->travelTo(now('Asia/Jakarta')->setTime(10, 0));
    }

    public function test_returns_upcoming_slots_and_only_empat_lawang_alerts(): void
    {
        $today = now('Asia/Jakarta')->format('Y-m-d');
        Http::fake([
            'api.bmkg.go.id/*' => Http::response(['data' => [['cuaca' => [
                [$this->slot("$today 07:00:00", 21), $this->slot("$today 10:00:00", 25)],
                [$this->slot("$today 13:00:00", 29)],
            ]]]]),
            'www.bmkg.go.id/*' => Http::response($this->rss(
                'Hujan Lebat disertai Petir di Sumatera Selatan|Hujan lebat di sebagian wilayah Sumatera Selatan, khususnya di MESUJI, TEBING TINGGI, SALING.',
                'Hujan Lebat disertai Petir di Sumatera Selatan|khususnya di AIR SUGIHAN, PEDAMARAN.',
                'Hujan Lebat di Jambi|khususnya di TEBING TINGGI.',
            )),
        ]);

        $res = $this->getJson('/cuaca')->assertOk()
            ->assertJsonPath('district', '04')
            ->assertJsonPath('name', 'Tebing Tinggi')
            ->assertJsonCount(2, 'slots')
            ->assertJsonPath('slots.0.temp', 25)
            ->assertJsonCount(1, 'alerts')
            ->assertJsonPath('alerts.0.areas', ['TEBING TINGGI', 'SALING']);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'adm4=16.11.04.2001'));
        $this->assertStringContainsString('BMKG', $res->json('source'));
    }

    public function test_district_param_is_whitelisted_and_results_cached(): void
    {
        Http::fake(['api.bmkg.go.id/*' => Http::response(['data' => [['cuaca' => [[]]]]]), 'www.bmkg.go.id/*' => Http::response($this->rss())]);

        $this->getJson('/cuaca?kec=09')->assertJsonPath('name', 'Saling');
        $this->getJson('/cuaca?kec=../../x')->assertJsonPath('district', '04');
        $this->getJson('/cuaca?kec=09');

        Http::assertSentCount(3); // 09 forecast + nowcast + 04 forecast; second 09 call served from cache
    }

    public function test_bmkg_down_gives_empty_data_and_is_retried(): void
    {
        Http::fake(['*' => Http::response('down', 503)]);

        $this->getJson('/cuaca')->assertOk()->assertJsonPath('slots', [])->assertJsonPath('alerts', []);
        $this->getJson('/cuaca');

        Http::assertSentCount(4); // failures are not cached
    }

    public function test_home_has_weather_card_banner_and_footer_chip(): void
    {
        Http::fake();

        $this->get('/')->assertOk()
            ->assertSee('id="bmkgCard"', false)
            ->assertSee('id="bmkgAlert"', false)
            ->assertSee('data-bmkg-chip', false)
            ->assertSee('Sumber:', false);
        Http::assertNothingSent(); // weather is loaded by JS, never during page render
    }
}
