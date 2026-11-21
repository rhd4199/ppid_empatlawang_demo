<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WaConversation;
use App\Support\WhatsAppGateway;
use App\Support\WhatsAppInbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppNewChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        WhatsAppGateway::save(['provider' => 'fonnte', 'enabled' => true], 'tok-123');
        $this->actingAs(User::factory()->create());
    }

    public function test_new_number_creates_conversation_and_sends_first_message(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        $this->get(route('admin.wa-inbox.index'))->assertOk()->assertSee('Chat baru')->assertSee(route('admin.wa-inbox.start'), false);

        $res = $this->post(route('admin.wa-inbox.start'), ['new_phone' => '0812-3456-7890', 'new_name' => 'Budi', 'new_body' => 'Selamat pagi, ini PPID.']);

        $c = WaConversation::sole();
        $res->assertRedirect(route('admin.wa-inbox.show', $c))->assertSessionHas('success');
        $this->assertSame(['6281234567890', 'Budi', 'done'], [$c->phone, $c->contact_name, $c->status]);
        $out = $c->messages()->sole();
        $this->assertSame(['out', 'sent', 'Selamat pagi, ini PPID.'], [$out->direction, $out->status, $out->body]);
        Http::assertSent(fn (Request $r) => $r['target'] === '6281234567890' && $r['message'] === 'Selamat pagi, ini PPID.');
    }

    public function test_existing_number_reuses_conversation_and_keeps_name(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        WhatsAppInbox::store([['id' => 'in-1', 'phone' => '6281234567890', 'name' => 'Budi WA', 'text' => 'Halo', 'time' => null]]);
        WaConversation::sole()->update(['contact_name' => 'Pak Budi']);

        $this->post(route('admin.wa-inbox.start'), ['new_phone' => '+62 812 3456 7890', 'new_name' => 'Lain', 'new_body' => 'Tindak lanjut'])
            ->assertRedirect(route('admin.wa-inbox.show', WaConversation::sole()));

        $c = WaConversation::sole();
        $this->assertSame('Pak Budi', $c->contact_name);
        $this->assertSame(['in', 'out'], $c->messages()->orderBy('id')->pluck('direction')->all());
    }

    public function test_invalid_number_is_rejected_without_sending(): void
    {
        Http::fake();
        foreach (['12345', '021555123', 'abc', '6281234567890123456'] as $phone) {
            $this->from(route('admin.wa-inbox.index'))
                ->post(route('admin.wa-inbox.start'), ['new_phone' => $phone, 'new_body' => 'Halo'])
                ->assertRedirect(route('admin.wa-inbox.index'))->assertSessionHasErrors('new_phone');
        }
        $this->post(route('admin.wa-inbox.start'), ['new_phone' => '081234567890', 'new_body' => ' '])->assertSessionHasErrors('new_body');

        $this->assertSame(0, WaConversation::count());
        Http::assertNothingSent();
    }

    public function test_provider_failure_shows_red_bubble(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'nomor tidak terdaftar'])]);

        $this->post(route('admin.wa-inbox.start'), ['new_phone' => '081234567890', 'new_body' => 'Halo warga'])
            ->assertSessionHas('error', 'Pesan gagal dikirim: Provider menolak: nomor tidak terdaftar');

        $c = WaConversation::sole();
        $this->assertSame('failed', $c->messages()->sole()->status);
        $this->get(route('admin.wa-inbox.show', $c))->assertOk()->assertSee('Gagal terkirim')->assertSee('Halo warga');
    }
}
