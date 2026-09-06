<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Reward;
use App\Models\User;
use App\Services\WinBackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WinBackLoopTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulating_a_purchase_takes_a_customer_off_the_win_back_list(): void
    {
        $user = User::factory()->create();
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);

        $customer = Customer::create([
            'name' => 'Slipping Away Sam',
            'email' => 'sam@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(20),
        ]);

        $this->assertTrue(
            app(WinBackService::class)->winBackCandidates()->contains('id', $customer->id),
            'Customer should be a win-back candidate before the purchase.',
        );

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/simulate", [
            'amount' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertSame(95, $customer->points_balance);
        $this->assertTrue($customer->last_activity_at->greaterThan(now()->subMinute()));

        $this->assertFalse(
            app(WinBackService::class)->winBackCandidates()->contains('id', $customer->id),
            'Customer should leave the win-back list once they are active again.',
        );
    }
}
