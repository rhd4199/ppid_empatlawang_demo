<?php

namespace Tests\Feature;

use App\Models\ServiceRequirement;
use App\Support\RequirementFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantRequirementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.key' => null]); // a real key in .env must never reach tests

        // small slice of ServiceRequirementSeeder data
        $rows = [
            ['Ganti Alamat KTP-el (Pindah dalam Kabupaten Empat Lawang)', 'ganti-alamat-ktp-pindah-dalam-kabupaten', 'kependudukan', 'Disdukcapil Kabupaten Empat Lawang', 'ganti alamat, pindah rumah, pindah domisili, alamat ktp', ['Fotokopi Kartu Keluarga (KK).', 'KTP-el lama.']],
            ['KTP-el Hilang, Rusak atau Perubahan Data', 'ktp-el-hilang-rusak', 'kependudukan', 'Disdukcapil Kabupaten Empat Lawang', 'ktp hilang, ktp rusak, e-ktp hilang', ['Surat keterangan kehilangan dari kepolisian.']],
            ['Kartu Identitas Anak (KIA)', 'kia', 'kependudukan', 'Disdukcapil Kabupaten Empat Lawang', 'kia, ktp anak, kia hilang', ['Fotokopi akta kelahiran anak.']],
            ['SPP-IRT / PIRT (Izin Produksi Pangan Industri Rumah Tangga)', 'spp-irt-pirt', 'perizinan_usaha', 'DPMPTSP dan Dinas Kesehatan', 'pirt, p-irt, spp-irt, izin makanan rumahan', ['NIB.', 'Rancangan label.']],
            ['Balik Nama Kendaraan Bekas (Motor/Mobil)', 'balik-nama-kendaraan', 'kendaraan', 'Samsat Kabupaten Empat Lawang', 'balik nama, bbn, bbnkb, motor bekas, mobil bekas', ['KTP-el pemilik baru.', 'BPKB asli dan STNK asli.', 'Kuitansi jual beli.', 'Cek fisik.', 'Surat kuasa.', 'Pengantar mutasi.']],
            ['Balik Nama Sertifikat Tanah karena Jual Beli', 'balik-nama-sertifikat-jual-beli', 'pertanahan', 'Kantor Pertanahan', 'balik nama sertifikat, ajb, ppat', ['Akta Jual Beli dari PPAT.']],
            ['Uji Berkala Kendaraan (KIR)', 'uji-kir', 'kendaraan', 'Dinas Perhubungan', 'kir, uji kir, tes emisi, tes rem', ['Buku uji lama.']],
            ['KTP-el Baru (Pertama Kali)', 'ktp-el-baru', 'kependudukan', 'Disdukcapil Kabupaten Empat Lawang', 'ktp baru, rekam ktp', ['Telah berusia 17 tahun.']],
            ['Draft SIM Rahasia', 'draft-sim', 'kendaraan', 'Satpas', 'sim', ['Rahasia.'], false],
        ];
        foreach ($rows as $r) {
            ServiceRequirement::create([
                'title' => $r[0], 'slug' => $r[1], 'category' => $r[2], 'agency' => $r[3], 'keywords' => $r[4],
                'requirements' => '<ol><li>' . implode('</li><li>', $r[5]) . '</li></ol>',
                'cost' => 'Gratis', 'is_published' => $r[6] ?? true,
            ]);
        }
    }

    private function ask(string $message): string
    {
        return $this->postJson('/sapa-madani/chat', ['message' => $message])->assertOk()->json('reply');
    }

    public function test_fallback_answers_from_requirements_with_link_and_agency(): void
    {
        $reply = $this->ask('Syarat ganti alamat KTP apa saja?');
        $this->assertStringContainsString('Persyaratan Ganti Alamat KTP-el', $reply);
        $this->assertStringContainsString('1. Fotokopi Kartu Keluarga (KK).', $reply);
        $this->assertStringContainsString('Disdukcapil Kabupaten Empat Lawang', $reply);
        $this->assertStringContainsString(route('service-requirements.show', 'ganti-alamat-ktp-pindah-dalam-kabupaten'), $reply);
        $this->assertStringContainsString('pastikan kembali', $reply);

        $this->assertStringContainsString('/persyaratan/spp-irt-pirt', $this->ask('cara bikin PIRT gimana?'));
        $this->assertStringContainsString('/persyaratan/ktp-el-hilang-rusak', $this->ask('ktp hilang'));

        $motor = $this->ask('syarat balik nama motor');
        $this->assertStringContainsString('/persyaratan/balik-nama-kendaraan', $motor);
        $this->assertStringContainsString('…dan 1 syarat lainnya.', $motor);
        $this->assertStringNotContainsString('balik-nama-sertifikat', $motor);
    }

    public function test_fallback_unknown_or_draft_service_points_to_list_and_agency(): void
    {
        $reply = $this->ask('syarat bikin sim');
        $this->assertStringContainsString('belum ada di daftar kami', $reply);
        $this->assertStringContainsString(route('service-requirements.index'), $reply);
        $this->assertStringNotContainsString('Rahasia', $reply);
    }

    public function test_chat_suffixes_and_test_messages(): void
    {
        // "ktpku" = ktp, "mengurusnya" = mengurus
        $this->assertStringContainsString('KTP-el Hilang', $this->ask('Halo ktpku hilang bagaimana cara mengurusnya'));
        // A bare "tes" is a greeting, not "Uji KIR" (whose keywords mention tes emisi).
        foreach (['Tes', 'tes', 'test'] as $text) {
            $this->assertStringNotContainsString('KIR', $this->ask($text));
        }
        $this->assertStringContainsString('Uji Berkala Kendaraan', $this->ask('syarat uji kir'));
        // Palembang dialect + "bikin" = the new KTP, not Ganti Alamat KTP-el.
        $this->assertStringContainsString('Persyaratan KTP-el Baru', $this->ask('Cara bikin KTP cak mano'));
    }

    public function test_unsure_or_ambiguous_match_is_confirmed_first(): void
    {
        $history = fn (string $q, string $a) => [['role' => 'user', 'content' => $q], ['role' => 'assistant', 'content' => $a]];

        // Hit only through keywords ("izin makanan rumahan" → PIRT): ask, "ya" answers.
        $ask = $this->ask('cara bikin izin makanan rumahan');
        $this->assertStringStartsWith(RequirementFinder::CONFIRM, $ask);
        $this->assertStringContainsString('1. SPP-IRT / PIRT', $ask);
        $this->assertStringNotContainsString('NIB.', $ask);
        $yes = $this->postJson('/sapa-madani/chat', ['message' => 'iya', 'history' => $history('cara bikin izin makanan rumahan', $ask)])->json('reply');
        $this->assertStringContainsString('Persyaratan SPP-IRT / PIRT', $yes);
        $this->assertStringContainsString('NIB.', $yes);

        // Several services fit equally: numbered choice.
        $ask = $this->ask('syarat ktp');
        $this->assertStringContainsString('Balas angkanya', $ask);
        preg_match('/^(\d)\. KTP-el Hilang/m', $ask, $m);
        $pick = $this->postJson('/sapa-madani/chat', ['message' => $m[1], 'history' => $history('syarat ktp', $ask)])->json('reply');
        $this->assertStringContainsString('Persyaratan KTP-el Hilang', $pick);

        // Clear questions are answered at once, ending with a follow-up question.
        $this->assertStringEndsWith('Apakah Bapak/Ibu juga perlu persyaratan layanan lain?', $this->ask('syarat ktp hilang'));

        // With steps: "mau tahu langkahnya?" → "ya" gives the steps.
        ServiceRequirement::where('slug', 'kia')->update(['steps' => '<ol><li>Datang ke Disdukcapil.</li><li>Ambil KIA.</li></ol>']);
        $kia = $this->ask('syarat kia');
        $this->assertStringEndsWith(RequirementFinder::STEPS_ASK . ' Kartu Identitas Anak (KIA)?', $kia);
        $steps = $this->postJson('/sapa-madani/chat', ['message' => 'ya', 'history' => $history('syarat kia', $kia)])->json('reply');
        $this->assertStringContainsString("Langkah pengurusan Kartu Identitas Anak (KIA):
1. Datang ke Disdukcapil.
2. Ambil KIA.", $steps);
    }

    public function test_ppid_questions_are_not_hijacked(): void
    {
        $this->assertStringContainsString(route('request.create'), $this->ask('cara mengajukan permohonan informasi'));
        $this->assertStringNotContainsString('/persyaratan/', $this->ask('alamat kantor ppid di mana?'));
        $this->assertStringNotContainsString('/persyaratan/', $this->ask('halo'));
    }

    public function test_openai_prompt_holds_only_matching_requirements(): void
    {
        config(['services.openai.key' => 'sk-test', 'services.openai.model' => 'gpt-4o-mini']);
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'ok']]]])]);

        $this->postJson('/sapa-madani/chat', ['message' => 'cara bikin pirt'])->assertOk();
        Http::assertSent(function ($req) {
            $system = $req['messages'][0]['content'];

            return str_contains($system, 'SPP-IRT / PIRT')
                && str_contains($system, route('service-requirements.show', 'spp-irt-pirt'))
                && str_contains($system, 'Persyaratan: NIB.; Rancangan label.')
                && ! str_contains($system, 'Balik Nama Kendaraan')
                && ! str_contains($system, 'Draft SIM Rahasia');
        });

        $this->postJson('/sapa-madani/chat', ['message' => 'syarat bikin paspor'])->assertOk();
        Http::assertSent(fn ($req) => str_contains($req['messages'][0]['content'], 'Jangan mengarang persyaratan'));
    }
}
