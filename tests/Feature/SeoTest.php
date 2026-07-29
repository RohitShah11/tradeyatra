<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_contains_only_public_canonical_pages(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home'))
            ->assertSee(route('broker.guide'))
            ->assertSee(route('support-fund.index'))
            ->assertDontSee(route('dashboard'));
    }

    public function test_robots_file_advertises_sitemap_and_blocks_private_areas(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Sitemap: '.route('sitemap'))
            ->assertSee('Disallow: /dashboard')
            ->assertSee('Disallow: /admin/');
    }

    public function test_homepage_has_canonical_social_and_structured_metadata(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('<meta name="application-name" content="TradeYatra">', false)
            ->assertSee('<meta property="og:site_name" content="TradeYatra">', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"name":"TradeYatra"', false)
            ->assertSee('"alternateName":"Trade Yatra"', false)
            ->assertSee('"@type":"SoftwareApplication"', false);
    }

    public function test_public_pages_use_livewire_navigation_without_intercepting_section_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('wire:navigate.hover', false)
            ->assertSee('Livewire Scripts', false)
            ->assertSee('window.tradeYatraPublicNavigationController', false);

        foreach ([route('broker.guide'), route('legal.risk')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('wire:navigate.hover', false)
                ->assertSee('window.tradeYatraPublicNavigationController', false);
        }

        $this->get(route('home'))
            ->assertSee('href="'.route('home').'#faq">FAQ', false)
            ->assertSee('aria-label="Mobile navigation"', false)
            ->assertDontSee('href="'.route('home').'#faq" wire:navigate', false);
    }

    public function test_authenticated_pages_are_marked_noindex(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,nofollow,noarchive">', false);
    }
}
