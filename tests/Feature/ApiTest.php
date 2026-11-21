<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private array $auth = ['Authorization' => 'Bearer test-token'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.api.token' => 'test-token']);
        Storage::fake('public');
    }

    public function test_rejects_missing_wrong_or_unconfigured_token(): void
    {
        $this->getJson('/api/v1/news')->assertUnauthorized();
        $this->getJson('/api/v1/news', ['Authorization' => 'Bearer nope'])->assertUnauthorized();

        config(['services.api.token' => '']);
        $this->getJson('/api/v1/news', ['Authorization' => 'Bearer '])->assertUnauthorized();
    }

    public function test_document_crud_with_file_and_category_filter(): void
    {
        $id = $this->post('/api/v1/documents', [
            'title' => 'Laporan 2025',
            'category' => 'laporan_ppid',
            'file' => UploadedFile::fake()->create('lap.pdf', 10, 'application/pdf'),
        ], $this->auth)->assertCreated()->assertJsonPath('is_published', true)->json('id');

        $this->post('/api/v1/documents', ['title' => 'X', 'category' => 'standar_layanan_sop'], $this->auth)->assertCreated();

        $this->get('/api/v1/documents?category=laporan', $this->auth)
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id);

        $this->patch("/api/v1/documents/$id", ['title' => 'Baru'], $this->auth)
            ->assertOk()->assertJsonPath('title', 'Baru')->assertJsonPath('category', 'laporan_ppid');

        // multipart updates go through POST + _method (PHP ignores files on real PUT)
        $this->post("/api/v1/documents/$id", [
            '_method' => 'PATCH',
            'file' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
        ], $this->auth)->assertOk()->assertJsonPath('title', 'Baru');

        $this->post('/api/v1/documents', ['title' => 'Y', 'category' => 'ngawur'], $this->auth)
            ->assertUnprocessable()->assertJsonValidationErrors('category');

        $this->delete("/api/v1/documents/$id", [], $this->auth)->assertNoContent();
        $this->get("/api/v1/documents/$id", $this->auth)->assertNotFound();
    }

    public function test_only_one_headline_and_public_forms(): void
    {
        $a = News::create(['title' => 'A', 'slug' => 'a', 'content' => 'x', 'is_headline' => true]);
        $b = News::create(['title' => 'B', 'slug' => 'b', 'content' => 'x']);

        $this->post("/api/v1/news/{$b->id}/toggle-headline", [], $this->auth)->assertOk();
        $this->assertFalse((bool) $a->fresh()->is_headline);
        $this->assertTrue((bool) $b->fresh()->is_headline);

        $ticket = $this->post('/api/v1/complaints', [
            'name' => 'Budi', 'email' => 'b@x.id', 'phone' => '08', 'reason_complaint' => 'lama',
        ], $this->auth)->assertCreated()->json('ticket_number');
        $this->assertStringStartsWith('ADU-', $ticket);

        $this->get("/api/v1/complaints?ticket_number=$ticket", $this->auth)->assertJsonCount(1, 'data');
    }

    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    }

    private function webp(): string
    {
        $img = imagecreatetruecolor(4, 4);
        ob_start();
        imagewebp($img);

        return ob_get_clean();
    }

    public function test_news_cover_from_image_url_is_downloaded_and_shown(): void
    {
        // IP literal: the SSRF check needs no DNS in tests
        Http::fake([
            '93.184.215.14/foto.png*' => Http::response($this->png(), 200, ['Content-Type' => 'image/png']),
            '93.184.215.14/foto.webp' => Http::response($this->webp(), 200, ['Content-Type' => 'application/octet-stream']),
        ]);

        $news = $this->postJson('/api/v1/news', [
            'title' => 'Rapat PPID', 'content' => '<p>Isi</p>', 'image_url' => 'https://93.184.215.14/foto.png?v=1',
        ], $this->auth)->assertCreated()->json();

        $this->assertMatchesRegularExpression('#^news/\w{40}\.png$#', $news['image']);
        Storage::disk('public')->assertExists($news['image']);
        $this->get('/berita')->assertOk()->assertSee(storage_url($news['image']), false);

        // update via URL (type from the bytes: webp despite octet-stream) replaces and deletes the old cover
        $updated = $this->putJson("/api/v1/news/{$news['id']}", ['image_url' => 'https://93.184.215.14/foto.webp'], $this->auth)
            ->assertOk()->json('image');
        $this->assertStringEndsWith('.webp', $updated);
        Storage::disk('public')->assertExists($updated);
        Storage::disk('public')->assertMissing($news['image']);

        // multipart webp up to 5 MB is accepted too
        $this->post('/api/v1/news', [
            'title' => 'Webp', 'content' => 'x', 'image' => UploadedFile::fake()->image('a.webp')->size(4000),
        ], $this->auth)->assertCreated();
    }

    public function test_news_image_url_rejects_local_addresses_and_non_images(): void
    {
        Http::fake([
            '93.184.215.14/page' => Http::response('<html>bukan gambar</html>', 200, ['Content-Type' => 'image/png']),
            '93.184.215.14/big.png' => Http::response(str_repeat('a', 5 * 1024 * 1024 + 1)),
            '93.184.215.14/missing.png' => Http::response('', 404),
        ]);

        foreach (['http://127.0.0.1/a.png', 'http://localhost/a.png', 'http://169.254.169.254/latest/meta-data', 'http://10.0.0.5/a.png',
            'http://[::1]/a.png', 'http://user:pw@93.184.215.14/a.png', 'ftp://93.184.215.14/a.png', 'file:///etc/passwd',
            'https://93.184.215.14/page', 'https://93.184.215.14/big.png', 'https://93.184.215.14/missing.png'] as $url) {
            $this->postJson('/api/v1/news', ['title' => 'X', 'content' => 'x', 'image_url' => $url], $this->auth)
                ->assertUnprocessable()->assertJsonValidationErrors('image_url');
        }
        $this->assertSame(0, News::count());
        Http::assertSentCount(3); // only the three public URLs were fetched
    }

    public function test_news_post_with_same_source_url_updates_instead_of_duplicating(): void
    {
        $src = 'https://empatlawangkab.go.id/berita/rapat-ppid';
        $first = $this->postJson('/api/v1/news', ['title' => 'Rapat PPID', 'content' => 'v1', 'source_url' => $src], $this->auth)
            ->assertCreated()->assertJsonPath('source_url', $src)->json();

        $this->postJson('/api/v1/news', ['title' => 'Rapat PPID (revisi)', 'content' => 'v2', 'source_url' => $src], $this->auth)
            ->assertOk()->assertJsonPath('id', $first['id'])->assertJsonPath('content', 'v2')->assertJsonPath('title', 'Rapat PPID (revisi)');
        $this->assertSame(1, News::count());

        // without source_url every POST still creates; another source is another row
        $this->postJson('/api/v1/news', ['title' => 'Lain', 'content' => 'x'], $this->auth)->assertCreated();
        $other = $this->postJson('/api/v1/news', ['title' => 'Lain', 'content' => 'x', 'source_url' => 'ID-42'], $this->auth)->assertCreated()->json('id');
        $this->assertSame(3, News::count());

        // a PUT cannot steal another row's source
        $this->putJson("/api/v1/news/$other", ['source_url' => $src], $this->auth)->assertUnprocessable()->assertJsonValidationErrors('source_url');
    }

    public function test_gallery_post_with_same_source_url_updates_instead_of_duplicating(): void
    {
        $src = 'https://empatlawangkab.go.id/galeri/hut-ri';
        $first = $this->post('/api/v1/galleries', [
            'title' => 'HUT RI', 'source_url' => $src, 'cover_image' => UploadedFile::fake()->image('a.jpg'),
        ], $this->auth)->assertCreated()->json();

        // repeat without a cover: allowed on the upsert path, old cover kept
        $this->post('/api/v1/galleries', ['title' => 'HUT RI ke-81', 'source_url' => $src], $this->auth)
            ->assertOk()->assertJsonPath('id', $first['id'])->assertJsonPath('title', 'HUT RI ke-81')
            ->assertJsonPath('cover_image', $first['cover_image']);
        $this->assertSame(1, \App\Models\Gallery::count());
    }

    public function test_gallery_with_cover_and_photos_from_urls_in_one_call(): void
    {
        Http::fake([
            '93.184.215.14/cover.png' => Http::response($this->png()),
            '93.184.215.14/p1.png' => Http::response($this->png()),
            '93.184.215.14/p2.webp' => Http::response($this->webp()),
            '93.184.215.14/p3.png' => Http::response($this->png()),
        ]);
        $src = 'https://empatlawangkab.go.id/galeri/upacara';

        $album = $this->postJson('/api/v1/galleries', [
            'title' => 'Upacara HUT RI', 'source_url' => $src,
            'cover_image_url' => 'https://93.184.215.14/cover.png',
            'photos' => [
                ['url' => 'https://93.184.215.14/p1.png', 'caption' => 'Pembukaan'],
                ['url' => 'https://93.184.215.14/p2.webp'],
            ],
        ], $this->auth)->assertCreated()
            ->assertJsonCount(2, 'items')
            ->assertJsonPath('items.0.caption', 'Pembukaan')
            ->assertJsonPath('items.1.order', 2)
            ->json();

        Storage::disk('public')->assertExists([$album['cover_image'], $album['items'][0]['image_path'], $album['items'][1]['image_path']]);
        $this->assertStringStartsWith('galleries/covers/', $album['cover_image']);
        $this->assertStringEndsWith('.webp', $album['items'][1]['image_path']);

        $this->get('/galeri')->assertOk()->assertSee('Upacara HUT RI')->assertSee(storage_url($album['cover_image']), false);
        $this->get('/galeri/' . $album['slug'])->assertOk()
            ->assertSee(storage_url($album['items'][0]['image_path']), false)
            ->assertSee(storage_url($album['items'][1]['image_path']), false);

        // cron re-sends the album with one extra photo → same album, only the new photo is downloaded
        $this->postJson('/api/v1/galleries', [
            'title' => 'Upacara HUT RI', 'source_url' => $src,
            'photos' => [['url' => 'https://93.184.215.14/p1.png'], ['url' => 'https://93.184.215.14/p3.png', 'caption' => 'Penutupan']],
        ], $this->auth)->assertOk()->assertJsonPath('id', $album['id'])->assertJsonCount(3, 'items');
        Http::assertSentCount(4);

        // JSON photos on the photos endpoint too
        Http::fake(['93.184.215.14/p4.png' => Http::response($this->png())]);
        $this->postJson("/api/v1/galleries/{$album['id']}/photos", ['photos' => [['url' => 'https://93.184.215.14/p4.png']]], $this->auth)
            ->assertCreated()->assertJsonPath('0.order', 4);
    }

    public function test_gallery_download_failure_rolls_back_album_and_files(): void
    {
        Http::fake([
            '93.184.215.14/cover.png' => Http::response($this->png()),
            '93.184.215.14/p1.png' => Http::response($this->png()),
            '93.184.215.14/broken.png' => Http::response('nope', 404),
        ]);

        $this->postJson('/api/v1/galleries', [
            'title' => 'Gagal',
            'cover_image_url' => 'https://93.184.215.14/cover.png',
            'photos' => [['url' => 'https://93.184.215.14/p1.png'], ['url' => 'https://93.184.215.14/broken.png']],
        ], $this->auth)->assertUnprocessable()->assertJsonValidationErrors('photos.1.url');

        $this->assertSame(0, \App\Models\Gallery::count());
        $this->assertSame(0, \App\Models\GalleryItem::count());
        $this->assertSame([], Storage::disk('public')->allFiles()); // cover + p1 deleted again

        // SSRF and missing cover are rejected before anything is fetched
        Http::fake();
        $this->postJson('/api/v1/galleries', ['title' => 'X', 'cover_image_url' => 'http://127.0.0.1/a.png'], $this->auth)
            ->assertUnprocessable()->assertJsonValidationErrors('cover_image_url');
        $this->postJson('/api/v1/galleries', ['title' => 'X'], $this->auth)
            ->assertUnprocessable()->assertJsonValidationErrors(['cover_image', 'cover_image_url']);
        Http::assertNothingSent();
    }

    public function test_web_route_names_untouched(): void
    {
        $this->assertSame(url('/berita'), route('news.index'));
    }
}
