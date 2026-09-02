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
}
