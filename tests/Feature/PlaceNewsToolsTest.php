<?php

namespace Tests\Feature;

use App\Support\AssistantTools;
use App\Support\GoogleNews;
use App\Support\PlaceFinder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlaceNewsToolsTest extends TestCase
{
    public function test_places_from_osm_with_google_maps_links_and_cache(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([
            ['name' => 'Wisma Contoh', 'type' => 'guest_house', 'display_name' => 'Wisma Contoh, Tebing Tinggi, Empat Lawang', 'lat' => '-3.6', 'lon' => '103.07'],
            ['name' => '', 'type' => 'hotel', 'display_name' => 'Jl. X, Pendopo, Empat Lawang', 'lat' => '-3.9', 'lon' => '102.9'],
            ['name' => 'Hotel Pagar Alam', 'type' => 'hotel', 'display_name' => 'Pagar Alam Selatan, Pagar Alam', 'lat' => '-4.02', 'lon' => '103.25'], // outside: dropped
        ])]);

        $r = PlaceFinder::find('tempat nginap', 'Tebing Tinggi');
        $this->assertSame('penginapan', $r['jenis']);
        $this->assertSame('https://www.google.com/maps/search/?api=1&query=penginapan%20Tebing%20Tinggi%20Empat%20Lawang', $r['google_maps']);
        $this->assertSame(['Wisma Contoh', '(tanpa nama di peta)'], array_column($r['tempat'], 'nama')); // same place from 2 terms: once
        $this->assertSame('https://www.google.com/maps/search/?api=1&query=-3.6,103.07', $r['tempat'][0]['peta']);
        Http::assertSentCount(2); // "hotel" + "guest house", bounded to the regency
        Http::assertSent(fn (Request $q) => $q['bounded'] == 1 && $q['viewbox'] === PlaceFinder::VIEWBOX && $q->hasHeader('User-Agent'));

        PlaceFinder::find('penginapan', 'Tebing Tinggi');
        Http::assertSentCount(2); // cached
        $this->assertArrayHasKey('google_maps', AssistantTools::run('cari_tempat', ['jenis' => 'rumah makan']));
    }

    public function test_osm_failure_still_gives_google_maps_link(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response('down', 500)]);
        $r = PlaceFinder::find('wisata');
        $this->assertSame([], $r['tempat']);
        $this->assertStringContainsString('google.com/maps/search', $r['google_maps']);
    }

    public function test_google_news_scoped_to_empat_lawang_without_links(): void
    {
        $rss = '<?xml version="1.0"?><rss><channel><item><title>Banjir Rendam Desa - rmolsumsel.id</title><link>https://news.google.com/x</link>'
            . '<pubDate>Sun, 22 Nov 2026 03:00:00 GMT</pubDate><source url="https://rmolsumsel.id">rmolsumsel.id</source></item></channel></rss>';
        Http::fake(['news.google.com/*' => Http::sequence()->push($rss)->push('x', 500)]); // one fake per test: a second fake would not replace it

        $this->assertSame([['judul' => 'Banjir Rendam Desa', 'sumber' => 'rmolsumsel.id', 'tanggal' => '22-11-2026']], GoogleNews::search('banjir'));
        Http::assertSent(fn (Request $q) => $q['q'] === '"Empat Lawang" banjir' && $q['hl'] === 'id');
        $this->assertSame('Banjir Rendam Desa', AssistantTools::run('berita_google', ['kata_kunci' => 'banjir'])['berita'][0]['judul']);

        cache()->flush(); // next call hits the 500
        $this->assertIsString(AssistantTools::run('berita_google', ['kata_kunci' => 'x'])['berita']);
    }
}
