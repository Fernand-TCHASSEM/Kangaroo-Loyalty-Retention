<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulatePurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulating_a_purchase_updates_balance_and_last_activity(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Loyal Larry',
            'email' => 'larry@example.com',
            'points_balance' => 50,
            'last_activity_at' => now()->subDays(30),
        ]);

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/simulate", [
            'amount' => 25,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertSame(75, $customer->points_balance);
        $this->assertTrue($customer->last_activity_at->greaterThan(now()->subMinute()));
        $this->assertDatabaseHas('transactions', [
            'customer_id' => $customer->id,
            'amount' => '25.00',
            'points_earned' => 25,
        ]);
    }

    public function test_an_invalid_amount_is_rejected(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Loyal Larry',
            'email' => 'larry@example.com',
            'points_balance' => 50,
            'last_activity_at' => now()->subDays(30),
        ]);

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/simulate", [
            'amount' => 0,
        ]);

        $response->assertSessionHasErrors('amount');

        $customer->refresh();
        $this->assertSame(50, $customer->points_balance);
        $this->assertDatabaseCount('transactions', 0);
    }
}
