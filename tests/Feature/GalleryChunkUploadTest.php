<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryChunkUploadTest extends TestCase
{
    use RefreshDatabase;

    /** A photo split across chunks is reassembled byte-for-byte and stored once. */
    public function test_chunks_are_reassembled_into_one_gallery_item(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $gallery = Gallery::create([
            'title' => 'Album Uji',
            'type' => 'photo',
            'cover_image' => 'galleries/covers/x.jpg',
            'is_published' => true,
        ]);

        $photo = UploadedFile::fake()->image('foto.jpg', 400, 300);
        $bytes = file_get_contents($photo->getRealPath());
        $parts = str_split($bytes, (int) ceil(strlen($bytes) / 3));
        $uploadId = 'test-upload-1234';

        foreach ($parts as $index => $part) {
            $chunk = tempnam(sys_get_temp_dir(), 'chunk');
            file_put_contents($chunk, $part);

            $response = $this->actingAs($user)->post(
                route('admin.galleries.upload-chunk', $gallery->id),
                [
                    'upload_id' => $uploadId,
                    'index' => $index,
                    'total' => count($parts),
                    'chunk' => new UploadedFile($chunk, 'part', 'application/octet-stream', null, true),
                ]
            );

            $response->assertOk();
        }

        $this->assertSame(1, GalleryItem::where('gallery_id', $gallery->id)->count());

        $item = GalleryItem::where('gallery_id', $gallery->id)->first();
        Storage::disk('public')->assertExists($item->image_path);
        $this->assertSame($bytes, Storage::disk('public')->get($item->image_path));
        $this->assertFileDoesNotExist(storage_path('app/chunks/'.$uploadId));
    }

    /** Non-image payloads are rejected after assembly, not stored. */
    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $gallery = Gallery::create([
            'title' => 'Album Uji',
            'type' => 'photo',
            'cover_image' => 'galleries/covers/x.jpg',
            'is_published' => true,
        ]);

        $chunk = tempnam(sys_get_temp_dir(), 'chunk');
        file_put_contents($chunk, 'bukan gambar');

        $this->actingAs($user)->post(
            route('admin.galleries.upload-chunk', $gallery->id),
            [
                'upload_id' => 'test-upload-5678',
                'index' => 0,
                'total' => 1,
                'chunk' => new UploadedFile($chunk, 'part', 'application/octet-stream', null, true),
            ]
        )->assertStatus(422);

        $this->assertSame(0, GalleryItem::where('gallery_id', $gallery->id)->count());
    }

    public function test_bulk_delete_removes_rows_then_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('galleries/covers/c.jpg', 'x');
        Storage::disk('public')->put('galleries/items/p.jpg', 'x');

        $gallery = Gallery::create(['title' => 'Album Hapus', 'type' => 'photo', 'cover_image' => 'galleries/covers/c.jpg']);
        $gallery->items()->create(['image_path' => 'galleries/items/p.jpg']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.galleries.bulk-action'), ['action' => 'delete', 'ids' => [$gallery->id]])
            ->assertRedirect();

        $this->assertSame(0, Gallery::count());
        $this->assertSame(0, GalleryItem::count());
        Storage::disk('public')->assertMissing(['galleries/covers/c.jpg', 'galleries/items/p.jpg']);
    }
}
