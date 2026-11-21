<?php

namespace Tests\Feature;

use App\Models\PpidSetting;
use App\Models\WaConversation;
use App\Models\WaMessage;
use App\Support\LarikaEvents;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LarikaEventsTest extends TestCase
{
    use RefreshDatabase;

    /** Body as in the Larika docs (Webhook example). */
    private function incoming(string $id, array $data = []): array
    {
        return ['id' => $id, 'channel' => 'wa', 'event' => 'message.incoming', 'instanceId' => 'inst1', 'ts' => 1790409600000,
            'data' => array_merge(['jid' => '6281234567890@s.whatsapp.net', 'isGroup' => false, 'fromMe' => false, 'phone' => '6281234567890',
                'message' => 'Hello', 'waMessageId' => "wa-$id", 'timestamp' => 1790409600000, 'pushName' => 'Budi', 'mediaType' => null], $data)];
    }

    private function outgoing(string $status = 'pending', string $phone = '6281234567890'): WaMessage
    {
        $c = WaConversation::create(['phone' => $phone, 'status' => 'done']);

        return $c->messages()->create(['direction' => 'out', 'body' => 'Balasan', 'status' => $status, 'sent_at' => now()]);
    }

    public function test_incoming_stored_once_for_same_event(): void
    {
        $this->assertCount(1, LarikaEvents::handle($this->incoming('ev1')));
        $this->assertSame([], LarikaEvents::handle($this->incoming('ev1'))); // poll + webhook, or a retry

        $m = WaMessage::sole();
        $this->assertSame(['Hello', 'wa-ev1', 1790409600], [$m->body, $m->provider_message_id, $m->sent_at->timestamp]); // ms → s
        $this->assertSame(['6281234567890', 'Budi', 'new'], [$m->conversation->phone, $m->conversation->name, $m->conversation->status]);
        $this->assertSame(1, DB::table('larika_events')->count());
    }

    public function test_from_me_and_group_ignored_but_recorded(): void
    {
        LarikaEvents::handle($this->incoming('ev1', ['fromMe' => true]));
        LarikaEvents::handle($this->incoming('ev2', ['isGroup' => true, 'jid' => '1203@g.us']));
        LarikaEvents::handle(['id' => 'ev3', 'event' => 'instance.status', 'data' => ['status' => 'ONLINE']]);

        $this->assertSame(0, WaMessage::count());
        $this->assertSame(3, DB::table('larika_events')->count());
    }

    public function test_status_updates_row_with_matching_ref(): void
    {
        $msg = $this->outgoing('sent');
        $other = $this->outgoing('sent', '6281200000002');
        $ref = 'wa-out-' . $msg->id;

        LarikaEvents::handle(['id' => 's1', 'event' => 'message.status', 'data' => ['ref' => $ref, 'status' => 'READ']]);
        $this->assertSame('read', $msg->fresh()->status);
        $this->assertSame('sent', $other->fresh()->status);

        // Late DELIVERED does not move READ back.
        LarikaEvents::handle(['id' => 's2', 'event' => 'message.status', 'data' => ['ref' => $ref, 'status' => 'DELIVERED']]);
        $this->assertSame('read', $msg->fresh()->status);

        // Unknown / foreign refs are ignored.
        LarikaEvents::handle(['id' => 's3', 'event' => 'message.status', 'data' => ['ref' => 'admin-test-x', 'status' => 'READ']]);
    }

    public function test_failed_marks_row_and_reopens_conversation(): void
    {
        $msg = $this->outgoing('sent');

        LarikaEvents::handle(['id' => 'f1', 'event' => 'message.failed', 'data' => ['ref' => 'wa-out-' . $msg->id, 'status' => 'FAILED', 'error' => 'not_on_whatsapp']]);

        $msg->refresh();
        $this->assertSame(['failed', 'Larika: not_on_whatsapp'], [$msg->status, $msg->error]);
        $this->assertSame(['new', 'Balasan gagal terkirim'], [$msg->conversation->status, $msg->conversation->handoff_reason]);
    }

    public function test_webhook_with_larika_provider_uses_shared_handler(): void
    {
        PpidSetting::updateOrCreate(['key' => 'wa_provider'], ['value' => 'larika']);
        $url = route('api.whatsapp.webhook', ['secret' => WhatsAppGateway::webhookSecret()]);

        $this->postJson($url, $this->incoming('ev9'))->assertOk()->assertJson(['stored' => 1]);
        $this->postJson($url, $this->incoming('ev9'))->assertOk()->assertJson(['stored' => 0]);
        $this->assertSame(1, WaMessage::count());
    }
}
