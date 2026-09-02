<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminding_a_win_back_candidate_stores_a_reminder(): void
    {
        $user = User::factory()->create();
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);
        $customer = Customer::create([
            'name' => 'Slipping Away Sam',
            'email' => 'sam@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(20),
        ]);

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/remind");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reminders', [
            'customer_id' => $customer->id,
            'message' => "Hi Slipping Away Sam, you're only 10 points away from your Free coffee. Come back soon and claim it!",
        ]);
    }

    public function test_reminding_a_non_candidate_is_rejected(): void
    {
        $user = User::factory()->create();
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);
        $customer = Customer::create([
            'name' => 'Regular Rita',
            'email' => 'rita@example.com',
            'points_balance' => 90,
            'last_activity_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)->post("/customers/{$customer->id}/remind");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reminders', 0);
    }
}
