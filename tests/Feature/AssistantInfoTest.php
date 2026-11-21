<?php

namespace Tests\Feature;

use App\Models\ContactSetting;
use App\Models\Official;
use App\Support\AssistantInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Sapa Madani fallback (no AI connected): general office info. */
class AssistantInfoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.key' => null]);
        ContactSetting::create([
            'address' => 'Jl. Contoh No. 1, Tebing Tinggi',
            'phones' => ['(0702) 111222'],
            'emails' => ['ppid@example.test'],
            'working_hours' => ['Senin - Jumat: 08:00 - 16:00', 'Sabtu - Minggu: Tutup'],
        ]);
        foreach ([['H. Bupati Contoh', 'Bupati Empat Lawang'], ['A. Wakil', 'Wakil Bupati Empat Lawang'],
            ['Sekda Contoh', 'Sekretaris Daerah Kabupaten Empat Lawang'],
            ['Kadis Kominfo', 'Kepala Dinas Komunikasi, Informatika, Statistik dan Persandian Kabupaten Empat Lawang']] as $i => [$name, $position]) {
            Official::create(['name' => $name, 'position' => $position, 'order' => $i]);
        }
    }

    private function reply(string $message): string
    {
        return $this->postJson('/sapa-madani/chat', ['message' => $message])->assertOk()->json('reply');
    }

    public function test_hours_location_and_holidays(): void
    {
        $this->assertStringContainsString('Senin - Jumat: 08:00 - 16:00', $this->reply('buka jam berapa?'));

        $where = $this->reply('kantornya di mana?');
        $this->assertStringContainsString('Jl. Contoh No. 1', $where);
        $this->assertStringContainsString(route('contact.index') . '#map-section', $where);

        $saturday = $this->reply('sabtu buka?');
        $this->assertStringContainsString('Sabtu - Minggu: Tutup', $saturday);
        $this->assertStringContainsString('hari libur nasional', $saturday);

        $this->assertStringContainsString('(0702) 111222', $this->reply('nomor wa ppid berapa'));
    }

    public function test_fee_and_processing_time(): void
    {
        foreach (['bayar gak?', 'apakah ada biaya untuk minta informasi', 'gratis kah?'] as $message) {
            $fee = $this->reply($message);
            $this->assertStringContainsString('GRATIS', $fee, $message);
            $this->assertStringContainsString(route('standard-service.index') . '#biaya', $fee);
        }
        $this->assertStringNotContainsString('GRATIS', $this->reply('minta data biaya perjalanan dinas'));

        foreach (['berapa lama prosesnya?', 'kapan dijawab', 'berapa hari permohonan selesai'] as $message) {
            $this->assertStringContainsString('10 + 7 hari kerja', $this->reply($message), $message);
        }
    }

    public function test_officials_ppid_and_visit(): void
    {
        $ppid = $this->reply('siapa ppid nya?');
        $this->assertStringContainsString('Kepala Dinas Komunikasi dan Informatika (saat ini Kadis Kominfo)', $ppid);
        $this->assertStringContainsString('Sekretaris Daerah (saat ini Sekda Contoh)', $ppid);

        $this->assertStringContainsString('A. Wakil', $this->reply('siapa wakil bupati?'));
        $this->assertStringNotContainsString('A. Wakil', $this->reply('siapa bupati empat lawang'));
        $this->assertStringContainsString('H. Bupati Contoh', $this->reply('siapa bupati empat lawang'));
        $this->assertStringContainsString(route('officials.index'), $this->reply('daftar pejabat'));

        $visit = $this->reply('bisa datang langsung ke kantor?');
        $this->assertStringContainsString('Jl. Contoh No. 1', $visit);
        $this->assertStringContainsString('08:00 - 16:00', $visit);
        $this->assertStringContainsString('#permohonan', $visit);
    }

    public function test_fixed_texts_match_the_standar_layanan_page(): void
    {
        $page = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($this->get(route('standard-service.index'))->assertOk()->getContent())));
        foreach ([AssistantInfo::FEE, AssistantInfo::DURATION, AssistantInfo::HOLIDAY] as $text) {
            $this->assertStringContainsString($text, $page);
        }
    }
}
