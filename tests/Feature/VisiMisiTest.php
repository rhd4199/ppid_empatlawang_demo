<?php

namespace Tests\Feature;

use App\Models\Profile;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisiMisiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_visi_misi_renders_quote_and_numbered_cards(): void
    {
        $this->seed(ProfileSeeder::class);

        $this->get('/profil/visi-misi')->assertOk()
            ->assertSee('class="vm-quote', false)
            ->assertSee('Empat Lawang MADANI')
            ->assertSeeInOrder(['vm-num', '1', 'Meningkatkan kualitas SDM.', 'vm-num', '2', 'tata kelola pemerintahan'], false);
    }

    public function test_free_form_content_is_shown_as_is(): void
    {
        Profile::create(['slug' => 'visi-misi', 'type' => 'visi_misi', 'title' => 'Visi dan Misi',
            'content' => '<p>Visi kami: <strong>terbuka</strong>.</p><ul><li>Satu</li></ul>']);

        $this->get('/profil/visi-misi')->assertOk()
            ->assertDontSee('class="vm-quote', false)
            ->assertSee('<p>Visi kami: <strong>terbuka</strong>.</p><ul><li>Satu</li></ul>', false);
    }

    public function test_parser(): void
    {
        $this->assertSame(
            ['visi' => '<p>A</p>', 'misi' => ['<p>x</p>', 'y'], 'rest' => '<p>Catatan</p>'],
            visi_misi('<H2 class="t"><strong>VISI</strong></H2><p>A</p><h3>Misi</h3> <ol><li><p>x</p></li><li class="z">y</li></ol><p>Catatan</p>')
        );
        $this->assertNull(visi_misi('<h3>Visi</h3><p>A</p><h3>Misi</h3><p>tanpa daftar</p>'));
        $this->assertNull(visi_misi('<p>Intro</p><h3>Visi</h3><p>A</p><h3>Misi</h3><ol><li>x</li></ol>'));
        $this->assertNull(visi_misi(null));
    }
}
