<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Models\WaConversation;
use App\Support\WhatsAppGateway;
use App\Support\WhatsAppInbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppReplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.key' => null]); // fallback drafts; the real key in .env must not be used
        WhatsAppGateway::save(['provider' => 'fonnte', 'enabled' => true], 'tok-123');
        $this->actingAs(User::factory()->create());
    }

    private function incoming(string $text, string $id = 'in-1'): WaConversation
    {
        WhatsAppInbox::store([['id' => $id, 'phone' => '6281234567890', 'name' => 'Budi', 'text' => $text, 'time' => null]]);

        return WaConversation::where('phone', '6281234567890')->sole();
    }

    public function test_draft_mentions_free_and_links_document(): void
    {
        $doc = Document::create(['title' => 'Laporan Kinerja Instansi Pemerintah (LKjIP) Tahun 2025', 'category' => 'informasi-publik-berkala', 'file_path' => 'docs/x.pdf', 'is_published' => true]);
        Document::create(['title' => 'Peraturan Presiden Nomor 16 Tahun 2018', 'description' => 'diubah 2025', 'category' => 'informasi-publik-berkala', 'file_path' => 'docs/y.pdf', 'is_published' => true]);
        $c = $this->incoming('Selamat pagi, saya mau minta salinan Laporan Kinerja 2025, ada biaya?');

        $this->get(route('admin.wa-inbox.show', $c))->assertOk()->assertSee('Setujui &amp; kirim', false)->assertSee('Ubah');

        $draft = $c->fresh()->reply_draft;
        $this->assertStringContainsString('GRATIS', $draft);
        $this->assertStringContainsString(route('informasi-publik.show', $doc->id), $draft);
        $this->assertStringNotContainsString('Peraturan Presiden', $draft); // year-only match is not a match
    }

    public function test_document_not_listed_still_mentions_free(): void
    {
        $c = $this->incoming('minta salinan Laporan Kinerja 2025, ada biaya?');
        $this->get(route('admin.wa-inbox.show', $c))->assertOk();

        $draft = $c->fresh()->reply_draft;
        $this->assertStringContainsString('GRATIS', $draft);
        $this->assertStringContainsString(route('request.create'), $draft);
    }

    public function test_approve_sends_edited_text_and_closes_conversation(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        $c = $this->incoming('Halo, jam layanan kantor PPID?');
        $this->get(route('admin.wa-inbox.show', $c));
        $this->assertNotEmpty($c->fresh()->reply_draft);

        $this->post(route('admin.wa-inbox.send', $c), ['body' => 'Jawaban hasil edit petugas'])
            ->assertRedirect(route('admin.wa-inbox.show', $c))->assertSessionHas('success');

        Http::assertSent(fn (Request $r) => $r['target'] === '6281234567890' && $r['message'] === 'Jawaban hasil edit petugas');
        $c->refresh();
        $this->assertSame(['done', null], [$c->status, $c->reply_draft]);
        $out = $c->messages()->where('direction', 'out')->sole();
        $this->assertSame(['sent', 'Jawaban hasil edit petugas', null], [$out->status, $out->body, $out->error]);

        // Answered: viewing again does not draft (or call the AI) again.
        $this->get(route('admin.wa-inbox.show', $c))->assertOk()->assertDontSee('Setujui &amp; kirim', false);
        $this->assertNull($c->fresh()->reply_draft);
    }

    public function test_provider_failure_shown_and_not_marked_sent(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'device disconnected'])]);
        $c = $this->incoming('Halo');

        $this->post(route('admin.wa-inbox.send', $c), ['body' => 'Balasan saya'])
            ->assertSessionHas('error', 'Pesan gagal dikirim: Provider menolak: device disconnected');

        $c->refresh();
        $this->assertNotSame('done', $c->status);
        $this->assertSame('Balasan saya', $c->reply_draft); // kept for retry
        $out = $c->messages()->where('direction', 'out')->sole();
        $this->assertSame(['failed', 'Provider menolak: device disconnected'], [$out->status, $out->error]);

        $this->get(route('admin.wa-inbox.show', $c))->assertOk()->assertSee('Gagal terkirim')->assertSee('device disconnected')->assertSee('Balasan saya');
    }

    public function test_redraft_and_new_message_reset_draft(): void
    {
        $c = $this->incoming('Halo');
        $c->update(['reply_draft' => 'draf lama']);

        $this->post(route('admin.wa-inbox.redraft', $c))->assertRedirect(route('admin.wa-inbox.show', $c));
        $this->assertNotSame('draf lama', $c->fresh()->reply_draft);

        $this->incoming('Pertanyaan baru', 'in-2');
        $this->assertNull($c->fresh()->reply_draft);
    }

    public function test_composer_sends_free_text_and_suggestion_card_only_with_draft(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        $c = $this->incoming('Halo, jam layanan kantor PPID?');

        $html = $this->get(route('admin.wa-inbox.show', $c))->assertOk()->getContent();
        $this->assertStringContainsString('data-wa-compose', $html);
        $this->assertStringContainsString('Saran balasan Sapa Madani', $html);
        $this->assertStringContainsString('Pakai/Ubah', $html);

        // Free text typed in the bar, not the AI draft.
        $this->post(route('admin.wa-inbox.send', $c), ['body' => 'Ketik bebas petugas'])->assertSessionHas('success');
        $out = $c->messages()->where('direction', 'out')->sole();
        $this->assertSame(['Ketik bebas petugas', null, 'sent'], [$out->body, $out->sender, $out->status]);

        // No draft (empty or none): bar stays, suggestion card hidden.
        foreach ([null, ''] as $draft) {
            $c->update(['reply_draft' => $draft]);
            $html = $this->get(route('admin.wa-inbox.show', $c))->assertOk()->assertSee('Petugas PPID')->assertSee('Ketik bebas petugas')->getContent();
            $this->assertStringContainsString('data-wa-compose', $html);
            $this->assertStringNotContainsString('Saran balasan Sapa Madani', $html);
        }
    }

    public function test_json_send_returns_bubble_without_redirect(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        $c = $this->incoming('Halo');

        $res = $this->postJson(route('admin.wa-inbox.send', $c), ['body' => 'Balasan <b>cepat</b>'])->assertOk();

        $out = $c->messages()->where('direction', 'out')->sole();
        $res->assertJson(['ok' => true, 'error' => null, 'id' => $out->id, 'day' => 'Hari ini']);
        $this->assertStringContainsString('data-wa-msg="' . $out->id . '"', $res->json('html'));
        $this->assertStringContainsString('Balasan &lt;b&gt;cepat&lt;/b&gt;', $res->json('html'));
        $this->assertStringContainsString('wa-tick-sent', $res->json('html'));
        $this->assertSame(['done', null], [$c->fresh()->status, $c->fresh()->reply_draft]);

        $this->postJson(route('admin.wa-inbox.send', $c), ['body' => ' '])->assertStatus(422)->assertJsonValidationErrors('body');
    }

    public function test_json_send_failure_returns_failed_bubble_and_keeps_draft(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'device disconnected'])]);
        $c = $this->incoming('Halo');

        $res = $this->postJson(route('admin.wa-inbox.send', $c), ['body' => 'Balasan saya'])->assertOk()
            ->assertJson(['ok' => false, 'error' => 'Provider menolak: device disconnected']);

        $this->assertStringContainsString('Gagal terkirim', $res->json('html'));
        $this->assertSame('Balasan saya', $c->fresh()->reply_draft);
        $this->assertSame('failed', $c->messages()->where('direction', 'out')->sole()->status);
    }
}
