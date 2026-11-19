<?php

namespace Tests\Feature;

use App\Models\PpidSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    private function set(array $values): void
    {
        foreach ($values as $key => $value) {
            PpidSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    public function test_wa_link_normalizes_numbers(): void
    {
        $this->assertSame('https://wa.me/6281234567890', wa_link('0812-3456-7890'));
        $this->assertSame('https://wa.me/6281234567890', wa_link('+62 812 3456 7890'));
        $this->assertNull(wa_link(''));
        $this->assertNull(wa_link('123'));
    }

    public function test_without_whatsapp_the_chat_panel_and_bottom_bar_render(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('id="smPanel"', false)
            ->assertSee('sm-bottombar', false)
            ->assertDontSee('wa.me/', false);
    }

    public function test_with_whatsapp_buttons_open_whatsapp(): void
    {
        $this->set(['assistant_whatsapp' => '0812-3456-7890']);

        $this->get('/')->assertOk()
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertDontSee('id="smPanel"', false);
    }

    public function test_disabled_hides_assistant_but_keeps_bottom_bar(): void
    {
        $this->set(['assistant_enabled' => '0']);

        $this->get('/')->assertOk()
            ->assertDontSee('Sapa Madani</span>', false)
            ->assertSee('sm-bottombar', false);
        $this->postJson('/sapa-madani/chat', ['message' => 'halo'])->assertNotFound();
    }

    public function test_chat_uses_placeholder_then_configured_api(): void
    {
        $this->postJson('/sapa-madani/chat', ['message' => 'halo'])->assertOk()->assertJsonPath('reply', fn ($r) => str_contains($r, 'Sapa Madani'));
        $this->postJson('/sapa-madani/chat', ['message' => ''])->assertUnprocessable();

        $this->set(['assistant_api_url' => 'https://bot.example.test/chat']);
        Http::fake(['bot.example.test/*' => Http::response(['reply' => 'Jawaban API'])]);
        $this->postJson('/sapa-madani/chat', ['message' => 'halo'])->assertJsonPath('reply', 'Jawaban API');
        Http::assertSent(fn ($req) => $req['message'] === 'halo');
    }

    public function test_api_failure_returns_friendly_reply(): void
    {
        $this->set(['assistant_api_url' => 'https://bot.example.test/chat']);
        Http::fake(['bot.example.test/*' => Http::response('down', 500)]);

        $this->postJson('/sapa-madani/chat', ['message' => 'halo'])->assertOk()->assertJsonPath('reply', fn ($r) => str_starts_with($r, 'Maaf'));
    }

    public function test_admin_saves_settings_and_validates(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.contact-settings.update-assistant'), [
            'assistant_enabled' => '1',
            'assistant_whatsapp' => '0812 3456 7890',
            'assistant_api_url' => 'https://bot.example.test/chat',
        ])->assertRedirect(route('admin.contact-settings.index'));

        $this->assertSame('https://wa.me/6281234567890', assistant_settings()['wa_url']);
        $this->assertSame('https://bot.example.test/chat', assistant_settings()['api_url']);

        $this->actingAs($admin)->put(route('admin.contact-settings.update-assistant'), [
            'assistant_whatsapp' => '<script>',
            'assistant_api_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors(['assistant_whatsapp', 'assistant_api_url']);

        $this->put(route('admin.contact-settings.update-assistant'), [])->assertRedirect();
        auth()->logout();
        $this->put(route('admin.contact-settings.update-assistant'), [])->assertRedirect('/login');
    }
}
