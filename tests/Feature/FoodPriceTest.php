<?php

namespace Tests\Feature;

use App\Support\AssistantFallback;
use App\Support\AssistantTools;
use App\Support\FoodPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FoodPriceTest extends TestCase
{
    use RefreshDatabase;

    private function row(int $id, string $name, int $today, int $yesterday): array
    {
        return ['id' => $id, 'name' => $name, 'satuan' => 'Rp./Kg', 'today' => $today, 'yesterday' => $yesterday, 'yesterday_date' => '22-11-2026'];
    }

    public function test_regency_prices_cached_and_used_by_tool_and_fallback(): void
    {
        Http::fake(['api-panelhargav2.badanpangan.go.id/*' => Http::response(['status' => 'success', 'data' => [
            $this->row(32, 'Cabai Merah Keriting', 0, 70000),
            $this->row(27, 'Beras Premium', 15000, 14900),
            $this->row(149, 'Daging Kerbau Beku', 0, 0), // no figure: skipped
        ]])]);

        $cabe = FoodPrice::find('cabe'); // "cabe" also finds "Cabai"
        $this->assertSame('Kabupaten Empat Lawang', $cabe['wilayah']);
        $this->assertSame([['komoditas' => 'Cabai Merah Keriting', 'harga' => 70000, 'satuan' => 'Kg', 'tanggal' => '22-11-2026', 'perubahan' => null]], $cabe['harga']);
        $this->assertSame(100, FoodPrice::find('beras')['harga'][0]['perubahan']);
        $this->assertCount(2, FoodPrice::latest()['harga']);
        Http::assertSentCount(1); // cached
        Http::assertSent(fn ($r) => $r['city_id'] == FoodPrice::CITY && $r['level_harga_id'] == FoodPrice::LEVEL);

        $this->assertSame('Cabai Merah Keriting', AssistantTools::run('harga_pangan', ['komoditas' => 'cabai'])['harga'][0]['komoditas']);
        $reply = AssistantFallback::reply('Berapa harga cabe hari ini');
        $this->assertStringContainsString('Cabai Merah Keriting: Rp70.000/Kg', $reply);
        $this->assertStringNotContainsString('GRATIS', $reply);
    }

    public function test_regency_empty_falls_back_to_province_and_failure_is_honest(): void
    {
        Http::fake(['api-panelhargav2.badanpangan.go.id/*' => Http::sequence()
            ->push(['data' => [$this->row(32, 'Cabai Merah Keriting', 0, 0)]])
            ->push(['data' => [$this->row(32, 'Cabai Merah Keriting', 0, 71222)]])]);
        $this->assertSame('Provinsi Sumatera Selatan (rata-rata)', FoodPrice::latest()['wilayah']);

        cache()->flush();
        Http::fake(['api-panelhargav2.badanpangan.go.id/*' => Http::response('down', 500)]);
        $this->assertSame([], FoodPrice::latest());
        $this->assertArrayHasKey('error', AssistantTools::run('harga_pangan', []));
        $this->assertStringContainsString('panelharga.badanpangan.go.id', AssistantFallback::reply('harga cabe'));
    }

    public function test_source_down_serves_last_good_prices_and_is_not_retried_for_30_minutes(): void
    {
        Http::fake(['api-panelhargav2.badanpangan.go.id/*' => Http::response(['data' => [$this->row(27, 'Beras Premium', 15000, 14900)]])]);
        $this->assertCount(1, FoodPrice::latest()['harga']);

        $this->travel(31)->minutes();
        $calls = 0;
        Http::fake(['api-panelhargav2.badanpangan.go.id/*' => function () use (&$calls) { $calls++; throw new \Illuminate\Http\Client\ConnectionException('cURL error 28'); }]);
        $this->assertSame('Beras Premium', FoodPrice::latest()['harga'][0]['komoditas']); // stale copy
        FoodPrice::latest();
        FoodPrice::find('beras');
        $this->assertSame(1, $calls); // the failure is cached too: one timeout per 30 minutes, not per chat
    }
}
