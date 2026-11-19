<?php

namespace Tests\Feature;

use App\Models\Document;
use Database\Seeders\InfoPublicDocumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InfoPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(InfoPublicDocumentSeeder::class);
    }

    public function test_seeded_documents_and_actions(): void
    {
        $this->seed(InfoPublicDocumentSeeder::class); // idempotent
        $this->assertSame(16, Document::count());
        Storage::disk('public')->assertExists(['documents/lkpd-2024.pdf', 'documents/peringatan-dini-cuaca.pdf']);
        $this->assertStringStartsWith('%PDF-', Storage::disk('public')->get('documents/klasifikasi-informasi-dikecualikan.pdf'));

        $lkpd = Document::where('file_path', 'documents/lkpd-2024.pdf')->first();
        $rkpd = Document::whereNotNull('external_url')->first();

        $this->get('/informasi-publik?category=berkala')->assertOk()
            ->assertSee('Laporan Keuangan Pemerintah Daerah (LKPD)')
            ->assertSee(route('informasi-publik.download', $lkpd->id), false)
            ->assertSee($rkpd->external_url, false)
            ->assertDontSee('Panduan Kesiapsiagaan Banjir')
            ->assertSee('06 Jan 2025'); // date from the official site
    }

    public function test_download_forces_attachment_and_counts(): void
    {
        $lkpd = Document::where('file_path', 'documents/lkpd-2024.pdf')->first();

        $this->get(route('informasi-publik.download', $lkpd->id))->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=laporan-keuangan-pemerintah-daerah-lkpd-kabupaten-empat-lawang-tahun-2024.pdf');
        $this->assertSame(1, $lkpd->fresh()->download_count);

        $external = Document::whereNotNull('external_url')->first();
        $this->get(route('informasi-publik.download', $external->id))->assertNotFound();
    }

    public function test_search_is_case_insensitive_and_literal(): void
    {
        $this->get('/informasi-publik?search=lkpd', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()
            ->assertSee('Laporan Keuangan Pemerintah Daerah (LKPD)')
            ->assertSee('Opini BPK') // matches in description
            ->assertDontSee('Panduan Kesiapsiagaan');

        $this->get('/informasi-publik?search=%25')->assertOk()->assertSee('Tidak ada informasi yang ditemukan.');

        $this->get('/informasi-publik?category=berkala&search=APBD')->assertOk()
            ->assertSee('value="APBD"', false); // form keeps the term without JS
    }
}
