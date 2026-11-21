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
        $this->assertSame(8, substr_count($html, 'class="wa-bubble'));

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
}
