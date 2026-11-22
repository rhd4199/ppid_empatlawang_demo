<?php

namespace Tests\Feature;

use App\Http\Controllers\AssistantController;
use App\Models\User;
use App\Models\WaConversation;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Keyword answers are off by default (owner): without AI nobody gets a template. */
class AssistantNoAiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.key' => null, 'services.assistant.keyword_fallback' => false]);
    }

    public function test_web_chat_says_ai_is_off_instead_of_a_template(): void
    {
        $this->assertFalse(AssistantController::aiAvailable());
        $this->postJson('/sapa-madani/chat', ['message' => 'berapa biaya permohonan?'])->assertOk()
            ->assertJson(['reply' => AssistantController::NO_AI]);
    }

    public function test_whatsapp_bot_stays_silent_and_officer_gets_no_template_draft(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        WhatsAppGateway::save(['provider' => 'fonnte', 'enabled' => true, 'auto_reply' => true], 'tok-123');
        $url = route('api.whatsapp.webhook', ['secret' => WhatsAppGateway::webhookSecret()]);
        $this->post($url, ['sender' => '081234567890', 'name' => 'Budi', 'message' => 'Berapa harga cabe hari ini', 'inboxid' => 'm1'])->assertOk();

        Http::assertNothingSent();
        $c = WaConversation::sole();
        $this->assertSame(['new', 'AI belum aktif (OPENAI_API_KEY kosong), dibalas petugas'], [$c->status, $c->handoff_reason]);

        $this->actingAs(User::factory()->create())->postJson(route('admin.wa-inbox.redraft', $c))->assertOk()->assertJson(['draft' => '']);
    }

    public function test_with_openai_the_ai_answers(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'Jawaban AI']]]])]);
        $this->postJson('/sapa-madani/chat', ['message' => 'berapa biaya?'])->assertJson(['reply' => 'Jawaban AI']);
    }
}
