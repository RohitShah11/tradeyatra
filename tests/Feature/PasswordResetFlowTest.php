<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_links_to_password_reset_request(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Forgot password?')
            ->assertSee(route('password.request'), false);
    }

    public function test_user_can_request_link_and_reset_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'OldPassword123!']);

        $this->post(route('password.email'), ['email' => strtoupper($user->email)])
            ->assertSessionHas('success');

        $token = null;
        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use (&$token, $user): bool {
                $token = $notification->token;
                $mail = $notification->toMail($user);

                return $mail->subject === 'Reset your TradeYatra password'
                    && $mail->view === 'emails.auth.reset-password'
                    && $mail->viewData['resetUrl'] === route('password.reset', [
                        'token' => $token,
                        'email' => $user->email,
                    ])
                    && $mail->viewData['expireMinutes'] === 60;
            }
        );

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Choose a new password');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => strtoupper($user->email),
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->fresh()->password));
    }

    public function test_unknown_email_is_redirected_to_registration(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'unknown@example.com'])
            ->assertRedirect(route('register'))
            ->assertSessionHas('error', 'No TradeYatra account was found for this email. Please register to create your journal.')
            ->assertSessionHasInput('email', 'unknown@example.com');

        Notification::assertNothingSent();
    }
}
