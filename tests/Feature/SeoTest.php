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
            ->assertSee(route('founder'))
            ->assertSee(route('support-fund.index'))
            ->assertDontSee(route('dashboard'));
    }

    public function test_founder_page_is_public_indexable_and_transparent(): void
    {
        $this->get(route('founder'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('founder').'">', false)
            ->assertSee('Rohit Shah, founder and builder of TradeYatra')
            ->assertSee('I’m a developer and trader')
            ->assertSee('Why I started TradeYatra')
            ->assertSee('Building the journal I wanted to use.')
            ->assertSee('Independent builder')
            ->assertSee('"@type":"Person"', false)
            ->assertSee('trust should begin with transparency', false)
            ->assertSee('images/founder/rohit-shah-original.jpeg');
    }

    public function test_robots_file_advertises_sitemap_and_blocks_private_areas(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Sitemap: '.route('sitemap'))
            ->assertSee('Disallow: /dashboard')
            ->assertSee('Disallow: /admin/')
            ->assertDontSee('Disallow: /founder');
    }

    public function test_homepage_has_canonical_social_and_structured_metadata(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('<meta name="application-name" content="TradeYatra">', false)
            ->assertSee('<meta property="og:site_name" content="TradeYatra">', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('Can TradeYatra place trades or withdraw funds?')
            ->assertSee('Does Yatra AI provide trading signals?')
            ->assertSee('<details class="faq-item"><summary>', false)
            ->assertDontSee('<details class="faq-item" open>', false)
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
            ->assertSee('window.tradeYatraPublicNavigationController', false)
            ->assertDontSee('id="reports"', false)
            ->assertDontSee('#reports', false)
            ->assertSee('Everything needed for a focused trading review.')
            ->assertSee('See the journal in action.')
            ->assertDontSee('One workspace for the full review cycle.')
            ->assertDontSee('01 · Command center')
            ->assertDontSee('id="workflow"', false)
            ->assertDontSee('Review in four simple steps.');

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
