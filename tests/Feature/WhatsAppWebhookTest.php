<?php

namespace Tests\Feature;

use App\Models\PpidSetting;
use App\Models\WaConversation;
use App\Models\WaMessage;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function useProvider(string $provider): string
    {
        PpidSetting::updateOrCreate(['key' => 'wa_provider'], ['value' => $provider]);

        return route('api.whatsapp.webhook', ['secret' => WhatsAppGateway::webhookSecret()]);
    }

    public function test_wrong_or_missing_secret_is_forbidden(): void
    {
        $this->useProvider('fonnte');
        $payload = ['sender' => '6281234567890', 'message' => 'Halo'];

        $this->postJson(route('api.whatsapp.webhook', ['secret' => 'salah']), $payload)->assertForbidden();
        $this->postJson(route('api.whatsapp.webhook'), $payload)->assertForbidden();
        $this->assertSame(0, WaMessage::count());
    }

    public function test_fonnte_messages_grouped_per_number_and_duplicates_ignored(): void
    {
        $url = $this->useProvider('fonnte');
        $first = ['device' => '628972007121', 'sender' => '6281234567890', 'name' => 'Budi', 'message' => 'Mau tanya syarat KTP', 'inboxid' => 'in-1'];

        $this->post($url, $first)->assertOk()->assertJson(['stored' => 1]); // form-encoded like Fonnte
        $this->post($url, $first)->assertOk()->assertJson(['stored' => 0]); // provider retry
        $this->post($url, ['sender' => '6281234567890', 'message' => 'Terima kasih', 'inboxid' => 'in-2'])->assertJson(['stored' => 1]);
        $this->post($url, ['sender' => '120363@g.us', 'member' => '628111', 'message' => 'grup', 'inboxid' => 'in-3'])->assertJson(['stored' => 0]);

        $this->assertSame(1, WaConversation::count());
        $c = WaConversation::first();
        $this->assertSame(['6281234567890', 'Budi', 'new'], [$c->phone, $c->name, $c->status]);
        $this->assertNotNull($c->last_message_at);
        $this->assertSame(['Mau tanya syarat KTP', 'Terima kasih'], $c->messages()->orderBy('id')->pluck('body')->all());
        $this->assertSame(['in', 'received'], [$c->messages[0]->direction, $c->messages[0]->status]);
    }

    public function test_new_message_reopens_done_conversation(): void
    {
        $url = $this->useProvider('wablas');
        $this->postJson($url, ['id' => 'w1', 'phone' => '081234567890', 'pushName' => 'Sari', 'message' => 'Halo']);
        WaConversation::query()->update(['status' => 'done']);

        $this->postJson($url, ['id' => 'w2', 'phone' => '6281234567890', 'message' => 'Lagi', 'isFromMe' => false])->assertJson(['stored' => 1]);
        $this->postJson($url, ['id' => 'w3', 'phone' => '6281234567890', 'message' => 'balasan admin', 'isFromMe' => true])->assertJson(['stored' => 0]);

        $this->assertSame('new', WaConversation::sole()->status);
        $this->assertSame(2, WaMessage::count());
    }

    public function test_cloud_api_verify_and_payload(): void
    {
        $url = $this->useProvider('cloud');
        $secret = WhatsAppGateway::webhookSecret();

        $this->get(route('api.whatsapp.verify', ['hub.mode' => 'subscribe', 'hub.verify_token' => $secret, 'hub.challenge' => '12345']))
            ->assertOk()->assertSee('12345');
        $this->get(route('api.whatsapp.verify', ['hub.verify_token' => 'salah', 'hub.challenge' => '1']))->assertForbidden();

        $payload = ['object' => 'whatsapp_business_account', 'entry' => [['changes' => [['value' => [
            'contacts' => [['wa_id' => '6281234567890', 'profile' => ['name' => 'Andi']]],
            'messages' => [['from' => '6281234567890', 'id' => 'wamid.ABC', 'timestamp' => '1763700000', 'type' => 'text', 'text' => ['body' => 'Info PPID']]],
        ]]]]]];
        $this->postJson($url, $payload)->assertJson(['stored' => 1]);
        $this->postJson($url, $payload)->assertJson(['stored' => 0]);

        $m = WaMessage::sole();
        $this->assertSame(['Info PPID', 'wamid.ABC', 1763700000], [$m->body, $m->provider_message_id, $m->sent_at->timestamp]);
        $this->assertSame('Andi', $m->conversation->name);
    }

    public function test_waha_payload(): void
    {
        $url = $this->useProvider('waha');
        $this->postJson($url, ['event' => 'message', 'payload' => [
            'id' => 'false_6281234567890@c.us_X', 'from' => '6281234567890@c.us', 'body' => 'Halo', 'fromMe' => false,
            '_data' => ['notifyName' => 'Rina'],
        ]])->assertJson(['stored' => 1]);

        $this->assertSame(['6281234567890', 'Rina'], [WaConversation::sole()->phone, WaConversation::sole()->name]);
    }
}
