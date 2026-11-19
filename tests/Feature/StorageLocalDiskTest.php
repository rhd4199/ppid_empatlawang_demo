<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageLocalDiskTest extends TestCase
{
    use RefreshDatabase;

    /** Without S3 the "public" disk writes to storage/app/public and links via /storage on the request host. */
    public function test_news_image_upload_uses_local_public_disk(): void
    {
        $this->assertSame('local', config('filesystems.disks.public.driver'));

        $response = $this->actingAs(User::factory()->create())->post(route('admin.news.store'), [
            'title' => 'Berita Uji Upload',
            'content' => '<p>Isi</p>',
            'image' => UploadedFile::fake()->image('foto.jpg', 200, 150),
        ]);
        $response->assertRedirect(route('admin.news.index'));

        $path = News::firstOrFail()->image;
        try {
            $this->assertStringStartsWith('news/', $path);
            $this->assertFileExists(storage_path('app/public/'.$path));
            $this->assertSame(url('/storage/'.$path), storage_url($path));
        } finally {
            Storage::disk('public')->delete($path);
        }
    }
}
