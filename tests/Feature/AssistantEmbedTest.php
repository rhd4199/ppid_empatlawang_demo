<?php

namespace Tests\Feature;

use App\Models\PpidSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantEmbedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.key' => null]); // a real key in .env must never reach tests
    }

    public function test_embed_page_is_the_open_panel_and_only_listed_sites_may_frame_it(): void
    {
        config(['services.assistant.embed_origins' => "'self' https://empatlawangkab.go.id"]);

        $this->get('/sapa-madani/embed')
            ->assertOk()
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self' https://empatlawangkab.go.id")
            ->assertSee('id="smPanel"', false)
            ->assertSee('const EMBED = true', false);
    }

    public function test_embed_script_points_at_the_embed_page(): void
    {
        $this->get('/sapa-madani/embed.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertSee(json_encode(route('assistant.embed')), false)
            ->assertSee('window.SapaMadani', false);
    }

    public function test_chat_answers_without_csrf_token_for_the_cross_site_frame(): void
    {
        $this->withMiddleware()
            ->postJson('/sapa-madani/chat', ['message' => 'Jam layanan PPID'])
            ->assertOk()
            ->assertJsonStructure(['reply']);
    }

    public function test_disabled_assistant_has_no_embed(): void
    {
        PpidSetting::updateOrCreate(['key' => 'assistant_enabled'], ['value' => '0']);

        $this->get('/sapa-madani/embed')->assertNotFound();
        $this->get('/sapa-madani/embed.js')->assertNotFound();
    }
}
