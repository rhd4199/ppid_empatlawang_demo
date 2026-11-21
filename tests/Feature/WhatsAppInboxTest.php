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
}
