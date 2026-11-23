<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_key_fails_without_calling_openai(): void
    {
        config(['services.openai.key' => null]);
        Http::fake();

        $this->artisan('assistant:check')->expectsOutputToContain('KOSONG')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_openai_error_is_shown_but_key_is_not(): void
    {
        config(['services.openai.key' => 'sk-secret-123456']);
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['code' => 'insufficient_quota', 'message' => 'You exceeded your quota']], 429)]);

        $this->artisan('assistant:check')->expectsOutputToContain('HTTP 429): insufficient_quota')
            ->doesntExpectOutputToContain('secret-123456')->assertFailed();
    }

    public function test_ok(): void
    {
        config(['services.openai.key' => 'sk-secret-123456']);
        Http::fake(['api.openai.com/*' => Http::response(['model' => 'gpt-4.1-mini', 'choices' => [['message' => ['content' => 'ok']]]])]);

        $this->artisan('assistant:check')->expectsOutputToContain('OpenAI OK')->assertSuccessful();
    }

    public function test_admin_web_status_shows_what_the_web_process_sees_without_the_key(): void
    {
        config(['services.openai.key' => 'sk-secret-123456']);
        Http::fake(['api.openai.com/*' => Http::response(['model' => 'gpt-4.1-mini'])]);

        $this->get('/admin/sapa-madani/status')->assertRedirect();

        $res = $this->actingAs(\App\Models\User::factory()->create())->get('/admin/sapa-madani/status?test=1')->assertOk()
            ->assertJson(['ai_available' => true, 'openai_key_set' => true, 'openai_test' => ['ok' => true]]);
        $this->assertStringNotContainsString('secret-123456', $res->getContent());
    }
}
