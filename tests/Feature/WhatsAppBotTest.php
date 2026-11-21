<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WaConversation;
use App\Support\WhatsAppBot;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppBotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.key' => null]);
    }

    private function configure(bool $autoReply = true, array $providerResponse = ['status' => true]): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response($providerResponse)]);
        WhatsAppGateway::save(['provider' => 'fonnte', 'enabled' => true, 'auto_reply' => $autoReply], 'tok-123');
    }

    private function incoming(string $text, string $id): WaConversation
    {
        $url = route('api.whatsapp.webhook', ['secret' => WhatsAppGateway::webhookSecret()]);
        $this->post($url, ['sender' => '081234567890', 'name' => 'Budi', 'message' => $text, 'inboxid' => $id])->assertOk();

        return WaConversation::where('phone', '6281234567890')->sole();
    }

    public function test_general_question_answered_automatically_and_not_queued(): void
    {
        $this->configure();
        $c = $this->incoming('jam buka kantor?', 'm1');

        Http::assertSent(fn (Request $r) => $r['target'] === '6281234567890' && str_contains($r['message'], 'jam'));
        $this->assertSame('done', $c->status);
        $bot = $c->messages()->where('direction', 'out')->sole();
        $this->assertSame(['bot', 'sent'], [$bot->sender, $bot->status]);

        $this->actingAs(User::factory()->create())->get(route('admin.wa-inbox.index'))
            ->assertOk()->assertDontSee('>Baru</span>', false);
        $this->get(route('admin.wa-inbox.show', $c))->assertSee('Bot Sapa Madani (otomatis)');
    }

    public function test_document_request_goes_to_officer_inbox(): void
    {
        $this->configure();
        $c = $this->incoming('Selamat pagi, saya minta salinan dokumen kontrak jalan desa', 'm1');

        Http::assertNothingSent();
        $this->assertSame(['new', 'Permintaan dokumen / permohonan informasi'], [$c->status, $c->handoff_reason]);
        $this->assertSame(0, $c->messages()->where('direction', 'out')->count());

        $this->actingAs(User::factory()->create())->get(route('admin.wa-inbox.index'))
            ->assertSee('>Baru</span>', false)->assertSee('Butuh petugas: Permintaan dokumen');
    }

    public function test_bot_off_queues_everything(): void
    {
        $this->configure(autoReply: false);
        $c = $this->incoming('jam buka kantor?', 'm1');

        Http::assertNothingSent();
        $this->assertSame('new', $c->status);
        $this->assertNull($c->handoff_reason);
    }

    public function test_failed_auto_reply_stays_in_queue(): void
    {
        $this->configure(providerResponse: ['status' => false, 'reason' => 'device disconnected']);
        $c = $this->incoming('alamat kantor PPID di mana?', 'm1');

        $this->assertSame(['new', 'Balasan otomatis gagal terkirim'], [$c->status, $c->handoff_reason]);
        $this->assertSame('failed', $c->messages()->where('direction', 'out')->sole()->status);
    }

    public function test_routing_rules(): void
    {
        foreach (['jam buka kantor?', 'berapa lama permohonan diproses', 'biayanya berapa?', 'Assalamualaikum', 'terima kasih min', 'alamat kantor PPID', 'apakah ada biaya?'] as $text) {
            $this->assertNull(WhatsAppBot::handoffReason($text), $text);
        }
        $this->assertSame('Aduan / laporan warga', WhatsAppBot::handoffReason('saya mau lapor jalan rusak di desa'));
        $this->assertSame('Keberatan / sengketa informasi', WhatsAppBot::handoffReason('permohonan saya ditolak, mau keberatan'));
        $this->assertSame('Permintaan dokumen / permohonan informasi', WhatsAppBot::handoffReason('minta data APBD 2025'));
        $this->assertSame('Bot tidak yakin dengan maksud pertanyaan', WhatsAppBot::handoffReason('anak saya sakit tolong dibantu'));
    }
}
