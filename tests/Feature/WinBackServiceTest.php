<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Reward;
use App\Services\WinBackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WinBackServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_close_and_inactive_customer_is_a_win_back_candidate(): void
    {
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        $customer = Customer::create([
            'name' => 'Slipping Away Sam',
            'email' => 'sam@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(20),
        ]);

        $candidates = app(WinBackService::class)->winBackCandidates();

        $this->assertTrue($candidates->contains('id', $customer->id));
    }

    public function test_a_close_but_active_customer_is_not_a_win_back_candidate(): void
    {
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        $customer = Customer::create([
            'name' => 'Regular Rita',
            'email' => 'rita@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(2),
        ]);

        $candidates = app(WinBackService::class)->winBackCandidates();

        $this->assertFalse($candidates->contains('id', $customer->id));
    }

    public function test_an_inactive_customer_far_from_a_reward_is_not_a_win_back_candidate(): void
    {
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        $customer = Customer::create([
            'name' => 'Distant Dan',
            'email' => 'dan@example.com',
            'points_balance' => 10,
            'last_activity_at' => now()->subDays(40),
        ]);

        $candidates = app(WinBackService::class)->winBackCandidates();

        $this->assertFalse($candidates->contains('id', $customer->id));
    }

    public function test_a_customer_exactly_at_both_thresholds_is_a_win_back_candidate(): void
    {
        $reward = Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        $customer = Customer::create([
            'name' => 'Boundary Bea',
            'email' => 'bea@example.com',
            'points_balance' => (int) round($reward->points_required * config('loyalty.proximity_threshold')),
            'last_activity_at' => now()->subDays(config('loyalty.inactivity_days')),
        ]);

        $candidates = app(WinBackService::class)->winBackCandidates();

        $this->assertTrue($candidates->contains('id', $customer->id));
    }

    public function test_a_customer_just_below_the_proximity_threshold_is_not_a_win_back_candidate(): void
    {
        $reward = Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        $customer = Customer::create([
            'name' => 'Almost Amy',
            'email' => 'amy@example.com',
            'points_balance' => (int) round($reward->points_required * config('loyalty.proximity_threshold')) - 1,
            'last_activity_at' => now()->subDays(config('loyalty.inactivity_days') + 10),
        ]);

        $candidates = app(WinBackService::class)->winBackCandidates();

        $this->assertFalse($candidates->contains('id', $customer->id));
    }

    public function test_a_customer_just_below_the_inactivity_threshold_is_not_a_win_back_candidate(): void
    {
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        $customer = Customer::create([
            'name' => 'Recent Ravi',
            'email' => 'ravi@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(config('loyalty.inactivity_days') - 1),
        ]);

        $candidates = app(WinBackService::class)->winBackCandidates();

        $this->assertFalse($candidates->contains('id', $customer->id));
    }

    public function test_summary_reports_revenue_at_risk_as_win_back_segment_spend(): void
    {
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        $candidate = Customer::create([
            'name' => 'Slipping Away Sam',
            'email' => 'sam@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(20),
        ]);
        $candidate->transactions()->createMany([
            ['amount' => 40, 'points_earned' => 40],
            ['amount' => 50, 'points_earned' => 50],
        ]);

        // Active customer: close to a reward but not a candidate, so their
        // spend must not count toward revenue at risk.
        $active = Customer::create([
            'name' => 'Regular Rita',
            'email' => 'rita@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(2),
        ]);
        $active->transactions()->create(['amount' => 999, 'points_earned' => 999]);

        $summary = app(WinBackService::class)->summary();

        $this->assertSame(90.0, $summary['revenue_at_risk']);
        $this->assertArrayNotHasKey('points_at_stake', $summary);
    }
}
