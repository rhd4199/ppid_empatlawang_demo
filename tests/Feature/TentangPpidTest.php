<?php

namespace Tests\Feature;

use App\Models\Profile;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TentangPpidTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_content_renders_icon_cards(): void
    {
        $this->seed(ProfileSeeder::class);

        $this->get('/profil/tentang-ppid')->assertOk()
            ->assertSee('class="ab-lead', false)
            ->assertSeeInOrder(['fa-scale-balanced', 'Dasar Hukum', 'ab-card', 'fa-gavel', 'UU No. 14 Tahun 2008'], false)
            ->assertSeeInOrder(['fa-handshake', 'Maklumat Pelayanan', 'fa-star', 'Pelayanan prima'], false)
            ->assertSeeInOrder(['<details class="ab-more', 'Latar Belakang', 'Selengkapnya', 'hak asasi manusia'], false)
            ->assertSee('Profil PPID'); // side menu unchanged
    }

    public function test_content_without_headings_is_shown_as_is(): void
    {
        Profile::create(['slug' => 'tentang-ppid', 'type' => 'general', 'title' => 'Tentang PPID',
            'content' => '<p>Teks <strong>bebas</strong>.</p>']);

        $this->get('/profil/tentang-ppid')->assertOk()
            ->assertDontSee('ab-card', false)
            ->assertSee('<p>Teks <strong>bebas</strong>.</p>', false);
    }

    public function test_parser(): void
    {
        $this->assertSame([
            'intro' => '<p>I</p>',
            'sections' => [
                ['title' => 'A & B', 'body' => '<p>x</p>', 'items' => ['1', '<p>2</p>'], 'after' => '<p>y</p>'],
                ['title' => 'C', 'body' => '<p>z</p>', 'items' => [], 'after' => ''],
            ],
        ], content_sections('<p>I</p><h3 class="t"><b>A &amp; B</b></h3><p>x</p><ol><li>1</li><li><p>2</p></li></ol><p>y</p><H2>C</H2><p>z</p>'));
        $this->assertNull(content_sections('<p>tanpa judul</p>'));
        $this->assertNull(content_sections(null));
    }
}
