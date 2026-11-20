<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MailConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_test_command_sends_email(): void
    {
        $this->artisan('mail:test', ['to' => 'a@b.c'])
            ->expectsOutputToContain('Terkirim ke a@b.c')
            ->assertExitCode(0);

        $sent = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
        $this->assertSame('a@b.c', $sent[0]->getOriginalMessage()->getTo()[0]->getAddress());
    }

    public function test_mail_test_command_rejects_invalid_address(): void
    {
        $this->artisan('mail:test', ['to' => 'bukan-email'])->assertExitCode(1);
    }

    public function test_password_reset_link_is_sent(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/password/email', ['email' => $user->email])->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
