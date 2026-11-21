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

        foreach (['nib-umk-perorangan', 'spp-irt-pirt', 'sertifikat-halal-self-declare', 'sertifikat-halal-reguler',
            'izin-edar-bpom-md', 'pbg-pengganti-imb'] as $slug) {
            $item = ServiceRequirement::where('slug', $slug)->sole();
            $this->assertSame('perizinan_usaha', $item->category);
            $this->assertNotEmpty($item->agency);
            $this->assertNotEmpty($item->legal_basis);
            $this->assertStringStartsWith('https://', $item->source_url);
        }

        foreach (['sim-baru', 'sim-perpanjangan', 'sim-hilang-rusak', 'pajak-tahunan-stnk', 'stnk-5-tahunan',
            'balik-nama-kendaraan', 'mutasi-kendaraan', 'bpkb-hilang'] as $slug) {
            $item = ServiceRequirement::where('slug', $slug)->sole();
            $this->assertSame('kendaraan', $item->category);
            $this->assertStringContainsString('PP 76/2020', $item->cost . $item->legal_basis);
        }

        foreach (['balik-nama-sertifikat-jual-beli', 'balik-nama-sertifikat-waris', 'balik-nama-sertifikat-hibah',
            'pendaftaran-tanah-pertama-ptsl', 'sertifikat-tanah-hilang-rusak', 'pemecahan-penggabungan-bidang',
            'roya-hak-tanggungan'] as $slug) {
            $item = ServiceRequirement::where('slug', $slug)->sole();
            $this->assertSame('pertanahan', $item->category);
            $this->assertStringContainsString('PP 128/2015', $item->cost . $item->legal_basis);
            $this->assertStringStartsWith('https://', $item->source_url);
        }

        // sqlite ignores varchar lengths; Postgres/MySQL do not.
        foreach (ServiceRequirement::all() as $item) {
            $this->assertLessThanOrEqual(255, mb_strlen($item->title), $item->slug);
            $this->assertLessThanOrEqual(255, mb_strlen($item->agency), $item->slug);
            $this->assertLessThanOrEqual(500, mb_strlen((string) $item->source_url), $item->slug);
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
