<?php

namespace Tests\Feature;

use App\Models\PpidSetting;
use App\Models\User;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppGatewayTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'secret-fonnte-token-123';

    private function saveSettings(array $override = []): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.whatsapp.update'), array_merge([
                'provider' => 'fonnte', 'api_url' => '', 'token' => self::TOKEN,
                'sender' => '628972007121', 'enabled' => 1,
            ], $override))
            ->assertRedirect(route('admin.whatsapp.index'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.whatsapp.index'))->assertRedirect(route('login'));
    }

    public function test_old_menu_url_opens_chat_with_settings_tab(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/whatsapp-gateway')->assertRedirect('/admin/kotak-masuk-wa');
        $this->get(route('admin.wa-inbox.index'))->assertOk()->assertSee(route('admin.whatsapp.index'));
        $this->get(route('admin.whatsapp.index'))->assertOk()->assertSee(route('admin.wa-inbox.index'));
    }

    public function test_settings_saved_and_token_never_shown(): void
    {
        $this->saveSettings();

        $stored = PpidSetting::where('key', 'wa_token')->value('value');
        $this->assertNotSame(self::TOKEN, $stored); // encrypted at rest

        $this->get(route('admin.whatsapp.index'))->assertOk()
            ->assertDontSee(self::TOKEN)->assertDontSee($stored)
            ->assertSee('tersimpan')->assertSee('628972007121');

        // Empty token field keeps the stored token.
        $this->saveSettings(['token' => '']);
        $this->assertSame($stored, PpidSetting::where('key', 'wa_token')->value('value'));
        $this->assertTrue(WhatsAppGateway::settings()['enabled']);
    }

    public function test_test_button_calls_fonnte_with_number_and_text(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        $this->saveSettings(['enabled' => 0]); // test works before switching on

        $this->post(route('admin.whatsapp.test'), ['test_phone' => '0812-3456-7890', 'test_message' => 'Halo uji'])
            ->assertRedirect(route('admin.whatsapp.index'))->assertSessionHas('success');

        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.fonnte.com/send'
            && $r->header('Authorization') === [self::TOKEN]
            && $r['target'] === '6281234567890' && $r['message'] === 'Halo uji');
    }

    public function test_provider_failure_is_reported(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'invalid token'])]);
        $this->saveSettings();

        $this->post(route('admin.whatsapp.test'), ['test_phone' => '081234567890'])
            ->assertSessionHas('error', 'Provider menolak: invalid token');
    }

    private const LARIKA_KEY = 'lk_test_secret_key';

    private function useLarika(): void
    {
        config(['services.larika.key' => self::LARIKA_KEY, 'services.larika.url' => 'https://gateway.larika.id']);
        $this->saveSettings(['provider' => 'larika', 'token' => '']);
    }

    public function test_larika_queued_202_sends_bearer_and_ref(): void
    {
        Http::fake(['gateway.larika.id/*' => Http::response(['id' => 'cm1', 'status' => 'QUEUED', 'ref' => 'x'], 202)]);
        $this->useLarika();

        $this->assertSame(['ok' => true, 'error' => null], WhatsAppGateway::send('0812-3456-7890', 'Halo', ref: 'wa-out-7'));
        Http::assertSent(fn (Request $r) => $r->url() === 'https://gateway.larika.id/v1/messages'
            && $r->header('Authorization') === ['Bearer ' . self::LARIKA_KEY]
            && $r['to'] === '6281234567890' && $r['text'] === 'Halo' && $r['ref'] === 'wa-out-7');

        // Admin test button: ref "admin-test-<uuid>", key never in the page.
        $this->post(route('admin.whatsapp.test'), ['test_phone' => '081234567890'])->assertSessionHas('success');
        Http::assertSent(fn (Request $r) => str_starts_with((string) $r['ref'], 'admin-test-'));
        $this->get(route('admin.whatsapp.index'))->assertOk()->assertSee('LARIKA_KEY')->assertDontSee(self::LARIKA_KEY);

        // Inbox replies use the outgoing row id as ref.
        $c = \App\Models\WaConversation::create(['phone' => '6281234567890']);
        $this->assertTrue(WhatsAppGateway::sendLogged($c, 'Balasan')['ok']);
        $row = $c->messages()->sole();
        $this->assertSame('sent', $row->status);
        Http::assertSent(fn (Request $r) => $r['ref'] === 'wa-out-' . $row->id);
    }

    public function test_larika_422_and_429_are_reported_without_retry(): void
    {
        Http::fakeSequence('gateway.larika.id/*')
            ->push(['error' => 'invalid_number'], 422)
            ->push(['error' => 'daily_cap_reached', 'nextSlotAt' => '2026-11-22T01:00:00.000Z'], 429, ['Retry-After' => '3600'])
            ->push(['error' => 'daily_cap_reached'], 429, ['Retry-After' => '120']);
        $this->useLarika();

        $this->assertSame('Provider menolak: invalid_number', WhatsAppGateway::send('081234567890', 'a')['error']);
        $cap = WhatsAppGateway::send('081234567890', 'b');
        $this->assertFalse($cap['ok']);
        $this->assertStringContainsString('22/11/2026 08:00 WIB', $cap['error']);
        $this->assertStringContainsString('120 detik', WhatsAppGateway::send('081234567890', 'c')['error']);
        Http::assertSentCount(3); // one call per send, no retry loop
    }

    public function test_larika_without_key(): void
    {
        Http::fake();
        $this->useLarika();
        config(['services.larika.key' => null]);

        $this->post(route('admin.whatsapp.test'), ['test_phone' => '081234567890'])
            ->assertSessionHas('error', 'LARIKA_KEY belum diisi di env server.');
        Http::assertNothingSent();
    }

    public function test_cloud_api_and_disabled_gateway(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'x']]])]);
        $this->saveSettings(['provider' => 'cloud', 'sender' => '1234567890', 'enabled' => 0]);

        $this->assertFalse(WhatsAppGateway::send('081234567890', 'x')['ok']); // off → nothing sent
        Http::assertNothingSent();

        $this->assertTrue(WhatsAppGateway::send('081234567890', 'Halo', force: true)['ok']);
        Http::assertSent(fn (Request $r) => $r->url() === 'https://graph.facebook.com/v21.0/1234567890/messages'
            && $r->header('Authorization') === ['Bearer ' . self::TOKEN]
            && $r['to'] === '6281234567890' && $r['text']['body'] === 'Halo');
    }
}
