<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HumanCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('X-Requested-With', 'XMLHttpRequest'); // the contact form posts with jQuery ajax
    }

    private array $form = ['name' => 'Budi', 'email' => 'budi@contoh.id', 'subject' => 'Tanya', 'message' => 'Halo PPID'];

    public function test_honeypot_blocks_bots_and_people_still_get_through(): void
    {
        $this->postJson(route('contact.store'), $this->form + ['website' => 'http://spam.test'])->assertStatus(422)->assertJsonValidationErrors('human');
        $this->assertSame(0, Contact::count());

        $this->postJson(route('contact.store'), $this->form)->assertOk(); // no Turnstile keys: honeypot + throttle only
        $this->assertSame(1, Contact::count());
    }

    public function test_turnstile_is_required_when_configured(): void
    {
        config(['services.turnstile.site_key' => '1x00000000000000000000AA', 'services.turnstile.secret_key' => 'secret-test']);
        Http::fake(['challenges.cloudflare.com/*' => Http::sequence()->push(['success' => false])->push(['success' => true])]);

        $this->get(route('contact.index'))->assertOk()->assertSee('cf-turnstile', false)->assertSee('challenges.cloudflare.com/turnstile', false);
        $this->get(route('request.create'))->assertOk()->assertSee('cf-turnstile', false)->assertSee('name="website"', false);

        $this->postJson(route('contact.store'), $this->form)->assertStatus(422)->assertJsonValidationErrors('human'); // no token
        $this->postJson(route('contact.store'), $this->form + ['cf-turnstile-response' => 'bad'])->assertStatus(422); // Cloudflare: false
        $this->postJson(route('contact.store'), $this->form + ['cf-turnstile-response' => 'good'])->assertOk();
        Http::assertSent(fn (Request $r) => $r['secret'] === 'secret-test' && $r['response'] === 'good');
        $this->assertSame(1, Contact::count());
    }

    public function test_public_forms_are_throttled(): void
    {
        foreach (range(1, 5) as $i) {
            $this->postJson(route('contact.store'), $this->form)->assertOk();
        }
        $this->postJson(route('contact.store'), $this->form)->assertStatus(429);
    }
}
