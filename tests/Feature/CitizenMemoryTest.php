<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WaConversation;
use App\Support\CitizenMemory;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CitizenMemoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_bot_sends_memory_and_ai_saves_new_facts_per_citizen(): void
    {
        config(['services.openai.key' => 'sk-test']);
        WhatsAppGateway::save(['provider' => 'fonnte', 'enabled' => true, 'auto_reply' => true], 'tok-123');
        WaConversation::create(['phone' => '6281234567890', 'name' => 'Budi', 'status' => 'done', 'notes' => 'Minta LKjIP 2025',
            'ai_memory' => '- 20-11-2026: tinggal di Desa Muara Kalangan']);
        Http::fake([
            'api.openai.com/*' => Http::sequence()
                ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => null, 'tool_calls' => [
                    ['id' => 'c1', 'type' => 'function', 'function' => ['name' => 'ingat_warga', 'arguments' => '{"catatan":"sedang mengurus KTP hilang, NIK 1611012345678901"}']],
                ]]]]])
                ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => 'Baik Pak Budi.']]]]),
            'api.fonnte.com/*' => Http::response(['status' => true]),
        ]);

        $url = route('api.whatsapp.webhook', ['secret' => WhatsAppGateway::webhookSecret()]);
        $this->post($url, ['sender' => '081234567890', 'name' => 'Budi', 'message' => 'KTP saya hilang', 'inboxid' => 'm1'])->assertOk();

        $first = Http::recorded()->map(fn ($p) => $p[0])->first(fn (Request $r) => str_contains($r->url(), 'openai'));
        $system = $first['messages'][0]['content'];
        $this->assertStringContainsString('Nama: Budi', $system);
        $this->assertStringContainsString('Catatan petugas: Minta LKjIP 2025', $system);
        $this->assertStringContainsString('Desa Muara Kalangan', $system);
        $this->assertContains('ingat_warga', array_column(array_column($first['tools'], 'function'), 'name'));

        $memory = WaConversation::sole()->ai_memory;
        $this->assertStringContainsString('sedang mengurus KTP hilang', $memory);
        $this->assertStringNotContainsString('1611012345678901', $memory); // long numbers never stored
        $this->assertStringContainsString('Desa Muara Kalangan', $memory); // older facts kept
    }

    public function test_web_chat_has_no_citizen_memory_tool(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'ok']]]])]);
        $this->postJson('/sapa-madani/chat', ['message' => 'halo'])->assertOk();
        Http::assertSent(fn (Request $r) => ! in_array('ingat_warga', array_column(array_column($r['tools'], 'function'), 'name'), true));
    }

    public function test_memory_is_capped_and_officer_can_edit_it(): void
    {
        $c = WaConversation::create(['phone' => '6281200000001', 'status' => 'read']);
        foreach (range(1, 40) as $i) {
            CitizenMemory::remember($c, "fakta nomor {$i} " . str_repeat('x', 40));
        }
        $this->assertLessThanOrEqual(CitizenMemory::MAX, mb_strlen($c->fresh()->ai_memory));
        $this->assertStringContainsString('fakta nomor 40', $c->fresh()->ai_memory); // newest kept

        $this->actingAs(User::factory()->create())->put(route('admin.wa-inbox.contact', $c), ['ai_memory' => 'dikoreksi petugas'])->assertRedirect();
        $this->assertSame('dikoreksi petugas', $c->fresh()->ai_memory);
    }
}
