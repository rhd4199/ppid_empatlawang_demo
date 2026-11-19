<?php

namespace Tests\Feature;

use App\Models\InformationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportStatsTest extends TestCase
{
    use RefreshDatabase;

    private function request(string $status, ?string $note = null, int $days = 0): void
    {
        $r = InformationRequest::create([
            'ticket_number' => 'REG-' . uniqid(), 'name' => 'Warga', 'address' => 'Tebing Tinggi',
            'email' => 'w@example.test', 'phone' => '0812', 'info_requested' => 'x', 'reason' => 'y',
            'delivery_method' => 'email', 'status' => $status, 'admin_note' => $note,
        ]);
        $r->timestamps = false;
        $r->forceFill(['created_at' => now()->subDays($days), 'updated_at' => now()])->save();
    }

    public function test_stats_are_counted_per_status(): void
    {
        $this->request('pending');
        $this->request('approved', null, 2);
        $this->request('rejected', 'Informasi dikecualikan', 4);

        $stats = $this->get('/laporan')->assertOk()->viewData('stats');

        $this->assertSame(3, $stats['total']);
        $this->assertSame(1, $stats['approved']);
        $this->assertSame(1, $stats['rejected']);
        $this->assertEquals(3.0, $stats['avg_days']);
        $this->assertSame(['Informasi dikecualikan'], $stats['rejection_reasons']->all());
    }
}
