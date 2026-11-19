<?php

namespace Tests\Feature;

use App\Models\Profile;
use Database\Seeders\ProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TugasFungsiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_content_renders_one_numbered_item_per_list_item(): void
    {
        $this->seed(ProfileSeeder::class);
        $content = Profile::where('slug', 'tugas-fungsi')->value('content');

        $html = $this->get('/profil/tugas-fungsi')->assertOk()
            ->assertSeeInOrder(['Tugas PPID Utama', 'Wewenang PPID Utama', 'Tugas PPID Pelaksana', 'Wewenang PPID Pelaksana'])
            ->assertSee('Menyusun dan melaksanakan kebijakan layanan informasi publik</div>', false)
            ->getContent();

        $this->assertSame(substr_count($content, '<li>'), substr_count($html, 'class="tf-card'));
        $this->assertSame(4, substr_count($html, 'class="tf-section'));
    }

    public function test_free_form_content_is_shown_as_is(): void
    {
        Profile::create(['slug' => 'tugas-fungsi', 'type' => 'tugas_fungsi', 'title' => 'Tugas dan Fungsi',
            'content' => '<p>Tugas kami <strong>banyak</strong>.</p><ul><li>Satu</li></ul>']);

        $this->get('/profil/tugas-fungsi')->assertOk()
            ->assertDontSee('class="tf-card', false)
            ->assertSee('<p>Tugas kami <strong>banyak</strong>.</p><ul><li>Satu</li></ul>', false);
    }

    public function test_text_around_lists_is_kept(): void
    {
        Profile::create(['slug' => 'tugas-fungsi', 'type' => 'tugas_fungsi', 'title' => 'Tugas dan Fungsi',
            'content' => '<p>Intro</p><h3>Tugas</h3><p>Pengantar</p><ol><li>Menyusun laporan;</li><li>Memberikan jawaban; dan</li></ol><p>Catatan</p><h3>Lain</h3><p>Tanpa daftar</p>']);

        $html = $this->get('/profil/tugas-fungsi')->assertOk()
            ->assertSeeInOrder(['Intro', 'href="#tf-title-0"', 'Pengantar', 'Menyusun laporan</div>', 'Memberikan jawaban</div>', 'Catatan', 'Lain', 'Tanpa daftar'], false)
            ->assertDontSee('fa-file-lines', false) // no per-item icons
            ->getContent();

        $this->assertSame(2, substr_count($html, 'class="tf-card'));
    }
}
