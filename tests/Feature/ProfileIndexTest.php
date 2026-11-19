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

    public function test_structure_page_shows_html_chart_without_image_and_zoom_with_image(): void
    {
        $profile = Profile::create(['slug' => 'struktur-organisasi', 'type' => 'struktur',
            'title' => 'Struktur Organisasi', 'content' => '<p>Isi</p>']);

        $this->get('/profil/struktur-organisasi')->assertOk()
            ->assertSee('class="structure-chart"', false)
            ->assertSeeInOrder(['Pembina', 'Atasan PPID', 'PPID Utama', 'PPID Pelaksana'])
            ->assertDontSee('structureModal', false);

        $profile->update(['image' => 'profiles/struktur.png']);

        $this->get('/profil/struktur-organisasi')->assertOk()
            ->assertSee('id="structureModal"', false)
            ->assertSee('profiles/struktur.png', false)
            ->assertSee('Buka file asli')
            ->assertDontSee('class="structure-chart"', false);
    }

    public function test_structure_table_renders_as_hierarchy_list(): void
    {
        $this->seed(\Database\Seeders\ProfileSeeder::class);

        $this->get('/profil/struktur-organisasi')->assertOk()
            ->assertSee('class="su-list', false)
            ->assertSeeInOrder(['su-role">Pembina', 'Bupati Empat Lawang', 'su-role">Atasan PPID', 'Bertanggung jawab kepada: Pembina'], false)
            ->assertDontSee('<table', false)
            ->assertDontSee('class="structure-chart"', false); // table replaces the fixed fallback chart

        $this->assertSame(['intro' => '<p>i</p>', 'head' => ['A', 'B'], 'rows' => [['x &', ''], ['y', 'z']], 'after' => ''],
            table_rows('<p>i</p><table><tr><th>A</th><th>B</th></tr><tr><td><b>x</b> &amp;</td><td></td></tr><tr><td>y</td><td>z</td></tr></table>'));
        $this->assertNull(table_rows('<p>tanpa tabel</p>'));
    }
}
