<?php

namespace Tests\Feature;

use App\Models\WaConversation;
use App\Support\LarikaPresence;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LarikaPresenceTest extends TestCase
{
    use RefreshDatabase;

    private function conversation(): WaConversation
    {
        $c = WaConversation::create(['phone' => '6281234567890', 'status' => 'new']);
        $c->messages()->create(['direction' => 'in', 'body' => 'lama', 'status' => 'received', 'sent_at' => now()->subHour(), 'provider_message_id' => 'OLD']);
        $c->messages()->create(['direction' => 'out', 'body' => 'jawab', 'status' => 'sent', 'sent_at' => now()->subMinutes(50)]);
        $c->messages()->create(['direction' => 'in', 'body' => 'Halo', 'status' => 'received', 'sent_at' => now(), 'provider_message_id' => 'NEW1']);

        return $c;
    }

    public function test_unanswered_messages_marked_read_and_typing_shown(): void
    {
        config(['services.larika.key' => 'lk_test', 'services.larika.url' => 'https://gateway.larika.id']);
        WhatsAppGateway::save(['provider' => 'larika', 'enabled' => true], null);
        Http::fake(['gateway.larika.id/*' => Http::response(['ok' => true])]);

        LarikaPresence::read($this->conversation(), typing: true);

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/v1/read') && $r['messageId'] === 'NEW1' && $r->hasHeader('Authorization', 'Bearer lk_test'));
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/v1/presence') && $r['state'] === 'composing' && $r['chat'] === '6281234567890');
    }

    public function test_other_provider_or_failure_never_breaks(): void
    {
        config(['services.larika.key' => 'lk_test']);
        WhatsAppGateway::save(['provider' => 'fonnte', 'enabled' => true], 'tok');
        Http::fake();
        LarikaPresence::read($this->conversation(), typing: true);
        Http::assertNothingSent();

        WhatsAppGateway::save(['provider' => 'larika', 'enabled' => true], null);
        Http::fake(['*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('down')]);
        LarikaPresence::read(WaConversation::sole());
        $this->assertTrue(true); // no exception
    }
}
