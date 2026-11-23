<?php

namespace Tests\Feature;

use App\Support\AssistantTools;
use App\Support\PortalTools;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PortalToolsTest extends TestCase
{
    private const BASE = 'https://portal.test';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.data_portal.url' => self::BASE . '/']); // trailing slash is tolerated
    }

    public function test_tools_follow_the_configured_base_url_and_can_be_switched_off(): void
    {
        $names = fn () => array_column(array_column(AssistantTools::definitions(), 'function'), 'name');
        $this->assertContains('portal_baris', $names());

        config(['services.data_portal.url' => '']);
        $this->assertNotContains('portal_baris', $names());
        $this->assertArrayHasKey('error', AssistantTools::run('portal_baris', ['dataset' => 'x']));
    }

    public function test_dataset_search_info_and_rows_use_the_api_and_cache(): void
    {
        Http::fake([
            'portal.test/api/v1/datasets?q=kemiskinan' => Http::response(['count' => 1, 'result' => [['name' => 'kemiskinan', 'title' => 'Penduduk Miskin', 'source' => 'BPS', 'links' => ['x']]]]),
            'portal.test/api/v1/datasets/kemiskinan' => Http::response(['result' => ['name' => 'kemiskinan', 'title' => 'Penduduk Miskin', 'rows' => 3, 'license' => 'CC-BY-4.0', 'fields' => [['id' => 'tahun', 'label' => 'Tahun'], ['id' => 'persen']]]]),
            'portal.test/api/v1/datasets/kemiskinan/records*' => Http::response(['dataset' => 'kemiskinan', 'total' => 3, 'fields' => [['id' => 'tahun'], ['id' => 'persen']], 'records' => [['tahun' => 2015, 'persen' => 15.0], ['tahun' => 2024, 'persen' => 13.1]]]),
        ]);

        $this->assertSame('kemiskinan', PortalTools::run('portal_cari_dataset', ['kata_kunci' => 'kemiskinan'])['dataset'][0]['nama']);
        $info = PortalTools::run('portal_info_dataset', ['dataset' => 'kemiskinan']);
        $this->assertSame(['tahun', 'persen'], array_column($info['kolom'], 'id'));

        $rows = PortalTools::run('portal_baris', ['dataset' => 'kemiskinan', 'filter' => ['tahun' => '2024', 'bad col!' => 'x'], 'jumlah' => 500]);
        $this->assertSame([3, 2, ['tahun' => 2024, 'persen' => 13.1]], [$rows['total'], $rows['ditampilkan'], $rows['baris'][0]]); // newest year first
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/records') && $r['tahun'] === '2024' && $r['limit'] == 1000 && ! isset($r['bad col!'])); // all matches, sorted here

        PortalTools::run('portal_baris', ['dataset' => 'kemiskinan', 'filter' => ['tahun' => '2024'], 'jumlah' => 500]);
        Http::assertSentCount(3); // the repeated rows call came from the cache

        // through the AI's dispatcher (a bug once sent portal_* to "tool tidak ada")
        $this->assertSame(3, AssistantTools::run('portal_baris', ['dataset' => 'kemiskinan', 'filter' => ['tahun' => '2024'], 'jumlah' => 500])['total']);
    }

    public function test_region_tools_and_errors_are_honest(): void
    {
        Http::fake([
            'portal.test/api/v1/wilayah/desa?kecamatan=Pendopo' => Http::response(['count' => 2, 'result' => [
                ['kode_desa' => '16.11.02.2001', 'desa' => 'Pendopo', 'kecamatan' => 'Pendopo', 'links' => ['self' => 'x']],
                ['kode_desa' => '16.11.02.2002', 'desa' => 'Tanjung Kupang', 'kecamatan' => 'Pendopo']]]),
            'portal.test/api/v1/wilayah/desa/16.11.02.2001' => Http::response(['result' => ['desa' => 'Pendopo', 'indikator' => ['kemiskinan' => ['data' => ['persen' => 9]]]]]),
            'portal.test/api/v1/datasets/ngawur/records*' => Http::response(['error' => 'kolom tidak dikenal: x. Kolom tersedia: tahun, persen'], 400),
            'portal.test/api/v1/datasets/mati' => Http::response('bad gateway', 502),
        ]);

        $desa = PortalTools::run('portal_wilayah', ['jenis' => 'desa', 'kecamatan' => 'Pendopo', 'nama_desa' => 'kupang']);
        $this->assertSame([1, 'Tanjung Kupang'], [$desa['jumlah'], $desa['desa'][0]['desa']]);
        $this->assertSame(9, PortalTools::run('portal_wilayah', ['jenis' => 'profil_desa', 'kode_desa' => '16.11.02.2001'])['indikator']['kemiskinan']['data']['persen']);
        $this->assertArrayHasKey('error', PortalTools::run('portal_wilayah', ['jenis' => 'profil_desa', 'kode_desa' => 'Pendopo'])); // not a Kemendagri code

        $this->assertStringContainsString('Kolom tersedia: tahun, persen', PortalTools::run('portal_baris', ['dataset' => 'ngawur', 'filter' => ['x' => '1']])['error']); // 4xx message reaches the model
        $this->assertStringContainsString('502', PortalTools::run('portal_info_dataset', ['dataset' => 'mati'])['error']);
    }

    public function test_point_layers_geo_layers_changelog_and_survey(): void
    {
        $rows = array_map(fn ($i) => ['nama' => "Puskesmas {$i}", 'kecamatan' => 'Pendopo', 'lat' => -3.8, 'lng' => 102.9], range(1, 40));
        Http::fake([
            'portal.test/api/v1/poi?limit=10&q=pustu' => Http::response(['dataset' => 'poi-empat-lawang', 'total' => 0, 'records' => []]),
            'portal.test/api/v1/fasilitas*' => Http::response(['dataset' => 'fasilitas-desa', 'total' => 1, 'records' => [['nama' => 'Pustu Nanjungan']]]),
            'portal.test/api/v1/poi*' => Http::response(['dataset' => 'poi-empat-lawang', 'total' => 40, 'records' => $rows]),
            'portal.test/api/v1/geo/kecamatan' => Http::response(['type' => 'FeatureCollection', 'features' => [
                ['properties' => ['code' => '16.11.02', 'name' => 'Pendopo'], 'geometry' => ['type' => 'Polygon', 'coordinates' => [[[0, 0]]]]],
                ['properties' => ['code' => '16.11.04', 'name' => 'Tebing Tinggi'], 'geometry' => ['type' => 'Polygon', 'coordinates' => [[[1, 1]]]]]]]),
            'portal.test/api/v1/changelog' => Http::response(['entries' => array_map(fn ($i) => ['date' => "2026-11-0{$i}", 'title' => "perubahan {$i}"], range(1, 8))]),
            'portal.test/api/v1/feedback/summary' => Http::response(['responden' => 12, 'rata_rata' => 4.5]),
        ]);

        $poi = PortalTools::run('portal_titik', ['jenis' => 'poi', 'q' => 'puskesmas', 'filter' => ['kecamatan' => 'Pendopo'], 'jumlah' => 99]);
        $this->assertSame([40, PortalTools::MAX_ROWS, PortalTools::MAX_ROWS], [$poi['total'], $poi['ditampilkan'], count($poi['baris'])]);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/api/v1/poi') && $r['kecamatan'] === 'Pendopo' && $r['q'] === 'puskesmas');
        $this->assertSame('poi', PortalTools::run('portal_titik', ['jenis' => 'hacker'])['lapisan']); // unknown kind falls back to poi
        $this->assertSame('fasilitas', PortalTools::run('portal_titik', ['jenis' => 'poi', 'q' => 'pustu'])['lapisan']); // empty POI → facilities

        $geo = PortalTools::run('portal_geo', ['lapisan' => 'kecamatan', 'nama' => 'tebing']);
        $this->assertSame([2, 1, [['code' => '16.11.04', 'name' => 'Tebing Tinggi']]], [$geo['jumlah_fitur'], $geo['cocok'], $geo['atribut']]); // no geometry
        $this->assertArrayHasKey('error', PortalTools::run('portal_geo', ['lapisan' => '../etc']));

        $this->assertCount(5, PortalTools::run('portal_pembaruan', [])['pembaruan']);
        $this->assertSame(12, PortalTools::run('portal_survei', [])['responden']);
    }
}
