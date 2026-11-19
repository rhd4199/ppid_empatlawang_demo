<?php

namespace Tests\Feature;

use App\Models\Official;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->seed(\Database\Seeders\OfficialSeeder::class);
        Official::create(['name' => 'Kadis A', 'position' => 'Kepala Dinas', 'order' => 10, 'is_published' => true,
            'bio' => str_repeat('Riwayat panjang. ', 20)]);
        Official::create(['name' => 'Hidden', 'position' => 'X', 'order' => 11, 'is_published' => false]);

        $this->get('/profil-pejabat')->assertOk()
            ->assertSeeInOrder(['Joncik Muhammad', "A. Rifa&#039;i", 'Fauzan Khoiri Denin', 'Pejabat Struktural', 'Kadis A'], false)
            ->assertSee('official-card-lead', false)
            ->assertSee('data-bs-target="#official-', false)
            ->assertDontSee('Hidden')
            ->assertDontSee('Data pejabat belum tersedia.');
    }
}
