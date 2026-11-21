<?php

namespace Tests\Feature;

use App\Models\ServiceRequirement;
use Database\Seeders\ServiceRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequirementSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent_and_keeps_admin_edits(): void
    {
        $this->travelTo('2026-11-21 08:00:00');
        $this->seed(ServiceRequirementSeeder::class);
        $count = ServiceRequirement::count();
        $this->assertGreaterThanOrEqual(14, $count);

        foreach (['ganti-alamat-ktp-pindah-dalam-kabupaten', 'pindah-keluar-skpwni', 'pindah-datang', 'ktp-el-baru',
            'ktp-el-hilang-rusak', 'kk-baru', 'kk-perubahan-data', 'akta-kelahiran', 'akta-kematian',
            'akta-perkawinan', 'akta-perceraian', 'perubahan-nama', 'kia'] as $slug) {
            $item = ServiceRequirement::where('slug', $slug)->sole();
            $this->assertSame('kependudukan', $item->category);
            $this->assertStringStartsWith('<ol><li>', $item->requirements);
            $this->assertNotEmpty($item->steps);
            $this->assertNotEmpty($item->legal_basis);
            $this->assertStringStartsWith('https://dukcapil.kemendagri.go.id/', $item->source_url);
            $this->assertSame('2026-11-21', $item->checked_at->format('Y-m-d'));
        }

        // Admin edits one row later; a seeded-but-unedited row gets stale text.
        $this->travel(1)->day();
        ServiceRequirement::where('slug', 'kia')->first()->update(['cost' => 'Diubah admin']);
        ServiceRequirement::where('slug', 'akta-kematian')->update(['cost' => 'lama']);
        ServiceRequirement::where('slug', 'akta-kematian')->update(['updated_at' => ServiceRequirement::where('slug', 'akta-kematian')->value('created_at')]);

        $this->seed(ServiceRequirementSeeder::class);

        $this->assertSame($count, ServiceRequirement::count());
        $this->assertSame('Diubah admin', ServiceRequirement::where('slug', 'kia')->value('cost'));
        $this->assertStringStartsWith('Gratis', ServiceRequirement::where('slug', 'akta-kematian')->value('cost'));
    }
}
