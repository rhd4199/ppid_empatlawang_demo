<?php

namespace Tests\Feature;

use App\Models\SppgUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppgAdminTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'SPPG Empat Lawang Saling Uji',
            'kecamatan' => 'Saling',
            'village' => 'Lubuk Kelumpang',
            'address' => 'Jl. Lintas Sumatera',
            'operator' => 'Yayasan Gizi Uji',
            'status' => 'persiapan',
            'beneficiaries' => '3000',
            'institutions_text' => "SD Negeri 1 Saling | Lubuk Kelumpang | Saling\n\nPAUD Melati",
            'latitude' => '-3.7012',
            'longitude' => '102.95',
            'source_url' => 'https://www.bgn.go.id/radar-mbg/',
            'checked_at' => '2026-11-20',
            'is_published' => 1,
        ], $override);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.sppg.index'))->assertRedirect(route('login'));
        $this->post(route('admin.sppg.store'), $this->payload())->assertRedirect(route('login'));
        $this->assertSame(0, SppgUnit::count());
    }

    public function test_admin_crud_flow(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.sppg.create'))->assertOk()->assertSee('Instansi yang Dilayani');
        $this->post(route('admin.sppg.store'), $this->payload())->assertRedirect(route('admin.sppg.index'));

        $unit = SppgUnit::sole();
        $this->assertSame('persiapan', $unit->status);
        $this->assertSame(3000, $unit->beneficiaries);
        $this->assertSame(2, $unit->institutions_count);
        $this->assertSame(['name' => 'SD Negeri 1 Saling', 'village' => 'Lubuk Kelumpang', 'kecamatan' => 'Saling'], $unit->institutions[0]);
        $this->assertNull($unit->institutions[1]['village']);

        $this->get(route('admin.sppg.index', ['kecamatan' => 'Saling']))->assertOk()->assertSee('SPPG Empat Lawang Saling Uji')->assertSee('SPPG (MBG)');
        $this->get(route('admin.sppg.edit', $unit->id))->assertOk()->assertSee('SD Negeri 1 Saling | Lubuk Kelumpang | Saling');

        $this->put(route('admin.sppg.update', $unit->id), $this->payload(['status' => 'operasional', 'institutions_text' => 'SMP Negeri 1 Saling']))
            ->assertRedirect(route('admin.sppg.index'));
        $this->assertSame(1, $unit->fresh()->institutions_count);
        $this->get(route('sppg.index'))->assertSee('SPPG Empat Lawang Saling Uji');

        // hide → gone from the public page
        $this->post(route('admin.sppg.toggle-status', $unit->id))->assertRedirect();
        $this->assertFalse($unit->fresh()->is_published);
        $this->get(route('sppg.index'))->assertDontSee('SPPG Empat Lawang Saling Uji');

        $this->delete(route('admin.sppg.destroy', $unit->id))->assertRedirect(route('admin.sppg.index'));
        $this->assertSame(0, SppgUnit::count());
    }

    public function test_validation(): void
    {
        $this->actingAs(User::factory()->create());
        SppgUnit::create(['name' => 'Lama', 'kecamatan' => 'Saling', 'bgn_id' => 'ABC12345']);

        $this->from(route('admin.sppg.create'))
            ->post(route('admin.sppg.store'), $this->payload(['name' => '', 'kecamatan' => '', 'status' => 'tutup', 'bgn_id' => 'ABC12345',
                'latitude' => '40', 'longitude' => '', 'source_url' => 'javascript:alert(1)', 'checked_at' => '2999-01-01']))
            ->assertRedirect(route('admin.sppg.create'))
            ->assertSessionHasErrors(['name', 'kecamatan', 'status', 'bgn_id', 'latitude', 'longitude', 'source_url', 'checked_at']);
        $this->assertSame(1, SppgUnit::count());
    }
}
