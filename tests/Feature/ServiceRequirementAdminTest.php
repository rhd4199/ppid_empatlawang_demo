<?php

namespace Tests\Feature;

use App\Models\ServiceRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequirementAdminTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Ganti Alamat KTP',
            'category' => 'kependudukan',
            'agency' => 'Disdukcapil',
            'requirements' => '<ul><li>KK asli</li><li>Surat pindah</li></ul>',
            'steps' => '<ol><li>Datang ke Disdukcapil</li></ol>',
            'cost' => 'Gratis',
            'duration' => '1 hari kerja',
            'legal_basis' => 'UU 24/2013',
            'source_url' => 'https://disdukcapil.empatlawangkab.go.id/layanan',
            'checked_at' => '2026-11-20',
            'keywords' => 'pindah alamat, e-ktp',
            'is_published' => 1,
        ], $override);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.service-requirements.index'))->assertRedirect(route('login'));
    }

    public function test_admin_crud_flow(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.service-requirements.create'))->assertOk()->assertSee('Kata Kunci / Sinonim');

        $this->post(route('admin.service-requirements.store'), $this->payload())
            ->assertRedirect(route('admin.service-requirements.index'));
        $item = ServiceRequirement::sole();
        $this->assertSame('ganti-alamat-ktp', $item->slug);
        $this->assertTrue($item->is_published);
        $this->assertSame('2026-11-20', $item->checked_at->format('Y-m-d'));

        // Same title → unique slug
        $this->post(route('admin.service-requirements.store'), $this->payload());
        $this->assertDatabaseHas('service_requirements', ['slug' => 'ganti-alamat-ktp-2']);

        $this->get(route('admin.service-requirements.index'))->assertOk()
            ->assertSee('Ganti Alamat KTP')->assertSee('Kependudukan');
        $this->get(route('admin.service-requirements.index', ['q' => 'E-KTP', 'category' => 'kependudukan']))
            ->assertOk()->assertSee('Ganti Alamat KTP');
        $this->get(route('admin.service-requirements.index', ['category' => 'kendaraan']))
            ->assertOk()->assertDontSee('Ganti Alamat KTP');
        $this->get(route('admin.service-requirements.edit', $item->id))->assertOk()->assertSee('Disdukcapil');

        // Update: title changes, slug stays; unchecked switch → draft
        $this->put(route('admin.service-requirements.update', $item->id), $this->payload([
            'title' => 'Perubahan Alamat KTP-el',
            'slug' => '',
            'is_published' => null,
        ]))->assertRedirect(route('admin.service-requirements.index'));
        $item->refresh();
        $this->assertSame('Perubahan Alamat KTP-el', $item->title);
        $this->assertSame('ganti-alamat-ktp', $item->slug);
        $this->assertFalse($item->is_published);

        // Toggle → published again
        $this->post(route('admin.service-requirements.toggle-status', $item->id))->assertRedirect();
        $this->assertTrue($item->refresh()->is_published);

        // Bulk unpublish both, then delete one
        $ids = ServiceRequirement::pluck('id')->all();
        $this->post(route('admin.service-requirements.bulk-action'), ['ids' => $ids, 'action' => 'unpublish'])
            ->assertRedirect(route('admin.service-requirements.index'));
        $this->assertSame(0, ServiceRequirement::where('is_published', true)->count());

        $this->post(route('admin.service-requirements.bulk-action'), ['ids' => [$ids[1]], 'action' => 'delete']);
        $this->assertSame(1, ServiceRequirement::count());

        $this->delete(route('admin.service-requirements.destroy', $item->id))
            ->assertRedirect(route('admin.service-requirements.index'));
        $this->assertSame(0, ServiceRequirement::count());
    }

    public function test_validation_rejects_bad_input(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.service-requirements.store'), $this->payload([
            'title' => '',
            'category' => 'bukan_kategori',
            'agency' => '',
            'requirements' => '',
            'source_url' => 'javascript:alert(1)',
            'checked_at' => now()->addDays(3)->format('Y-m-d'),
            'slug' => 'ada spasi',
        ]))->assertSessionHasErrors(['title', 'category', 'agency', 'requirements', 'source_url', 'checked_at', 'slug']);
        $this->assertSame(0, ServiceRequirement::count());

        $this->post(route('admin.service-requirements.store'), $this->payload(['title' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('title');

        // Duplicate slug on another row
        $a = ServiceRequirement::create($this->payload(['slug' => 'balik-nama-kendaraan', 'category' => 'kendaraan']));
        $b = ServiceRequirement::create($this->payload());
        $this->put(route('admin.service-requirements.update', $b->id), $this->payload(['slug' => $a->slug]))
            ->assertSessionHasErrors('slug');
        // Own slug is fine
        $this->put(route('admin.service-requirements.update', $a->id), $this->payload(['slug' => $a->slug, 'category' => 'kendaraan']))
            ->assertSessionHasNoErrors();

        $this->post(route('admin.service-requirements.bulk-action'), ['ids' => [999], 'action' => 'drop'])
            ->assertSessionHasErrors(['ids.0', 'action']);
        $this->assertSame(2, ServiceRequirement::count());
    }
}
