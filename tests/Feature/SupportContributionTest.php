<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SupportContribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportContributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_support_page_displays_bhim_qr_and_accepts_a_submission(): void
    {
        $this->get(route('support-fund.index'))
            ->assertOk()
            ->assertSee('support-tradeyatra-bhim-qr.jpeg')
            ->assertSee('Submit payment details');

        $this->post(route('support-fund.store'), [
            'contributor_name' => 'Rohit Supporter',
            'email' => 'rohit@example.com',
            'phone' => '+91 98765 43210',
            'amount' => 501,
            'transaction_reference' => 'UPI123456789',
            'message' => 'Keep building.',
            'show_publicly' => '1',
        ])->assertRedirect(route('support-fund.index'));

        $this->assertDatabaseHas('support_contributions', [
            'transaction_reference' => 'UPI123456789',
            'phone' => '+91 98765 43210',
            'status' => 'pending',
        ]);
        $this->get(route('support-fund.index'))->assertDontSee('Rohit Supporter');
    }

    public function test_logged_in_user_sees_dashboard_instead_of_guest_actions(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('support-fund.index'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertDontSee('>Login<', false)
            ->assertDontSee('Start Free');
    }

    public function test_verified_contribution_is_public_and_receives_highest_badge(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $contribution = SupportContribution::query()->create([
            'contributor_name' => 'Top Supporter',
            'amount' => 1000,
            'transaction_reference' => 'UPI987654321',
            'show_publicly' => true,
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.contributions.update', $contribution), [
                'status' => 'verified',
                'admin_notes' => 'Matched in BHIM history.',
            ])->assertSessionHas('success');

        $this->get(route('support-fund.index'))
            ->assertOk()
            ->assertSee('Top Supporter')
            ->assertSee('Highest contributor');
        $this->assertNotNull($contribution->fresh()->verified_at);
        $this->assertSame($admin->id, $contribution->fresh()->verified_by);
    }

    public function test_private_verified_contributor_name_is_not_disclosed(): void
    {
        SupportContribution::query()->create([
            'contributor_name' => 'Private Person',
            'amount' => 250,
            'transaction_reference' => 'PRIVATE12345',
            'show_publicly' => false,
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->get(route('support-fund.index'))
            ->assertSee('Anonymous')
            ->assertDontSee('Private Person');
    }

    public function test_admin_contribution_page_requires_admin_authentication(): void
    {
        $this->get(route('admin.contributions.index'))->assertRedirect(route('admin.login'));
    }
}
