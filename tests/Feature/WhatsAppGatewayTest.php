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
