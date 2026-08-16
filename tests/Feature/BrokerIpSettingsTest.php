<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerIpSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_settings_show_configured_ipv4_and_ipv6_whitelist_guidance(): void
    {
        config()->set('services.broker_sync.ipv4', '203.0.113.10');
        config()->set('services.broker_sync.ipv6', '2001:db8::10');

        $user = User::factory()->create();

        foreach (['shark.settings', 'delta.settings'] as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('Server IP whitelist')
                ->assertSee('203.0.113.10')
                ->assertSee('2001:db8::10')
                ->assertSee('Copy IPv4')
                ->assertSee('Copy IPv6')
                ->assertSee(route('broker.guide'), false)
                ->assertDontSee('Need help connecting');
        }
    }

    public function test_broker_settings_explain_when_ipv6_is_not_used(): void
    {
        config()->set('services.broker_sync.ipv4', '203.0.113.10');
        config()->set('services.broker_sync.ipv6');

        $user = User::factory()->create();

        foreach (['shark.settings', 'delta.settings'] as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('The sync server does not use IPv6.');
        }
    }

    public function test_public_guide_has_full_steps_without_disclosing_server_addresses(): void
    {
        config()->set('services.broker_sync.ipv4', '203.0.113.10');
        config()->set('services.broker_sync.ipv6', '2001:db8::10');

        $response = $this->get(route('broker.guide'))
            ->assertOk()
            ->assertSee('Apply IP restriction when available')
            ->assertSee('Add the TradeYatra server addresses')
            ->assertSee('Run and verify the first sync')
            ->assertSee('Run the first Delta sync')
            ->assertSee('https://www.youtube-nocookie.com/embed/8z0kvif4Hlc', false)
            ->assertSee('Watch directly on YouTube')
            ->assertDontSee('203.0.113.10')
            ->assertDontSee('2001:db8::10');

        $this->assertStringContainsString(
            "frame-src 'self' https://www.youtube-nocookie.com",
            (string) $response->headers->get('Content-Security-Policy'),
        );
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'https://www.youtube-nocookie.com/embed/8z0kvif4Hlc'),
        );
    }
}
