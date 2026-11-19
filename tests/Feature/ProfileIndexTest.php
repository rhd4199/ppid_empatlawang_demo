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

    public function test_profile_detail_has_side_menu_and_prev_next(): void
    {
        foreach ([['tugas-fungsi', 'tugas_fungsi', 'Tugas'], ['tentang-ppid', 'general', 'Tentang'],
            ['visi-misi', 'visi_misi', 'Visi']] as [$slug, $type, $title]) {
            Profile::create(['slug' => $slug, 'type' => $type, 'title' => $title, 'content' => '<p>Isi</p>']);
        }

        $this->get('/profil/visi-misi')->assertOk()
            ->assertSee('href="'.route('profiles.index').'"', false)
            ->assertSeeInOrder(['Menu profil', 'Tentang', 'aria-current="page"', 'Visi', 'Tugas', 'Profil Pejabat'], false)
            ->assertSee('href="'.route('profiles.show', 'tentang-ppid').'" rel="prev"', false)
            ->assertSee('href="'.route('profiles.show', 'tugas-fungsi').'" rel="next"', false);

        // Last profile subpage links on to Pejabat; first has no prev.
        $this->get('/profil/tugas-fungsi')->assertSee('href="'.route('officials.index').'" rel="next"', false);
        $this->get('/profil/tentang-ppid')->assertDontSee('rel="prev"', false);
    }
}
