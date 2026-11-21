<?php

namespace Tests\Feature;

use App\Models\ServiceRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequirementPublicTest extends TestCase
{
    use RefreshDatabase;

    private function make(array $data): ServiceRequirement
    {
        return ServiceRequirement::create($data + [
            'category' => 'kependudukan',
            'agency' => 'Disdukcapil Kabupaten Empat Lawang',
            'requirements' => '<ol><li>Surat kehilangan dari kepolisian</li></ol>',
            'steps' => '<ol><li>Datang ke Disdukcapil</li></ol>',
            'cost' => 'Gratis',
            'duration' => '1 jam',
            'legal_basis' => 'Perpres 96/2018',
            'source_url' => 'https://dukcapil.kemendagri.go.id/x.pdf',
            'checked_at' => '2026-11-21',
            'is_published' => true,
        ]);
    }

    public function test_index_groups_by_category_and_hides_drafts(): void
    {
        $this->make(['title' => 'KTP-el Hilang atau Rusak', 'keywords' => 'e-ktp hilang']);
        $this->make(['title' => 'SIM Baru', 'category' => 'kendaraan', 'agency' => 'Satpas Polres']);
        $this->make(['title' => 'Layanan Draft Rahasia', 'is_published' => false]);

        $this->get('/persyaratan')->assertOk()
            ->assertSee('<title>Persyaratan Layanan | PPID Kabupaten Empat Lawang</title>', false)
            ->assertSeeInOrder(['Kependudukan', 'KTP-el Hilang atau Rusak', 'Kendaraan', 'SIM Baru'])
            ->assertSee('pastikan kembali ke instansi')
            ->assertDontSee('Layanan Draft Rahasia');

        $this->get('/persyaratan?kategori=kendaraan')->assertOk()
            ->assertSee('SIM Baru')->assertDontSee('KTP-el Hilang');
        $this->get('/persyaratan?kategori=tidak-ada')->assertOk()->assertSee('SIM Baru');
    }

    public function test_search_matches_every_word_in_title_keywords_or_agency(): void
    {
        $this->make(['title' => 'KTP-el Hilang atau Rusak', 'keywords' => 'e-ktp hilang, ganti ktp']);
        $this->make(['title' => 'Balik Nama Kendaraan Bekas', 'category' => 'kendaraan', 'keywords' => 'bbn, motor bekas']);
        $this->make(['title' => 'Draft KTP', 'keywords' => 'ktp', 'is_published' => false]);

        $this->get('/persyaratan?q=ktp+hilang')->assertOk()
            ->assertSee('KTP-el Hilang atau Rusak')->assertDontSee('Balik Nama Kendaraan')->assertDontSee('Draft KTP');
        $this->get('/persyaratan?q=MOTOR')->assertOk()->assertSee('Balik Nama Kendaraan Bekas');
        $this->get('/persyaratan?q=disdukcapil')->assertOk()->assertSee('KTP-el Hilang');
        $this->get('/persyaratan?q=100%25_')->assertOk()->assertSee('Layanan tidak ditemukan');
    }

    public function test_detail_shows_all_sections_and_drafts_are_404(): void
    {
        $item = $this->make(['title' => 'KTP-el Hilang atau Rusak']);
        $this->make(['title' => 'KK Baru']);
        $draft = $this->make(['title' => 'Draft', 'is_published' => false]);

        $this->get('/persyaratan/' . $item->slug)->assertOk()
            ->assertSee('<title>KTP-el Hilang atau Rusak | PPID Kabupaten Empat Lawang</title>', false)
            ->assertSee('<li>Surat kehilangan dari kepolisian</li>', false)
            ->assertSee('Datang ke Disdukcapil')
            ->assertSee('Perpres 96/2018')
            ->assertSee('21 November 2026')
            ->assertSee('dukcapil.kemendagri.go.id')
            ->assertSee('Informasi umum, bukan keputusan resmi instansi')
            ->assertSee('KK Baru');

        $this->get('/persyaratan/' . $draft->slug)->assertNotFound();
        $this->get('/persyaratan/tidak-ada')->assertNotFound();
    }

    public function test_sitemap_lists_published_only(): void
    {
        $item = $this->make(['title' => 'SIM Baru']);
        $draft = $this->make(['title' => 'Draft', 'is_published' => false]);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee(route('service-requirements.index'), false)
            ->assertSee(route('service-requirements.show', $item->slug), false)
            ->assertDontSee(route('service-requirements.show', $draft->slug), false);
    }
}
