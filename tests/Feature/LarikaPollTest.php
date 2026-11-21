<?php

namespace Tests\Feature;

use App\Console\Commands\LarikaPoll;
use App\Models\PpidSetting;
use App\Models\WaMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LarikaPollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.larika.key' => 'lk_test', 'services.larika.url' => 'https://gateway.larika.id', 'services.openai.key' => null]);
    }

    private function event(string $id, string $text): array
    {
        return ['id' => $id, 'channel' => 'wa', 'event' => 'message.incoming', 'ts' => 1790409600000, 'data' => [
            'isGroup' => false, 'fromMe' => false, 'phone' => '6281234567890', 'message' => $text,
            'waMessageId' => "wa-$id", 'timestamp' => 1790409600000, 'pushName' => 'Budi',
        ]];
    }

    private function cursor(): ?string
    {
        return PpidSetting::where('key', LarikaPoll::CURSOR_KEY)->value('value');
    }

    public function test_once_stores_events_and_cursor_is_used_as_after(): void
    {
        Http::fakeSequence('gateway.larika.id/*')
            ->push(['events' => [$this->event('ev1', 'Halo'), $this->event('ev2', 'Tanya')], 'next' => 'ev2'])
            ->push(['events' => [], 'next' => 'ev2']);

        $this->artisan('whatsapp:larika-poll', ['--once' => true])->assertSuccessful();
        $this->assertSame(['Halo', 'Tanya'], WaMessage::orderBy('id')->pluck('body')->all());
        $this->assertSame('ev2', $this->cursor());

        $this->artisan('whatsapp:larika-poll', ['--once' => true])->assertSuccessful();

        $sent = Http::recorded()->map(fn ($pair) => $pair[0]);
        $this->assertSame('Bearer lk_test', $sent[0]->header('Authorization')[0]);
        $this->assertStringStartsWith('https://gateway.larika.id/v1/events?', $sent[0]->url());
        $this->assertEquals(25, $sent[0]->data()['wait'] ?? null);
        $this->assertArrayNotHasKey('after', $sent[0]->data()); // first run: no cursor
        $this->assertSame('ev2', $sent[1]->data()['after'] ?? null);
    }

    public function test_auth_error_stops_with_failure(): void
    {
        Http::fake(['gateway.larika.id/*' => Http::response(['error' => 'unauthorized'], 401)]);

        $this->artisan('whatsapp:larika-poll', ['--once' => true])
            ->expectsOutputToContain('poller berhenti')
            ->assertExitCode(LarikaPoll::AUTH_FAILED);
        $this->assertNull($this->cursor());
    }

    public function test_server_error_keeps_cursor(): void
    {
        PpidSetting::create(['key' => LarikaPoll::CURSOR_KEY, 'value' => 'ev5']);
        Http::fake(['gateway.larika.id/*' => Http::response('oops', 502)]);

        $this->artisan('whatsapp:larika-poll', ['--once' => true])->expectsOutputToContain('HTTP 502')->assertFailed();
        $this->assertSame('ev5', $this->cursor());
        Http::assertSent(fn (Request $r) => ($r->data()['after'] ?? null) === 'ev5');
    }

    public function test_missing_key(): void
    {
        config(['services.larika.key' => null]);
        Http::fake();

        $this->artisan('whatsapp:larika-poll', ['--once' => true])->expectsOutputToContain('LARIKA_KEY belum diisi')->assertFailed();
        Http::assertNothingSent();
    }
}
