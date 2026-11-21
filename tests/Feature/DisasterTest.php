<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DisasterTest extends TestCase
{
    use RefreshDatabase;

    private function feature(string $id, string $date, string $region, array $extra = []): array
    {
        return ['attributes' => $extra + [
            'id' => $id, 'dt' => strtotime("{$date} 00:00:00 UTC") * 1000, 'kabupaten' => $region, 'kategori_bencana' => 'Banjir',
            'meninggal' => 0, 'hilang' => 0, 'luka_sakit' => 0, 'mengungsi' => 0, 'menderita' => 330, 'rumah_rusak' => 0, 'rumah_terendam' => 66,
            'kronologis' => "●\tHujan deras, sungai meluap ke <b>pemukiman</b>.", 'kondisi_mutakhir' => "Kamis\n●\tBanjir belum surut",
        ]];
    }

    public function test_events_are_mapped_and_filtered_to_empat_lawang_and_neighbours(): void
    {
        $this->travelTo('2026-11-21 10:00:00');
        Http::fake(['gis.bnpb.go.id/*' => Http::response(['features' => [
            $this->feature('a', '2026-11-19', 'Seluma'),
            $this->feature('b', '2026-11-20', 'Empat Lawang', ['kategori_bencana' => 'Tanah longsor', 'meninggal' => 1]),
        ]])]);

        $json = $this->getJson('/bencana')->assertOk()->json();

        $this->assertSame(['Empat Lawang', 'Lahat', 'Musi Rawas', 'Kepahiang', 'Rejang Lebong', 'Seluma'], $json['regions']);
        $this->assertSame(['b', 'a'], array_column($json['events'], 'id')); // newest first
        $this->assertSame(['meninggal' => 1, 'terdampak' => 330, 'rumah terendam' => 66], $json['events'][0]['impact']); // zeros dropped
        $this->assertSame('Hujan deras, sungai meluap ke <b>pemukiman</b>.', $json['events'][1]['summary']); // bullets gone, HTML left to the view's textContent
        $this->assertSame('Kamis Banjir belum surut', $json['events'][1]['status']);
        Http::assertSent(fn ($req) => str_contains(urldecode($req->url()), "kabupaten IN ('Empat Lawang','Lahat','Musi Rawas','Kepahiang','Rejang Lebong','Seluma')"));
    }

    public function test_past_weeks_are_kept_for_30_days_and_bnpb_is_called_once_per_hour(): void
    {
        $this->travelTo('2026-11-10 10:00:00');
        Http::fakeSequence('gis.bnpb.go.id/*')
            ->push(['features' => [$this->feature('old', '2026-10-01', 'Lahat'), $this->feature('w45', '2026-11-09', 'Lahat')]])
            ->push(['features' => [$this->feature('w47', '2026-11-19', 'Seluma')]]);

        $this->getJson('/bencana')->assertOk();
        $this->getJson('/bencana')->assertOk();
        Http::assertSentCount(1); // cached for an hour

        $this->travelTo('2026-11-21 10:00:00'); // next week: BNPB only returns the new week
        $this->assertSame(['w47', 'w45'], array_column($this->getJson('/bencana')->json('events'), 'id')); // "old" > 30 days dropped
    }

    public function test_bnpb_failure_returns_empty_list_and_retries_later(): void
    {
        Http::fake(['gis.bnpb.go.id/*' => Http::response(['error' => ['code' => 400, 'message' => 'Unable to complete operation.']])]);

        $this->getJson('/bencana')->assertOk()->assertJsonPath('events', [])->assertJsonPath('updated', null)
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->getJson('/bencana')->assertOk();
        Http::assertSentCount(1); // failure also cached for 5 minutes
        $this->assertSame('', Cache::get('disaster:fetched'));
    }

    public function test_home_shows_the_disaster_card(): void
    {
        Http::fake(); // the home page itself must not call BNPB
        $this->get('/')->assertOk()->assertSee('id="bnpbCard"', false)->assertSee(json_encode(route('disaster.show')), false)->assertSee('Sumber: BNPB');
        Http::assertNothingSent();
    }
}
