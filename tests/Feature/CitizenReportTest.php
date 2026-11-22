<?php

namespace Tests\Feature;

use App\Models\CitizenReport;
use App\Models\User;
use App\Models\WaConversation;
use App\Support\ReportDesk;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CitizenReportTest extends TestCase
{
    use RefreshDatabase;

    private function toolCall(string $name, array $args): array
    {
        return ['choices' => [['message' => ['role' => 'assistant', 'content' => null, 'tool_calls' => [
            ['id' => 'c1', 'type' => 'function', 'function' => ['name' => $name, 'arguments' => json_encode($args)]],
        ]]]]];
    }

    public function test_whatsapp_report_is_recorded_flagged_for_officer_and_followed_up_in_admin(): void
    {
        config(['services.openai.key' => 'sk-test']);
        WhatsAppGateway::save(['provider' => 'fonnte', 'enabled' => true, 'auto_reply' => true], 'tok-123');
        Http::fake([
            'api.openai.com/*' => Http::sequence()
                ->push($this->toolCall('catat_laporan', ['kategori' => 'infrastruktur', 'uraian' => 'Jalan berlubang besar, motor sering jatuh',
                    'lokasi' => 'Jl. Lintas Sumatera dekat SDN 1', 'kecamatan' => 'Tebing Tinggi', 'desa' => 'Pasar Tebing Tinggi', 'waktu_kejadian' => 'sejak 2 minggu']))
                ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => 'Laporan sudah dicatat dengan kode LW.']]]]),
            'api.fonnte.com/*' => Http::response(['status' => true]),
        ]);

        $url = route('api.whatsapp.webhook', ['secret' => WhatsAppGateway::webhookSecret()]);
        $this->post($url, ['sender' => '081234567890', 'name' => 'Budi', 'message' => 'Jalan di depan SDN 1 rusak parah', 'inboxid' => 'm1'])->assertOk();

        $report = CitizenReport::sole();
        $this->assertSame([now('Asia/Jakarta')->year . '', 'whatsapp', 'infrastruktur', '+6281234567890', 'Budi', 'baru'],
            [substr($report->code, 3, 4), $report->channel, $report->category, $report->contact, $report->reporter_name, $report->status]);
        $c = WaConversation::sole();
        $this->assertSame(['new', 'aduan'], [$c->status, $c->label]);
        $this->assertStringStartsWith("Laporan warga {$report->code}", $c->handoff_reason);
        // the prompt asks for details first, records, then other channels
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'openai') && str_contains($r['messages'][0]['content'] ?? '', 'TINDAK LANJUT UTAMA adalah mencatat laporannya')
            && str_contains($r['messages'][0]['content'], 'Waktu sekarang:'));

        $admin = User::factory()->create();
        $this->actingAs($admin)->get(route('admin.citizen-reports.index'))->assertOk()->assertSee($report->code)->assertSee('Jalan berlubang besar')->assertSee('Buka chat');
        $this->put(route('admin.citizen-reports.update', $report), ['status' => 'diteruskan', 'officer_notes' => 'Ke Dinas PUPR'])->assertSessionHas('success');
        $this->assertSame(['diteruskan', 'Ke Dinas PUPR'], [$report->fresh()->status, $report->fresh()->officer_notes]);
        $this->put(route('admin.citizen-reports.update', $report), ['status' => 'x'])->assertSessionHasErrors('status');
    }

    public function test_record_needs_details_numbers_per_year_and_updates_same_report(): void
    {
        $this->assertArrayHasKey('error', ReportDesk::record(['kategori' => 'infrastruktur', 'uraian' => 'rusak', 'lokasi' => ''], null, 'web'));

        $first = ReportDesk::record(['kategori' => 'pungli', 'uraian' => 'Diminta bayar Rp50.000 saat urus surat', 'lokasi' => 'Kantor desa', 'kontak' => '0812-3456-7890'], null, 'web');
        $second = ReportDesk::record(['kategori' => 'nggak-ada', 'uraian' => 'Sampah menumpuk di pinggir jalan', 'lokasi' => 'Dusun II'], null, 'web');
        $this->assertSame(now('Asia/Jakarta')->year . '-0001', substr($first['kode'], 3));
        $this->assertSame(now('Asia/Jakarta')->year . '-0002', substr($second['kode'], 3));
        $this->assertSame(['6281234567890', 'lainnya'], [CitizenReport::where('code', $first['kode'])->value('contact'), CitizenReport::where('code', $second['kode'])->value('category')]);

        ReportDesk::record(['kategori' => 'pungli', 'uraian' => 'Diminta bayar Rp50.000, petugasnya bernama X', 'lokasi' => 'Kantor desa Y', 'kode' => $first['kode']], null, 'web');
        $this->assertSame(2, CitizenReport::count());
        $this->assertSame('Kantor desa Y', CitizenReport::where('code', $first['kode'])->value('location'));
    }

    public function test_web_chat_offers_the_report_tool(): void
    {
        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'ok']]]])]);
        $this->postJson('/sapa-madani/chat', ['message' => 'mau lapor'])->assertOk();
        Http::assertSent(fn (Request $r) => in_array('catat_laporan', array_column(array_column($r['tools'], 'function'), 'name'), true));
    }
}
