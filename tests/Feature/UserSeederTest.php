<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_admin_is_seeded_once_and_can_log_in(): void
    {
        config(['services.admin' => ['email' => null, 'password' => null]]);
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertSame(2, User::count()); // old Administrator kept, no duplicate
        $this->post('/login', ['email' => 'rezaharjadinata4199@gmail.com', 'password' => 'qwe123'])->assertRedirect();
        $this->assertAuthenticated();
        $this->get('/admin')->assertOk();
    }

    public function test_production_needs_admin_password_from_env(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['services.admin' => ['email' => null, 'password' => null]]);
        (new UserSeeder)->run(); // db:seed would ask to confirm in production
        $this->assertSame(0, User::where('email', 'rezaharjadinata4199@gmail.com')->count());

        config(['services.admin' => ['email' => 'kepala@empatlawangkab.go.id', 'password' => 'Panjang-Dan-Acak-123']]);
        (new UserSeeder)->run(); // db:seed would ask to confirm in production
        $this->assertTrue(\Hash::check('Panjang-Dan-Acak-123', User::where('email', 'kepala@empatlawangkab.go.id')->value('password')));
    }
}
