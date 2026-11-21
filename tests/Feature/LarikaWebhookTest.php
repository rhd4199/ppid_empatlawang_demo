<?php

namespace Tests\Feature;

use App\Models\WaMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LarikaWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'whk_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.larika.webhook_key' => self::KEY, 'services.larika.key' => 'lk_test', 'services.openai.key' => null]);
    }

    private function event(string $id = 'ev1'): array
    {
        return ['id' => $id, 'channel' => 'wa', 'event' => 'message.incoming', 'instanceId' => 'inst1', 'ts' => 1790409600000, 'data' => [
            'jid' => '6281234567890@s.whatsapp.net', 'isGroup' => false, 'fromMe' => false, 'phone' => '6281234567890',
            'message' => 'Hello', 'waMessageId' => "wa-$id", 'timestamp' => 1790409600000, 'pushName' => 'Budi', 'mediaType' => null,
        ]];
    }

    private function deliver(array $event, ?string $key = self::KEY)
    {
        return $this->postJson(route('api.whatsapp.larika'), $event, array_filter(['x-larika-webhook-key' => $key, 'x-larika-event' => $event['event']]));
    }

    public function test_wrong_missing_or_unset_key_is_401(): void
    {
        $this->deliver($this->event(), 'salah')->assertUnauthorized();
        $this->deliver($this->event(), null)->assertUnauthorized();
        config(['services.larika.webhook_key' => null]);
        $this->deliver($this->event(), '')->assertUnauthorized();
        $this->assertSame(0, WaMessage::count());
    }

    public function test_valid_event_stored_once(): void
    {
        $this->deliver($this->event())->assertOk()->assertJson(['ok' => true]);
        $this->deliver($this->event())->assertOk(); // at-least-once delivery

        $m = WaMessage::sole();
        $this->assertSame(['Hello', '6281234567890', 'Budi'], [$m->body, $m->conversation->phone, $m->conversation->name]);
    }

    public function test_same_event_from_poll_and_webhook_stored_once(): void
    {
        Http::fake(['gateway.larika.id/*' => Http::response(['events' => [$this->event('ev7')], 'next' => 'ev7'])]);
        $this->artisan('whatsapp:larika-poll', ['--once' => true])->assertSuccessful();

        $this->deliver($this->event('ev7'))->assertOk();
        $this->assertSame(1, WaMessage::count());
    }
}
