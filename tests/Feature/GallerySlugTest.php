<?php

namespace Tests\Feature;

use App\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GallerySlugTest extends TestCase
{
    use RefreshDatabase;

    private function album(string $title): Gallery
    {
        return Gallery::create([
            'title' => $title,
            'type' => 'photo',
            'cover_image' => 'galleries/covers/sampul.jpg',
            'is_published' => true,
        ]);
    }

    public function test_show_uses_slug_and_shares_cover_image(): void
    {
        $a = $this->album('Rapat Teknis SDI');
        $b = $this->album('Rapat Teknis SDI');

        $this->assertSame('rapat-teknis-sdi', $a->slug);
        $this->assertSame('rapat-teknis-sdi-2', $b->slug);

        $this->get('/galeri/rapat-teknis-sdi')
            ->assertOk()
            ->assertSee('og:image', false)
            ->assertSee('galleries/covers/sampul.jpg', false);

        $this->get('/galeri/' . $a->id)->assertRedirect('/galeri/rapat-teknis-sdi');

        $a->update(['title' => 'Judul Baru']);
        $this->assertSame('rapat-teknis-sdi', $a->fresh()->slug);
    }
}
