<?php

namespace Tests\Feature;

use App\Models\SppgUnit;
use Database\Seeders\SppgUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppgPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_lists_published_units_with_summary_and_source(): void
    {
        $this->seed(SppgUnitSeeder::class);

        $html = $this->get(route('sppg.index'))->assertOk()
            ->assertSee('SPPG di Empat Lawang')
            ->assertSee('SPPG Empat Lawang Ulu Musi Muara Kalangan')
            ->assertSee('Tebing Tinggi')
            ->assertSee('bgn.go.id/radar-mbg')
            ->assertDontSee('SPPG Kota Pagar Alam') // hidden: kitchen outside the regency
            ->getContent();

        $this->assertSame(23, substr_count($html, 'data-sppg-row'));
        $this->assertSame(23, SppgUnit::published()->count());
    }

    public function test_filter_by_kecamatan_and_search_by_school(): void
    {
        $this->seed(SppgUnitSeeder::class);

        $html = $this->get(route('sppg.index', ['kecamatan' => 'Ulu Musi']))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'data-sppg-row'));

        // a school served by the kitchen is found and shown bold
        $html = $this->get(route('sppg.index', ['q' => 'SD Negeri 7 Muara Pinang']))->assertOk()
            ->assertSee('SPPG Empat Lawang Muara Pinang Muara Timbuk')->getContent();
        $this->assertStringContainsString('fw-bold">SD Negeri 7 Muara Pinang', $html);

        // unknown kecamatan is ignored, not an error
        $this->get(route('sppg.index', ['kecamatan' => 'Antah Berantah']))->assertOk();
        $this->get(route('sppg.index', ['q' => 'zzzz tidak ada']))->assertOk()->assertSee('SPPG tidak ditemukan');
    }

    public function test_hidden_unit_and_sitemap_and_menu(): void
    {
        SppgUnit::create(['name' => 'SPPG Rahasia Uji', 'kecamatan' => 'Saling', 'is_published' => false]);
        SppgUnit::create(['name' => 'SPPG Tampil Uji', 'kecamatan' => 'Saling', 'latitude' => -3.7, 'longitude' => 102.9]);

        $this->get(route('sppg.index', ['q' => 'uji']))->assertOk()
            ->assertSee('SPPG Tampil Uji')->assertDontSee('SPPG Rahasia Uji')
            ->assertSee('id="sppgMap"', false);
        $this->get(route('home'))->assertSee(route('sppg.index'));
        $this->get(route('sitemap'))->assertSee(route('sppg.index'));
    }

    public function test_seeder_keeps_admin_edits(): void
    {
        $this->seed(SppgUnitSeeder::class);
        $unit = SppgUnit::where('bgn_id', '5ZKMLWVL')->first();
        $this->travel(1)->minutes();
        $unit->update(['operator' => 'Yayasan Uji']);

        $this->seed(SppgUnitSeeder::class);
        $this->assertSame('Yayasan Uji', $unit->fresh()->operator);
        $this->assertSame(24, SppgUnit::count());
        $this->assertSame(29, $unit->fresh()->institutions_count);
    }
}
