<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardPayloadTest extends TestCase
{
    use RefreshDatabase;

    private const CUSTOMER_KEYS = [
        'id',
        'name',
        'email',
        'points_balance',
        'points_needed',
        'progress_percent',
        'days_inactive',
        'status',
        'reason',
        'next_reward',
    ];

    public function test_dashboard_customer_payload_exposes_only_the_defined_shape(): void
    {
        $user = User::factory()->create();
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        Customer::create([
            'name' => 'Slipping Away Sam',
            'email' => 'sam@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(20),
        ]);
        Customer::create([
            'name' => 'Never Nina',
            'email' => 'nina@example.com',
            'points_balance' => 0,
            'last_activity_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('winBack', 1, fn (AssertableInertia $row) => $row
                ->hasAll(self::CUSTOMER_KEYS)
                ->where('status', 'win_back')
                ->where('next_reward.name', 'Free coffee')
                ->where('next_reward.points_required', 100)
                ->missing('last_activity_at')
                ->missing('created_at')
                ->missing('updated_at')
                ->missing('is_win_back')
            )
            ->has('allCustomers', 2, fn (AssertableInertia $row) => $row->hasAll(self::CUSTOMER_KEYS))
            ->where('allCustomers.1.status', 'never_active')
            ->where('allCustomers.1.days_inactive', null)
        );
    }
}
