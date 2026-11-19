<?php

namespace Tests\Feature;

use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_cards_follow_fixed_order_and_link_officials(): void
    {
        // Inserted out of order on purpose.
        foreach ([['tugas-fungsi', 'tugas_fungsi', 'Tugas'], ['tentang-ppid', 'general', 'Tentang'],
            ['struktur-organisasi', 'struktur', 'Struktur'], ['visi-misi', 'visi_misi', 'Visi']] as [$slug, $type, $title]) {
            Profile::create(['slug' => $slug, 'type' => $type, 'title' => $title, 'content' => '<p>Isi</p>']);
        }

        $this->get('/profil')->assertOk()
            ->assertSee('Profil PPID')
            ->assertSeeInOrder(['>Tentang<', '>Visi<', '>Struktur<', '>Tugas<', 'Pejabat &amp; Pejabat Struktural'], false)
            ->assertSee(route('officials.index'), false);
    }
}
