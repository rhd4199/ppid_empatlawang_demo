<?php

namespace Tests\Feature;

use App\Console\Commands\LarikaWebhook;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LarikaWebhookRegisterTest extends TestCase
{
    private const SECRET = 'whsec_from_larika';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.larika.key' => 'lk_test', 'services.larika.url' => 'https://gateway.larika.id',
            'services.larika.webhook_key' => null, 'app.url' => 'https://ppid.example.go.id/']);
    }

    private function fakeLarika(array $test = ['ok' => true]): void
    {
        Http::fake([
            'gateway.larika.id/v1/webhooks/*/test' => Http::response($test),
            'gateway.larika.id/v1/webhooks' => Http::response(['id' => 'cmwhk0001', 'url' => 'x', 'secret' => self::SECRET], 201),
        ]);
    }

    public function test_registers_app_url_and_prints_secret(): void
    {
        $this->fakeLarika();

        $this->artisan('whatsapp:larika-webhook')
            ->expectsOutputToContain('https://ppid.example.go.id/api/v1/whatsapp/larika')
            ->expectsOutputToContain('LARIKA_WEBHOOK_KEY=' . self::SECRET)
            ->assertSuccessful();

        Http::assertSent(fn (Request $r) => $r->method() === 'POST' && $r->url() === 'https://gateway.larika.id/v1/webhooks'
            && $r->header('Authorization') === ['Bearer lk_test']
            && $r['url'] === 'https://ppid.example.go.id/api/v1/whatsapp/larika'
            && $r['events'] === LarikaWebhook::EVENTS);
        Http::assertSentCount(1); // no --test
    }

    public function test_matching_env_key_is_not_printed_again(): void
    {
        config(['services.larika.webhook_key' => self::SECRET]);
        $this->fakeLarika();

        $this->artisan('whatsapp:larika-webhook')
            ->expectsOutputToContain('sudah sama')
            ->doesntExpectOutputToContain('LARIKA_WEBHOOK_KEY=' . self::SECRET)
            ->assertSuccessful();
    }

    public function test_option_test_calls_test_endpoint(): void
    {
        $this->fakeLarika(['ok' => false, 'error' => 'HTTP 401']);

        $this->artisan('whatsapp:larika-webhook', ['--test' => true])
            ->expectsOutputToContain('belum sama dengan secret')
            ->assertFailed();
        Http::assertSent(fn (Request $r) => $r->url() === 'https://gateway.larika.id/v1/webhooks/cmwhk0001/test');
    }

    public function test_http_app_url_is_rejected(): void
    {
        config(['app.url' => 'http://localhost']);
        Http::fake();

        $this->artisan('whatsapp:larika-webhook')->expectsOutputToContain('hanya menerima webhook https://')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_missing_key(): void
    {
        config(['services.larika.key' => null]);
        Http::fake();
        $this->artisan('whatsapp:larika-webhook')->expectsOutputToContain('LARIKA_KEY belum diisi')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_rejection_is_reported(): void
    {
        Http::fake(['gateway.larika.id/*' => Http::response(['error' => 'too_many_webhooks'], 422)]);
        $this->artisan('whatsapp:larika-webhook')->expectsOutputToContain('too_many_webhooks')->assertFailed();
    }
}
