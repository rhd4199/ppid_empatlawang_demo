<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_web_route_names_untouched(): void
    {
        $this->assertSame(url('/berita'), route('news.index'));
    }
}
