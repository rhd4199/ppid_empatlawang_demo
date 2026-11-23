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
            'portal.test/api/v1/datasets/kemiskinan/records*' => Http::response(['dataset' => 'kemiskinan', 'total' => 3, 'fields' => [['id' => 'tahun'], ['id' => 'persen']], 'records' => [['tahun' => 2024, 'persen' => 13.1]]]),
        ]);

        $this->assertSame('kemiskinan', PortalTools::run('portal_cari_dataset', ['kata_kunci' => 'kemiskinan'])['dataset'][0]['nama']);
        $info = PortalTools::run('portal_info_dataset', ['dataset' => 'kemiskinan']);
        $this->assertSame(['tahun', 'persen'], array_column($info['kolom'], 'id'));

        $rows = PortalTools::run('portal_baris', ['dataset' => 'kemiskinan', 'filter' => ['tahun' => '2024', 'bad col!' => 'x'], 'jumlah' => 500]);
        $this->assertSame([3, 1, [['tahun' => 2024, 'persen' => 13.1]]], [$rows['total'], $rows['ditampilkan'], $rows['baris']]);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/records') && $r['tahun'] === '2024' && $r['limit'] == PortalTools::MAX_ROWS && ! isset($r['bad col!']));

        PortalTools::run('portal_baris', ['dataset' => 'kemiskinan', 'filter' => ['tahun' => '2024'], 'jumlah' => 500]);
        Http::assertSentCount(3); // the repeated rows call came from the cache
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
}
