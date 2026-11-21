<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\LarikaStatus;
use App\Support\WhatsAppGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LarikaStatusTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'lk_test_status_key';

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.larika.key' => self::KEY, 'services.larika.url' => 'https://gateway.larika.id']);
        WhatsAppGateway::save(['enabled' => true, 'auto_reply' => false, 'provider' => 'larika', 'api_url' => '', 'sender' => ''], null);
        $this->actingAs(User::factory()->create());
    }

    private function page()
    {
        return $this->get(route('admin.whatsapp.index'))->assertOk()->assertDontSee(self::KEY);
    }

    public function test_missing_key(): void
    {
        config(['services.larika.key' => '']);

        $this->page()->assertSee('data-wa-status="missing"', false)->assertSee('LARIKA_KEY belum diisi');
        Http::assertNothingSent();
    }

    public function test_rejected_key(): void
    {
        Http::fake(['gateway.larika.id/v1/number' => Http::response(['error' => 'unauthorized'], 401)]);

        $this->page()->assertSee('data-wa-status="rejected"', false)->assertSee('Key ditolak');
    }

    public function test_connected_with_poller_and_last_event(): void
    {
        Http::fake(['gateway.larika.id/v1/number' => Http::response([
            'name' => 'WA SAPA MADANI', 'status' => 'ONLINE', 'phone' => '628972007121', 'profileName' => 'Sapa Madani',
            'usage' => ['capUsed' => 3, 'dailyCap' => 200], 'health' => ['risk' => 'LOW'],
        ])]);
        Cache::forever(LarikaStatus::POLL_KEY, now()->subSeconds(10)->toIso8601String());
        DB::table('larika_events')->insert(['event_id' => 'ev1', 'event' => 'message.incoming', 'handled_at' => now()]);

        $this->page()->assertSee('data-wa-status="ok"', false)->assertSee('Tersambung')
            ->assertSee('+628972007121')->assertSee('3 / 200')->assertSee('berjalan')->assertDontSee('belum pernah');
        Http::assertSent(fn ($r) => $r->url() === 'https://gateway.larika.id/v1/number' && $r->header('Authorization') === ['Bearer ' . self::KEY]);
    }

    public function test_offline_number_and_stale_poller(): void
    {
        Http::fake(['gateway.larika.id/v1/number' => Http::response(['status' => 'QR', 'phone' => null])]);
        Cache::forever(LarikaStatus::POLL_KEY, now()->subMinutes(10)->toIso8601String());

        $this->page()->assertSee('Nomor belum online')->assertSee('tidak berjalan');
    }
}
