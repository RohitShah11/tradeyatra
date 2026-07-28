<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_helpful_placeholders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('placeholder="you@example.com"', false)
            ->assertSee('placeholder="Enter your password"', false)
            ->assertSee('autocomplete="username"', false)
            ->assertSee('Keep me signed in')
            ->assertSee('Logging out will end it.');
    }

    public function test_remember_me_creates_persistent_login_cookie(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertCookie(Auth::guard()->getRecallerName());
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_normal_login_does_not_create_remember_cookie(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertCookieMissing(Auth::guard()->getRecallerName());
        $this->assertAuthenticatedAs($user);
    }
}
