<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_index_lists_the_public_guides(): void
    {
        $this->get(route('resources.index'))
            ->assertOk()
            ->assertSee('Build a trading review process')
            ->assertSee('Delta Exchange Trading Journal')
            ->assertSee('Shark Exchange Trading Journal')
            ->assertSee('Crypto Trading Journal for India');
    }

    public function test_each_resource_has_unique_indexable_article_metadata(): void
    {
        foreach ([
            'resources.delta',
            'resources.shark',
            'resources.crypto-india',
        ] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('<meta name="robots" content="index,follow,max-image-preview:large">', false)
                ->assertSee('<link rel="canonical" href="'.route($routeName).'">', false)
                ->assertSee('"@type":"Article"', false)
                ->assertSee('Frequently asked questions')
                ->assertSee('Start free');
        }
    }

    public function test_sitemap_includes_every_resource_page(): void
    {
        $response = $this->get(route('sitemap'))->assertOk();

        $response->assertSee(route('resources.index'));
        foreach (['resources.delta', 'resources.shark', 'resources.crypto-india'] as $routeName) {
            $response->assertSee(route($routeName));
        }
    }

    public function test_old_resource_urls_permanently_redirect_to_clean_urls(): void
    {
        $this->get('/resources')->assertRedirect('/guides')->assertStatus(301);
        $this->get('/resources/delta-exchange-trading-journal')
            ->assertRedirect('/delta-exchange-trading-journal')
            ->assertStatus(301);
    }
}
