<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_visit_and_campaign_are_recorded(): void
    {
        Http::fake([
            'ipwho.is/*' => Http::response([
                'success' => true,
                'country_code' => 'IN',
                'country' => 'India',
                'region' => 'Maharashtra',
                'city' => 'Mumbai',
            ]),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '49.36.10.20'])
            ->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) AppleWebKit/605.1.15 Version/17.0 Mobile/15 Safari/604.1')
            ->get('/?utm_source=instagram&utm_medium=social&utm_campaign=launch')->assertOk();

        $this->assertDatabaseHas('analytics_events', [
            'event' => 'page_view',
            'route' => 'home',
            'source' => 'instagram',
            'medium' => 'social',
            'campaign' => 'launch',
            'device_type' => 'Mobile',
            'browser' => 'Safari',
            'operating_system' => 'iOS',
            'country_code' => 'IN',
            'region' => 'Maharashtra',
            'city' => 'Mumbai',
        ]);
    }

    public function test_registration_is_attributed_to_the_visitor(): void
    {
        $this->get('/?utm_source=youtube');
        $this->post(route('register.store'), [
            'name' => 'Analytics User',
            'email' => 'analytics@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('analytics_events', [
            'event' => 'registration_completed',
            'source' => 'youtube',
            'user_id' => 1,
        ]);
    }

    public function test_admin_can_view_analytics_dashboard(): void
    {
        AnalyticsEvent::create([
            'visitor_id' => 'visitor-location-test',
            'event' => 'page_view',
            'path' => '/guides',
            'country_code' => 'IN',
            'country' => 'India',
            'region' => 'Maharashtra',
            'city' => 'Mumbai',
        ]);

        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'SecureAdmin123',
        ]);

        $this->actingAs($admin, 'admin')->get(route('admin.analytics'))
            ->assertOk()
            ->assertSee('Conversion funnel')
            ->assertSee('Unique visitors')
            ->assertSee('Shark connection rate')
            ->assertSee('Campaign performance')
            ->assertSee('Operating systems')
            ->assertSee('Visitor locations in India')
            ->assertSee('<th>Location</th>', false)
            ->assertSee('Pages viewed')
            ->assertSee('/guides')
            ->assertSee('1 view')
            ->assertSee('Last viewed')
            ->assertSee('Mumbai')
            ->assertSee('Maharashtra, India');
    }

    public function test_authenticated_html_pages_are_included_in_page_view_analytics(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/126.0')
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/126.0')
            ->get(route('profile.edit'))
            ->assertOk();

        $this->assertDatabaseHas('analytics_events', [
            'event' => 'page_view',
            'route' => 'dashboard',
            'path' => '/dashboard',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('analytics_events', [
            'event' => 'page_view',
            'route' => 'profile.edit',
            'path' => '/profile',
            'user_id' => $user->id,
        ]);
    }
}
