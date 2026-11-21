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

    public function test_stats_sum_impact_and_count_per_type_and_region(): void
    {
        $this->travelTo('2026-11-21 10:00:00');
        Http::fake();
        $event = fn ($id, $date, $region, $type, $impact) => compact('id', 'date', 'region', 'type', 'impact') + ['summary' => '', 'status' => ''];
        Cache::forever('disaster:history', [
            'a' => $event('a', '2026-11-19', 'Seluma', 'Banjir', ['terdampak' => 330, 'rumah terendam' => 66]),
            'b' => $event('b', '2026-11-12', 'Empat Lawang', 'Tanah longsor', ['meninggal' => 1, 'mengungsi' => 12, 'rumah rusak' => 3]),
            'c' => $event('c', '2026-11-05', 'Seluma', 'Banjir', ['terdampak' => 100, 'rumah terendam' => 20]),
            'd' => $event('d', '2026-11-04', 'Kota Bengkulu', 'Cuaca ekstrem', []), // outside REGIONS: counted in total/type only
        ]);
        Cache::put('disaster:fetched', '2026-11-21 09:30:00', 3600); // no BNPB call

        $stats = $this->getJson('/bencana')->assertOk()->json('stats');

        $this->assertSame(4, $stats['total']);
        $this->assertEquals(['terdampak' => 430, 'rumah terendam' => 86, 'meninggal' => 1, 'mengungsi' => 12, 'rumah rusak' => 3], $stats['impact']);
        $this->assertSame(['Banjir' => 2, 'Tanah longsor' => 1, 'Cuaca ekstrem' => 1], $stats['types']);
        $this->assertSame(['Empat Lawang' => 1, 'Lahat' => 0, 'Musi Rawas' => 0, 'Kepahiang' => 0, 'Rejang Lebong' => 0, 'Seluma' => 2], $stats['regions']);
        Http::assertNothingSent();
    }

    public function test_stats_for_zero_events_keep_all_regions(): void
    {
        Http::fake();
        Cache::put('disaster:fetched', '2026-11-21 09:30:00', 3600);

        $json = $this->getJson('/bencana')->assertOk()->json();

        $this->assertSame([], $json['events']);
        $this->assertSame(0, $json['stats']['total']);
        $this->assertSame([], $json['stats']['impact']); // JSON {} → [] in PHP
        $this->assertSame(array_fill_keys(\App\Support\BnpbDisaster::REGIONS, 0), $json['stats']['regions']);
    }

    public function test_home_shows_the_disaster_card(): void
    {
        Http::fake(); // the home page itself must not call BNPB
        $this->get('/')->assertOk()->assertSee('id="bnpbCard"', false)->assertSee(json_encode(route('disaster.show')), false)->assertSee('Sumber: BNPB');
        Http::assertNothingSent();
    }
}
