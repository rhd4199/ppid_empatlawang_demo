<?php

namespace Tests\Feature;

use App\Http\Controllers\AssistantController;
use App\Models\ServiceRequirement;
use App\Support\AssistantTools;
use App\Support\WhatsAppFormat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ServiceRequirement::create(['title' => 'KTP-el Baru (Pertama Kali)', 'slug' => 'ktp-el-baru', 'category' => 'kependudukan',
            'agency' => 'Disdukcapil Kabupaten Empat Lawang', 'keywords' => 'ktp baru', 'requirements' => '<ol><li>Berusia 17 tahun.</li><li>Fotokopi KK.</li></ol>',
            'cost' => 'Gratis', 'is_published' => true]);
        ServiceRequirement::create(['title' => 'Draft Rahasia', 'slug' => 'draft', 'category' => 'kependudukan', 'agency' => 'X',
            'requirements' => '<p>x</p>', 'is_published' => false]);
    }

    public function test_tools_list_search_and_detail_published_rows_only(): void
    {
        $list = AssistantTools::run('daftar_persyaratan', []);
        $this->assertSame([['judul' => 'KTP-el Baru (Pertama Kali)', 'slug' => 'ktp-el-baru']], $list['Kependudukan']->all());
        $this->assertSame(route('service-requirements.index'), $list['halaman']);

        $this->assertSame('ktp-el-baru', AssistantTools::run('cari_persyaratan', ['kata_kunci' => 'ktp baru'])['hasil'][0]['slug']);
        $detail = AssistantTools::run('detail_persyaratan', ['slug' => 'ktp-el-baru']);
        $this->assertSame(['Berusia 17 tahun.', 'Fotokopi KK.'], $detail['persyaratan']);
        $this->assertSame(route('service-requirements.show', 'ktp-el-baru'), $detail['tautan']);
        $this->assertArrayHasKey('error', AssistantTools::run('detail_persyaratan', ['slug' => 'draft']));
    }

    public function test_site_data_tools(): void
    {
        \App\Models\Official::create(['name' => 'AKBP Contoh', 'position' => 'Kapolres Empat Lawang', 'order' => 1, 'is_published' => true]);
        \App\Models\Official::create(['name' => 'Rahasia', 'position' => 'Kapolres Lama', 'order' => 2, 'is_published' => false]);
        \App\Models\News::create(['title' => 'Bupati Resmikan Pasar', 'slug' => 'pasar', 'content' => '<p>Isi <b>berita</b></p>', 'is_published' => true, 'published_at' => now()->subDay()]);
        \App\Models\News::create(['title' => 'Bupati Besok', 'slug' => 'besok', 'content' => 'x', 'is_published' => true, 'published_at' => now()->addDay()]);
        \App\Models\Event::create(['title' => 'Rapat Lalu', 'slug' => 'lalu', 'start_date' => now()->subDays(3)]);
        \App\Models\Event::create(['title' => 'Rapat Nanti', 'slug' => 'nanti', 'start_date' => now()->addDays(3), 'location' => 'Aula']);

        $this->assertSame([['jabatan' => 'Kapolres Empat Lawang', 'nama' => 'AKBP Contoh']], AssistantTools::run('daftar_pejabat', ['jabatan' => 'Kapolres'])['pejabat']);
        $this->assertIsString(AssistantTools::run('daftar_pejabat', ['jabatan' => 'dandim'])['pejabat']); // not in data: say so
        $news = AssistantTools::run('cari_berita', ['kata_kunci' => 'bupati'])['berita'];
        $this->assertSame(['Bupati Resmikan Pasar'], array_column($news, 'judul')); // future post hidden
        $this->assertSame('Isi berita', $news[0]['ringkasan']);
        $this->assertSame(['Rapat Nanti'], array_column(AssistantTools::run('agenda_kegiatan', [])['agenda'], 'kegiatan'));
        $this->assertSame(['Rapat Lalu', 'Rapat Nanti'], array_column(AssistantTools::run('agenda_kegiatan', ['dari_tanggal' => now()->subWeek()->toDateString()])['agenda'], 'kegiatan'));
        $this->assertSame(route('contact.index'), AssistantTools::run('kontak_ppid', [])['halaman_kontak']);
        $this->assertArrayHasKey('hasil', AssistantTools::run('cari_dokumen', ['kata_kunci' => 'apbd']));
        $this->assertCount(28, AssistantTools::definitions());
        config(['services.openai.search_model' => '']);
        $this->assertNotContains('cari_web', array_column(array_column(AssistantTools::definitions(), 'function'), 'name')); // switched off
    }

    public function test_web_search_tool_returns_answer_with_sources_or_an_honest_error(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::sequence()
            ->push(['output' => [['type' => 'web_search_call'], ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Kapolres saat ini AKBP Contoh (2026).', 'annotations' => [
                ['type' => 'url_citation', 'url' => 'https://berita.test/a', 'title' => 'Berita A'],
                ['type' => 'url_citation', 'url' => 'https://berita.test/a', 'title' => 'Berita A'],
            ]]]]]])
            ->push(['error' => 'boom'], 500)]);

        $ok = AssistantTools::run('cari_web', ['pertanyaan' => 'kapolres terbaru']);
        $this->assertSame('Kapolres saat ini AKBP Contoh (2026).', $ok['jawaban']);
        $this->assertSame([['judul' => 'Berita A', 'url' => 'https://berita.test/a']], $ok['sumber']); // de-duplicated
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/v1/responses') && $r['model'] === 'gpt-4o-mini' && $r['tools'][0]['type'] === 'web_search'
            && str_contains($r['input'], 'Empat Lawang'));

        $this->assertArrayHasKey('error', AssistantTools::run('cari_web', ['pertanyaan' => 'x'])); // API failure never throws
        $this->assertArrayHasKey('error', AssistantTools::run('cari_web', ['pertanyaan' => '  ']));

        // same question again (other case/spacing): from the cache, no third request; the failure was not cached
        $this->assertSame($ok, AssistantTools::run('cari_web', ['pertanyaan' => 'Kapolres   TERBARU']));
        Http::assertSentCount(2);
    }

    public function test_system_prompt_starts_with_the_static_part_for_prompt_caching(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['role' => 'assistant', 'content' => 'Halo']]]])]);
        $this->travelTo(now('Asia/Jakarta')->setTime(9, 0));
        app(AssistantController::class)->answer('halo');
        $this->travelTo(now('Asia/Jakarta')->setTime(15, 37));
        app(AssistantController::class)->answer('jam layanan');

        [$a, $b] = Http::recorded()->map(fn ($pair) => $pair[0]['messages'][0]['content'])->values()->all();
        $prefix = strspn($a ^ $b, "\0"); // identical leading bytes
        $this->assertGreaterThan(0.8 * strlen($a), $prefix, 'time/date must sit at the end of the system prompt');
        $this->assertStringContainsString('pukul 15:37', $b);
        Http::assertSent(fn (Request $r) => $r['prompt_cache_key'] === 'sapa-madani');
    }

    public function test_openai_runs_tool_calls_then_answers_in_whatsapp_format(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::sequence()
            ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => null, 'tool_calls' => [
                ['id' => 'call_1', 'type' => 'function', 'function' => ['name' => 'detail_persyaratan', 'arguments' => '{"slug":"ktp-el-baru"}']],
            ]]]]])
            ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => "## Syarat KTP\n**Penting:** bawa KK\n- Fotokopi KK\n[Rincian](https://x.test/p)"]]]])]);

        $reply = app(AssistantController::class)->answer('syarat bikin ktp', [], 'wa');

        $this->assertSame("*Syarat KTP*\n*Penting:* bawa KK\n• Fotokopi KK\nRincian: https://x.test/p", $reply);
        $sent = Http::recorded()->map(fn ($pair) => $pair[0]);
        $this->assertCount(2, $sent);
        $this->assertSame('detail_persyaratan', $sent[0]['tools'][2]['function']['name']);
        $this->assertStringContainsString('format WhatsApp', $sent[0]['messages'][0]['content']);
        $tool = collect($sent[1]['messages'])->firstWhere('role', 'tool');
        $this->assertSame('call_1', $tool['tool_call_id']);
        $this->assertStringContainsString('Fotokopi KK', $tool['content']);
    }

    public function test_not_found_answer_without_web_search_gets_one_automatically(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::sequence()
            ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => 'Maaf Bapak/Ibu, data tentang siapa Sekretaris Satgas Makan Bergizi Gratis (MBG) belum tersedia di data resmi kami.']]]])
            ->push(['output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Sekretaris Satgas MBG: Fulan (2026).', 'annotations' => []]]]]])
            ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => 'Menurut berita daring, sekretarisnya Fulan.']]]])]);

        $this->assertSame('Menurut berita daring, sekretarisnya Fulan.', app(AssistantController::class)->answer('siapa sekretaris Satgas MBG'));
        $sent = Http::recorded()->map(fn ($pair) => $pair[0])->values();
        $this->assertCount(3, $sent);
        $this->assertStringEndsWith('/v1/responses', $sent[1]->url());
        $this->assertStringContainsString('siapa sekretaris Satgas MBG', $sent[1]['input']);
        $tool = collect($sent[2]['messages'])->firstWhere('role', 'tool');
        $this->assertSame('auto_web', $tool['tool_call_id']);
        $this->assertStringContainsString('Fulan', $tool['content']);
    }

    public function test_not_found_answer_after_a_web_search_is_kept(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::sequence()
            ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => null, 'tool_calls' => [
                ['id' => 'w', 'type' => 'function', 'function' => ['name' => 'cari_web', 'arguments' => '{"pertanyaan":"sekretaris satgas mbg"}']],
            ]]]]])
            ->push(['output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Tidak ditemukan.', 'annotations' => []]]]]])
            ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => 'Sudah saya cari di web, namanya belum ditemukan.']]]])]);

        $this->assertSame('Sudah saya cari di web, namanya belum ditemukan.', app(AssistantController::class)->answer('siapa sekretaris Satgas MBG'));
        Http::assertSentCount(3); // no second, automatic search
    }

    public function test_says_not_found_matches_refusals_only(): void
    {
        $this->assertTrue(AssistantController::saysNotFound('Maaf, data tentang siapa Sekretaris Satgas MBG belum tersedia di data resmi kami.'));
        $this->assertTrue(AssistantController::saysNotFound('Saya belum menemukan informasinya.'));
        $this->assertTrue(AssistantController::saysNotFound('Jawabannya belum bisa dipastikan.'));
        $this->assertFalse(AssistantController::saysNotFound('Syarat SIM baru: KTP-el asli dan fotokopi. Biaya PNBP SIM A Rp120.000.'));
        $this->assertFalse(AssistantController::saysNotFound('Layanan ini tidak dipungut biaya.'));
    }

    public function test_tool_rounds_are_capped(): void
    {
        config(['services.openai.key' => 'sk-test']);
        $call = ['choices' => [['message' => ['role' => 'assistant', 'content' => null, 'tool_calls' => [
            ['id' => 'c', 'type' => 'function', 'function' => ['name' => 'daftar_persyaratan', 'arguments' => '{}']],
        ]]]]];
        $sequence = Http::sequence();
        for ($i = 0; $i < AssistantTools::MAX_ROUNDS; $i++) {
            $sequence->push($call); // the model keeps asking for tools
        }
        Http::fake(['api.openai.com/*' => $sequence->push(['choices' => [['message' => ['role' => 'assistant', 'content' => 'Jawaban akhir']]]])]);

        $this->assertSame('Jawaban akhir', app(AssistantController::class)->answer('layanan apa saja?'));
        Http::assertSentCount(AssistantTools::MAX_ROUNDS + 1);
        $this->assertArrayNotHasKey('tools', Http::recorded()->last()[0]->data()); // last call must answer
        Http::assertSent(fn (Request $r) => ! str_contains($r['messages'][0]['content'], 'format WhatsApp')); // web channel
    }

    public function test_web_chat_gets_plain_text_from_ai_markdown(): void
    {
        $this->assertSame("Syarat\nCabai: Rp70.000\nPeta: https://x.test/m", WhatsAppFormat::plain("## Syarat\n**Cabai**: Rp70.000\n[Peta](https://x.test/m)"));
    }

    public function test_fallback_requirement_answer_is_dressed_for_whatsapp(): void
    {
        $reply = WhatsAppFormat::text("Baik, Bapak/Ibu. Persyaratan KTP-el Baru (dilayani Disdukcapil):\n1. Fotokopi KK.\nBiaya: Gratis\n"
            . "Rincian lengkap (langkah, lama proses, dasar hukum): https://x.test/a\nMungkin juga yang dimaksud: KIA — https://x.test/b\n"
            . 'Ini informasi umum; persyaratan bisa berubah, jadi mohon pastikan kembali ke Disdukcapil sebelum datang. Ada lagi?');

        $this->assertSame("Baik, Bapak/Ibu.\n\n📋 *Persyaratan KTP-el Baru*\n_Dilayani Disdukcapil_\n\n1. Fotokopi KK.\n\n💰 *Biaya:* Gratis\n"
            . "🔗 *Rincian lengkap:* https://x.test/a\n• Mungkin juga: *KIA*  https://x.test/b\n\n"
            . "_Ini informasi umum; persyaratan bisa berubah, jadi mohon pastikan kembali ke Disdukcapil sebelum datang._\n\nAda lagi?", $reply);
    }
}
