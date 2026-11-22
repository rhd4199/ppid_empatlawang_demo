<?php

namespace Tests\Feature;

use App\Models\ContactSetting;
use App\Models\SppgUnit;
use App\Support\AssistantTools;
use Database\Seeders\SppgUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Sapa Madani on SPPG/MBG: the data_sppg tool and the answer without AI. */
class SppgAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.key' => null]);
        ContactSetting::create(['address' => 'Jl. Kantor PPID No. 1', 'phones' => ['(0702) 111222']]);
        $this->seed(SppgUnitSeeder::class);
    }

    private function reply(string $message): string
    {
        return $this->postJson('/sapa-madani/chat', ['message' => $message])->assertOk()->json('reply');
    }

    public function test_tool_summary_filters_and_published_only(): void
    {
        $this->assertContains('data_sppg', array_column(array_column(AssistantTools::definitions(), 'function'), 'name'));

        $all = AssistantTools::run('data_sppg', []);
        $this->assertSame(23, $all['ringkasan']['total_sppg']);
        $this->assertSame(['Operasional' => 23], $all['ringkasan']['per_status']);
        $this->assertSame(8, $all['ringkasan']['per_kecamatan']['Tebing Tinggi']);
        $this->assertCount(23, $all['hasil']);
        $this->assertNotContains('SPPG Kota Pagar Alam Dempo Tengah Karang Dalo', array_column($all['hasil'], 'nama')); // hidden row
        $this->assertSame(route('sppg.index'), $all['halaman']);

        $ulu = AssistantTools::run('data_sppg', ['kecamatan' => 'ulu musi']);
        $this->assertSame(['SPPG Empat Lawang Ulu Musi Muara Kalangan'], array_column($ulu['hasil'], 'nama'));
        $this->assertSame(route('sppg.index', ['kecamatan' => 'Ulu Musi']), $ulu['halaman']);

        $school = AssistantTools::run('data_sppg', ['kata_kunci' => 'SD Negeri 7 Muara Pinang']);
        $timbuk = collect($school['hasil'])->firstWhere('nama', 'SPPG Empat Lawang Muara Pinang Muara Timbuk');
        $this->assertContains('SD Negeri 7 Muara Pinang', $timbuk['instansi_cocok']);

        $this->assertIsString(AssistantTools::run('data_sppg', ['kecamatan' => 'Antah'])['hasil']);

        SppgUnit::query()->update(['is_published' => false]);
        $this->assertSame(0, AssistantTools::run('data_sppg', [])['ringkasan']['total_sppg']);
    }

    public function test_fallback_answers_before_office_address(): void
    {
        $lokasi = $this->reply('dimana lokasi sppg ulu musi');
        $this->assertStringContainsString('Kecamatan Ulu Musi ada 1 SPPG', $lokasi);
        $this->assertStringContainsString('Desa Muara Kalangan', $lokasi);
        $this->assertStringNotContainsString('Jl. Kantor PPID', $lokasi); // not hijacked by the PPID address answer

        $pendopo = $this->reply('sppg di pendopo ada berapa?');
        $this->assertStringContainsString('Kecamatan Pendopo ada 2 SPPG', $pendopo);
        $this->assertStringNotContainsString('Pendopo Barat', $pendopo);
        $this->assertStringContainsString('Kecamatan Pendopo Barat ada 2 SPPG', $this->reply('SPPG Pendopo Barat'));

        $dapur = $this->reply('dapur mbg');
        $this->assertStringContainsString('tercatat 23 SPPG', $dapur);
        $this->assertStringContainsString('Tebing Tinggi 8', $dapur);
        $this->assertStringContainsString(route('sppg.index'), $dapur);
        $this->assertStringContainsString('Radar MBG Badan Gizi Nasional', $dapur);

        // other questions still go to the office address
        $this->assertStringContainsString('Jl. Kantor PPID', $this->reply('dimana alamat kantor ppid?'));
    }

    public function test_fallback_without_data(): void
    {
        SppgUnit::query()->delete();
        $this->assertStringContainsString('belum tersedia', $this->reply('ada berapa dapur mbg?'));
    }
}
