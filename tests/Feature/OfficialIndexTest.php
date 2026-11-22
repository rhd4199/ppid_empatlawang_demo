<?php

namespace Tests\Feature;

use App\Models\Official;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficialIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_state(): void
    {
        $this->get('/profil-pejabat')->assertOk()
            ->assertSee('Data pejabat belum tersedia.')
            ->assertSee(route('profiles.index'), false);
    }

    public function test_leaders_first_then_grid_with_bio_modal(): void
    {
        Storage::fake('public');
        $this->seed(\Database\Seeders\OfficialSeeder::class);
        Storage::disk('public')->assertExists('officials/bupati.jpg');
        $count = Official::count();
        $this->seed(\Database\Seeders\OfficialSeeder::class);
        $this->assertSame($count, Official::count()); // re-seed updates, no duplicates
        Official::create(['name' => 'Kadis A', 'position' => 'Kepala Dinas', 'order' => 1000, 'is_published' => true,
            'bio' => str_repeat('Riwayat panjang. ', 20)]);
        Official::create(['name' => 'Hidden', 'position' => 'X', 'order' => 11, 'is_published' => false]);

        $this->get('/profil-pejabat')->assertOk()
            ->assertSeeInOrder(['data-row="heads"', 'H. Joncik Muhammad', 'A. Rifai', 'data-row="secretary"', 'Fauzan Khoiri Denin', 'Pejabat Struktural', 'M. Yunidi Nuzli', 'Sumitro Sukma Bahagia', 'Kadis A'], false)
            ->assertSee('official-card-lead', false)
            ->assertSee('officials/bupati.jpg', false)
            ->assertSee('>YN</div>', false) // initials when no photo
            ->assertSee('data-bs-target="#official-', false)
            ->assertSee('class="om-hero', false)
            ->assertSee('Periode 2025–2030') // chip parsed from bio
            ->assertSee('Pimpinan Daerah</span>', false)
            ->assertDontSee('Hidden')
            ->assertDontSee('Data pejabat belum tersedia.');
    }

    public function test_other_groups_get_their_own_sections_and_admin_can_set_group(): void
    {
        Official::create(['name' => 'Camat B', 'position' => 'Camat Tebing Tinggi', 'group' => 'kecamatan', 'order' => 1, 'is_published' => true]);
        Official::create(['name' => 'AKBP C', 'position' => 'Kapolres Empat Lawang', 'group' => 'forkopimda', 'order' => 1, 'is_published' => true]);
        Official::create(['name' => 'Kadis D', 'position' => 'Kepala Dinas X', 'order' => 10, 'is_published' => true]);

        $this->get('/profil-pejabat')->assertOk()
            ->assertSeeInOrder(['Pejabat Struktural', 'Kadis D', 'Forkopimda', 'AKBP C', 'Camat', 'Camat B'], false);

        // order 1 outside Pemkab is not a Pimpinan Daerah leader card
        $this->assertStringNotContainsString('AKBP C', explode('Pejabat Struktural', $this->get('/profil-pejabat')->getContent())[0]);

        $admin = \App\Models\User::factory()->create();
        $kadis = Official::where('name', 'Kadis D')->sole();
        $this->actingAs($admin)->put(route('admin.officials.update', $kadis), ['name' => 'Kadis D', 'position' => 'Ketua DPRD', 'group' => 'dprd', 'order' => 1])
            ->assertRedirect(route('admin.officials.index'));
        $this->assertSame('dprd', $kadis->fresh()->group);
        $this->actingAs($admin)->put(route('admin.officials.update', $kadis), ['name' => 'Kadis D', 'position' => 'X', 'group' => 'hacker'])->assertSessionHasErrors('group');
        $this->actingAs($admin)->get(route('admin.officials.index'))->assertOk()->assertSee('DPRD Kabupaten')->assertSeeInOrder(['Kapolres Empat Lawang', 'Ketua DPRD', 'Camat Tebing Tinggi']);
    }

    public function test_more_officials_seeded_published_only_when_two_sources_and_reseed_keeps_admin_edits(): void
    {
        Storage::fake('public');
        $this->seed(\Database\Seeders\OfficialSeeder::class);
        $total = Official::count();
        $this->assertGreaterThan(30, $total);
        $this->assertSame('dprd', Official::where('position', 'Ketua DPRD Kabupaten Empat Lawang')->value('group'));
        // one source / conflict / successor not yet in office: hidden until an officer checks
        $this->assertFalse((bool) Official::where('name', 'AKBP Daniel Ganjar Kristanto')->value('is_published'));
        $this->assertFalse((bool) Official::where('position', 'Camat Muara Pinang')->value('is_published'));
        $this->assertTrue((bool) Official::where('position', 'Ketua DPRD Kabupaten Empat Lawang')->value('is_published'));

        Official::where('position', 'Ketua DPRD Kabupaten Empat Lawang')->update(['name' => 'Diedit Admin']);
        $this->seed(\Database\Seeders\OfficialSeeder::class);
        $this->assertSame($total, Official::count());
        $this->assertSame('Diedit Admin', Official::where('position', 'Ketua DPRD Kabupaten Empat Lawang')->value('name'));
    }
}
