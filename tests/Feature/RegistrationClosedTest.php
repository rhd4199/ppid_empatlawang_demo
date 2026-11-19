<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationClosedTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_closed(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'password' => 'Passw0rd123',
            'password_confirmation' => 'Passw0rd123',
        ])->assertNotFound();

        $this->assertSame(0, User::count());
        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/login');
    }
}
