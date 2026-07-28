<?php

namespace Tests\Feature;

use Tests\TestCase;

class SupportedBrokerHomePageTest extends TestCase
{
    public function test_home_page_clearly_identifies_supported_broker_integrations(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Shark + Delta, unified')
            ->assertSee('Live journal sync')
            ->assertSee('Two broker histories flow into one private performance journal.')
            ->assertSee('Encrypted credentials')
            ->assertSee('5-minute auto sync')
            ->assertSee('TradeYatra unified data matrix')
            ->assertSee('images/branding/tradeyatra-icon-v2.png')
            ->assertSee('Shark Exchange')
            ->assertSee('Delta Exchange India')
            ->assertSee('Supported now: Shark Exchange and Delta Exchange India')
            ->assertSee('Choose your exchange')
            ->assertSee('TradeYatra is a journaling and analytics platform');
    }
}
