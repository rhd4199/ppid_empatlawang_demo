<?php

namespace Tests\Feature;

use App\Models\Document;
use Database\Seeders\ProcurementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcurementRegulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_fills_regulation_tab_without_duplicates(): void
    {
        Storage::fake('public');

        $this->seed(ProcurementSeeder::class);
        $this->seed(ProcurementSeeder::class);

        $this->assertSame(8, Document::where('category', 'pengadaan_regulasi')->count());
        Storage::disk('public')->assertExists('documents/pengadaan/perbup-empatlawang-15-2020.pdf');

        $this->get('/pengadaan')->assertOk()
            ->assertDontSee('Belum ada dokumen regulasi')
            ->assertSee('Peraturan Presiden Nomor 16 Tahun 2018 tentang Pengadaan Barang/Jasa Pemerintah')
            ->assertSee('Peraturan Bupati Empat Lawang Nomor 15 Tahun 2020')
            ->assertSee('https://peraturan.bpk.go.id/Details/318647/perpres-no-46-tahun-2025', false);
    }
}
