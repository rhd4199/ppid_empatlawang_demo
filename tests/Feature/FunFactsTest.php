<?php

namespace Tests\Feature;

use App\Support\AssistantTools;
use App\Support\FunFacts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FunFactsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_sourced_fact_even_when_live_sources_are_down(): void
    {
        Http::fake(fn () => Http::response('down', 502)); // portal and price panel unavailable
        $all = array_column(FunFacts::FIXED, 0);
        foreach (range(1, 5) as $i) {
            $f = AssistantTools::run('fakta_unik', []);
            $this->assertContains($f['fakta'], $all); // only fixed, sourced facts remain
            $this->assertNotSame('', $f['sumber']);
        }
    }
}
