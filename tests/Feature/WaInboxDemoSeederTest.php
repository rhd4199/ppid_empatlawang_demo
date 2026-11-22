<?php

namespace Tests\Feature;

use App\Models\WaConversation;
use App\Models\WaMessage;
use Database\Seeders\WaInboxDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaInboxDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_chats_are_safe_dated_and_rerunnable(): void
    {
        $real = WaConversation::create(['phone' => '6281234567890', 'status' => 'new']);

        $this->seed(WaInboxDemoSeeder::class);
        $this->seed(WaInboxDemoSeeder::class); // re-run replaces, does not double

        $demo = WaConversation::where('phone', 'like', '628000%');
        $this->assertSame(140, $demo->count());
        $this->assertNotNull($real->fresh()); // non-demo rows untouched
        $this->assertSame(0, WaMessage::where('body', 'like', '%{%')->count()); // placeholders filled
        $this->assertTrue(WaMessage::min('sent_at') >= '2026-02-28 17:00:00'); // 1 March WIB
        $this->assertTrue(WaMessage::max('sent_at') <= now()->toDateTimeString());
        $this->assertSame(0, WaConversation::whereColumn('last_message_at', '<', 'created_at')->count());
    }
}
