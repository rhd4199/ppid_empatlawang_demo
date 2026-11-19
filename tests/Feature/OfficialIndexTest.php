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
        $this->seed(\Database\Seeders\OfficialSeeder::class);
        $this->assertSame(5, Official::count()); // re-seed updates, no duplicates
        Official::create(['name' => 'Kadis A', 'position' => 'Kepala Dinas', 'order' => 10, 'is_published' => true,
            'bio' => str_repeat('Riwayat panjang. ', 20)]);
        Official::create(['name' => 'Hidden', 'position' => 'X', 'order' => 11, 'is_published' => false]);

        $this->get('/profil-pejabat')->assertOk()
            ->assertSeeInOrder(['H. Joncik Muhammad', 'A. Rifai', 'Fauzan Khoiri Denin', 'Pejabat Struktural', 'M. Yunidi Nuzli', 'Sumitro Sukma Bahagia', 'Kadis A'], false)
            ->assertSee('official-card-lead', false)
            ->assertSee('officials/bupati.jpg', false)
            ->assertSee('>YN</div>', false) // initials when no photo
            ->assertSee('data-bs-target="#official-', false)
            ->assertDontSee('Hidden')
            ->assertDontSee('Data pejabat belum tersedia.');
    }
}
