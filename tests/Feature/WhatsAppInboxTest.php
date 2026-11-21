<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WaConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppInboxTest extends TestCase
{
    use RefreshDatabase;

    private function conversation(string $phone, string $name, string $status, array $messages): WaConversation
    {
        $c = WaConversation::create(['phone' => $phone, 'name' => $name, 'status' => $status, 'last_message_at' => now()]);
        foreach ($messages as $i => [$direction, $body]) {
            $c->messages()->create(['direction' => $direction, 'body' => $body, 'status' => $direction === 'in' ? 'received' : 'sent', 'sent_at' => now()->addMinutes($i)]);
        }

        return $c;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $c = $this->conversation('6281200000001', 'Budi', 'new', [['in', 'Halo']]);

        $this->get(route('admin.wa-inbox.index'))->assertRedirect(route('login'));
        $this->get(route('admin.wa-inbox.show', $c))->assertRedirect(route('login'));
    }

    public function test_index_lists_conversations_with_badge_only_for_unread(): void
    {
        $this->conversation('6281200000001', 'Budi Santoso', 'new', [['in', 'Syarat KTP apa saja?']]);
        $this->conversation('6281200000002', 'Sari Dewi', 'read', [['in', 'Halo'], ['out', 'Jawaban petugas terakhir']]);

        $html = $this->actingAs(User::factory()->create())->get(route('admin.wa-inbox.index'))
            ->assertOk()
            ->assertSee('Budi Santoso')->assertSee('Syarat KTP apa saja?')
            ->assertSee('Sari Dewi')->assertSee('Jawaban petugas terakhir') // last message snippet
            ->assertSee('Warga · WhatsApp')
            ->getContent();

        $this->assertSame(1, substr_count($html, '>Baru</span>'));
        $this->assertMatchesRegularExpression('/data-wa-unread>1</', $html); // sidebar badge
    }

    public function test_show_marks_read_and_renders_thread(): void
    {
        $c = $this->conversation('6281200000001', 'Budi', 'new', [['in', 'Pertanyaan <b>warga</b>'], ['out', 'Balasan petugas']]);
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.wa-inbox.show', $c))->assertOk()
            ->assertSee('Pertanyaan warga · diteruskan dari bot WhatsApp')
            ->assertSee('Pertanyaan &lt;b&gt;warga&lt;/b&gt;', false) // escaped
            ->assertSee('Balasan petugas')->assertSee('Petugas PPID')
            ->assertDontSee('data-wa-unread', false); // badge gone once read

        $this->assertSame('read', $c->fresh()->status);

        // Done conversations are not reopened by viewing.
        $c->update(['status' => 'done']);
        $this->get(route('admin.wa-inbox.show', $c))->assertOk();
        $this->assertSame('done', $c->fresh()->status);
    }

    public function test_whatsapp_style_ticks_day_separators_and_unread_count(): void
    {
        $this->travelTo(now('Asia/Jakarta')->setTime(10, 0)->utc());
        $c = WaConversation::create(['phone' => '6281200000001', 'name' => 'Budi', 'status' => 'read', 'last_message_at' => now()]);
        $add = fn ($dir, $body, $status, $at) => $c->messages()->create(['direction' => $dir, 'body' => $body, 'status' => $status, 'sent_at' => $at]);
        $add('in', 'Pesan lama', 'received', now()->subDays(3));
        $add('out', 'Dibaca', 'read', now()->subDays(3)->addMinute());
        $add('in', 'Kemarin warga', 'received', now()->subDay());
        $add('out', 'Diterima', 'delivered', now()->subDay()->addMinute());
        $add('out', 'Terkirim', 'sent', now()->subMinutes(30));
        $add('out', 'Gagal', 'failed', now()->subMinutes(20));
        $add('in', 'Satu', 'received', now()->subMinutes(10));
        $add('in', 'Dua', 'received', now()->subMinutes(5));
        $this->actingAs(User::factory()->create());

        $html = $this->get(route('admin.wa-inbox.show', $c))->assertOk()
            ->assertSee('Hari ini')->assertSee('Kemarin')
            ->assertSee(now()->subDays(3)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y'))
            ->getContent();
        $this->assertSame(3, substr_count($html, 'data-wa-day'));
        foreach (['wa-tick-read', 'wa-tick-delivered', 'wa-tick-sent', 'wa-tick-failed'] as $tick) {
            $this->assertStringContainsString($tick, $html);
        }
        $this->assertSame(8, substr_count(strstr($html, '<template data-wa-pending>', true), 'class="wa-bubble')); // thread only, not the pending-bubble template

        // New conversation: badge counts citizen messages after the last successful reply (failed one ignored).
        $c->update(['status' => 'new']);
        $list = $this->get(route('admin.wa-inbox.index'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/class="wa-count[^"]*"[^>]*>2</', $list);
    }

    public function test_poll_endpoints_return_only_newer_messages_and_changed_ticks(): void
    {
        $c = $this->conversation('6281200000001', 'Budi', 'new', [['in', 'Pertama'], ['out', 'Balasan'], ['in', 'Kedua']]);
        [, $reply, $second] = $c->messages()->orderBy('id')->get()->all();

        $this->getJson(route('admin.wa-inbox.messages', [$c, 'after' => 0]))->assertUnauthorized();
        $this->getJson(route('admin.wa-inbox.summary'))->assertUnauthorized();

        $this->actingAs(User::factory()->create());
        $since = now()->subSecond()->toDateTimeString();
        $reply->update(['status' => 'read']);

        $res = $this->getJson(route('admin.wa-inbox.messages', [$c, 'after' => $reply->id, 'since' => $since]))->assertOk();
        $this->assertSame([$second->id], array_column($res->json('messages'), 'id'));
        $this->assertSame('Hari ini', $res->json('messages.0.day'));
        $this->assertStringContainsString('data-wa-msg="' . $second->id . '"', $res->json('messages.0.html'));
        $this->assertSame([$reply->id], array_column($res->json('changed'), 'id')); // tick update
        $this->assertStringContainsString('wa-tick-read', $res->json('changed.0.html'));
        $this->assertSame('read', $c->fresh()->status); // open chat = read

        $this->getJson(route('admin.wa-inbox.messages', [$c, 'after' => $second->id]))->assertOk()->assertJson(['messages' => [], 'changed' => []]);

        // A new citizen message: list puts it on top with the Baru badge, sidebar count follows.
        $this->conversation('6281200000002', 'Sari', 'new', [['in', 'Pesan baru masuk']]);
        $sum = $this->getJson(route('admin.wa-inbox.summary', ['current' => $c->id]))->assertOk();
        $this->assertSame(1, $sum->json('unread'));
        $list = $sum->json('list');
        $this->assertStringContainsString('>Baru</span>', $list);
        $this->assertLessThan(strpos($list, 'Budi'), strpos($list, 'Sari'));
    }

    public function test_search_and_status_filters(): void
    {
        $this->conversation('6281200000001', 'Budi Santoso', 'new', [['in', 'Syarat KTP apa saja?']]);
        $this->conversation('6281277770002', 'Sari Dewi', 'done', [['in', 'Halo'], ['out', 'Sudah dijawab']]);
        $this->conversation('6281200000003', 'Andi', 'read', [['in', 'Minta data APBD']])->update(['handoff_reason' => 'Permohonan informasi']);
        $this->actingAs(User::factory()->create());
        $index = fn (array $query) => $this->get(route('admin.wa-inbox.index', $query))->assertOk();

        $index(['q' => '081277770002'])->assertSee('Sari Dewi')->assertDontSee('Budi Santoso')->assertDontSee('Andi'); // 08… = 628…
        $index(['q' => 'ktp'])->assertSee('Budi Santoso')->assertDontSee('Sari Dewi'); // message text, any case
        $index(['q' => 'budi'])->assertSee('Budi Santoso')->assertDontSee('Andi');
        $index(['status' => 'done'])->assertSee('Sari Dewi')->assertDontSee('Budi Santoso')->assertDontSee('Andi');
        $index(['status' => 'new'])->assertSee('Budi Santoso')->assertDontSee('Sari Dewi');
        $index(['status' => 'handoff'])->assertSee('Andi')->assertDontSee('Budi Santoso')->assertDontSee('Sari Dewi');
        $index(['q' => 'tidak ada'])->assertSee('Tidak ada percakapan yang cocok.');

        // Filters stay in conversation links and the live list poll.
        $index(['status' => 'done', 'q' => 'sari'])
            ->assertSee('status=done', false)
            ->assertSee(route('admin.wa-inbox.summary', ['q' => 'sari', 'status' => 'done'])); // escaped &amp;
        $list = $this->getJson(route('admin.wa-inbox.summary', ['status' => 'done']))->assertOk()->json('list');
        $this->assertStringContainsString('Sari Dewi', $list);
        $this->assertStringNotContainsString('Budi Santoso', $list);
    }

    public function test_contact_card_saves_name_label_and_notes(): void
    {
        $c = $this->conversation('6281200000001', 'bdi', 'read', [['in', 'Halo']]);
        $this->actingAs(User::factory()->create());

        $this->put(route('admin.wa-inbox.contact', $c), ['contact_name' => 'Budi Santoso', 'label' => 'keberatan', 'notes' => 'Wartawan koran lokal'])
            ->assertRedirect(route('admin.wa-inbox.show', $c))->assertSessionHas('success');
        $this->assertSame(['Budi Santoso', 'keberatan', 'Wartawan koran lokal'], [$c->fresh()->contact_name, $c->fresh()->label, $c->fresh()->notes]);

        $this->get(route('admin.wa-inbox.show', $c))->assertOk()
            ->assertSee('Budi Santoso')->assertSee('Keberatan')->assertSee('Wartawan koran lokal')->assertSee('Nama WA: bdi');
        $this->get(route('admin.wa-inbox.index', ['q' => 'santoso']))->assertSee('Budi Santoso'); // officer name searchable
        $this->get(route('admin.wa-inbox.index', ['q' => 'wartawan']))->assertSee('Budi Santoso'); // notes too

        // A new WhatsApp message keeps the officer's name (only the profile name changes).
        $this->post(route('api.whatsapp.webhook', ['secret' => \App\Support\WhatsAppGateway::webhookSecret()]), ['sender' => '6281200000001', 'name' => 'Budi WA', 'message' => 'Halo lagi'])->assertOk();
        $this->assertSame('Budi Santoso', $c->fresh()->displayName());

        // Empty name falls back to the WhatsApp name; unknown label rejected.
        $this->put(route('admin.wa-inbox.contact', $c), ['contact_name' => '', 'label' => ''])->assertRedirect();
        $this->assertSame('Budi WA', $c->fresh()->displayName());
        $this->assertNull($c->fresh()->label);
        $this->put(route('admin.wa-inbox.contact', $c), ['label' => 'vip'])->assertSessionHasErrors('label');
    }
}
