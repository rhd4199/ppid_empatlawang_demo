<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminGalleryVideoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    public function test_admin_creates_and_edits_a_video_album(): void
    {
        $this->get(route('admin.galleries.create'))->assertOk()->assertSee('name="type"', false)->assertSee('Link Video YouTube');

        $this->post(route('admin.galleries.store'), [
            'title' => 'Video Pelantikan', 'type' => 'video', 'url' => 'https://youtu.be/dQw4w9WgXcQ', 'is_published' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $video = Gallery::sole();
        $this->assertSame('video', $video->type);
        $this->assertNull($video->cover_image);

        $this->get(route('admin.galleries.index'))->assertOk()->assertSee('Video YouTube')->assertSee('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', false);
        $this->get(route('admin.galleries.edit', $video->id))->assertOk()->assertSee('value="https://youtu.be/dQw4w9WgXcQ"', false)
            ->assertSee('thumbnail YouTube otomatis');

        $this->get('/galeri')->assertOk()->assertSee('Video Pelantikan')->assertSee('video-play-icon', false);
        $this->get('/galeri/' . $video->slug)->assertOk()->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false);

        // new link → embed follows
        $this->put(route('admin.galleries.update', $video->id), [
            'title' => 'Video Pelantikan', 'type' => 'video', 'url' => 'https://www.youtube.com/watch?v=abcdefghijk', 'is_published' => 1,
        ])->assertSessionHasNoErrors();
        $this->get('/galeri/' . $video->slug)->assertOk()
            ->assertSee('youtube-nocookie.com/embed/abcdefghijk', false)->assertDontSee('dQw4w9WgXcQ', false);

        // back to photo → link dropped, no embed
        $this->put(route('admin.galleries.update', $video->id), ['title' => 'Video Pelantikan', 'type' => 'photo', 'url' => 'https://youtu.be/dQw4w9WgXcQ']);
        $this->assertNull($video->fresh()->url);
        $this->get('/galeri/' . $video->slug)->assertDontSee('youtube-nocookie', false);
    }

    public function test_validation_for_video_and_photo_albums(): void
    {
        // video needs a YouTube video link
        foreach (['', 'https://vimeo.com/1', 'https://www.youtube.com/channel/UCx'] as $url) {
            $this->post(route('admin.galleries.store'), ['title' => 'X', 'type' => 'video', 'url' => $url])->assertSessionHasErrors('url');
        }
        // photo album still needs a cover; old forms without "type" stay photo albums
        $this->post(route('admin.galleries.store'), ['title' => 'Foto', 'type' => 'photo'])->assertSessionHasErrors('cover_image');
        $this->post(route('admin.galleries.store'), ['title' => 'Foto', 'cover_image' => UploadedFile::fake()->image('a.jpg')])->assertSessionHasNoErrors();
        $this->assertSame('photo', Gallery::sole()->type);
    }
}
