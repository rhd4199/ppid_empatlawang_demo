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

    public function test_web_route_names_untouched(): void
    {
        $this->assertSame(url('/berita'), route('news.index'));
    }
}
