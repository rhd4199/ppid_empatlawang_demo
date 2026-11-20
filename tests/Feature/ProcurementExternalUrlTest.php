<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementExternalUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_without_file_links_to_external_url(): void
    {
        foreach (['pengadaan_info', 'pengadaan_regulasi'] as $category) {
            Document::create([
                'title' => "Dok $category",
                'category' => $category,
                'external_url' => "https://sirup.inaproc.id/$category",
                'is_published' => true,
            ]);
        }

        $this->get('/pengadaan')->assertOk()
            ->assertSee('href="https://sirup.inaproc.id/pengadaan_info" class="btn btn-sm btn-outline-primary rounded-pill px-3 text-nowrap" target="_blank" rel="noopener"', false)
            ->assertSee('href="https://sirup.inaproc.id/pengadaan_regulasi"', false);
    }

    public function test_admin_saves_external_url(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.procurements.store'), [
            'title' => 'RUP 2026',
            'category' => 'pengadaan_info',
            'external_url' => 'https://sirup.inaproc.id/sirup/rekap/klpd/D163',
            'is_published' => 1,
        ])->assertRedirect(route('admin.procurements.index'));

        $doc = Document::firstWhere('title', 'RUP 2026');
        $this->assertSame('https://sirup.inaproc.id/sirup/rekap/klpd/D163', $doc->external_url);

        $this->put(route('admin.procurements.update', $doc), [
            'title' => 'RUP 2026',
            'category' => 'pengadaan_info',
            'external_url' => '/kontak',
        ])->assertRedirect();
        $this->assertSame('/kontak', $doc->fresh()->external_url);

        $this->put(route('admin.procurements.update', $doc), [
            'title' => 'RUP 2026',
            'category' => 'pengadaan_info',
            'external_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('external_url');
        $this->assertSame('/kontak', $doc->fresh()->external_url);
    }
}
