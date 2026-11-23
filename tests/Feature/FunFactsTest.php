<?php

namespace Tests\Feature;

use App\Support\AssistantTools;
use App\Support\FunFacts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FunFactsTest extends TestCase
{
    use RefreshDatabase;

    public function test_random_statistic_from_the_data_portal(): void
    {
        config(['services.data_portal.url' => 'https://portal.test']);
        Http::fake([
            'portal.test/api/v1/endpoints' => Http::response(['groups' => [['id' => 'dataset-pertanian', 'items' => [
                ['dataset' => 'produksi-padi', 'title' => 'Produksi Padi', 'period' => '2018-2025'],
                ['dataset' => 'kode-komoditas', 'title' => 'Referensi Kode Komoditas']]]]]), // reference table: never picked
            'portal.test/api/v1/datasets/produksi-padi/records*' => Http::response(['total' => 2, 'fields' => [['id' => 'tahun']],
                'records' => [['tahun' => 2018, 'ton' => 1], ['tahun' => 2025, 'ton' => 9]]]),
        ]);

        $stat = FunFacts::statistic();
        $this->assertSame(['statistik', 'Produksi Padi'], [$stat['jenis'], $stat['dataset']]);
        $this->assertSame(2025, $stat['data_terbaru'][0]['tahun']); // newest first
    }

    public function test_a_sourced_fact_even_when_live_sources_are_down(): void
    {
        Http::fake(fn () => Http::response('down', 502)); // BMKG, Open-Meteo, price panel, news, BNPB, portal all down
        config(['services.data_portal.url' => '']);
        $all = array_column(FunFacts::FIXED, 0);
        foreach (range(1, 5) as $i) {
            $f = AssistantTools::run('fakta_unik', []);
            $this->assertContains($f['fakta'], $all); // no SPPG rows either: only fixed, sourced facts remain
            $this->assertNotSame('', $f['sumber']);
        }
    }
}
