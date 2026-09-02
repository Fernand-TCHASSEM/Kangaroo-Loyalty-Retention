<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Reward;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LoyaltyDemoSeeder extends Seeder
{
    public function run(): void
    {
        Reward::create(['name' => 'Free coffee', 'points_required' => 100]);
        Reward::create(['name' => 'Free lunch', 'points_required' => 300]);

        // Win-back candidates: >= 80% of the next reward, inactive 14+ days.
        // points_needed (ascending): Emma 8, Noah 15, Ava 19, Liam 30, Sophia 55.
        $this->seedCustomer('Emma Clarke', 'emma.clarke@example.com', 92, 20);
        $this->seedCustomer('Noah Bennett', 'noah.bennett@example.com', 85, 30);
        $this->seedCustomer('Ava Foster', 'ava.foster@example.com', 81, 15);
        $this->seedCustomer('Liam Ortiz', 'liam.ortiz@example.com', 270, 18);
        $this->seedCustomer('Sophia Reyes', 'sophia.reyes@example.com', 245, 25);

        // Close to a reward but still active: must NOT appear in the win-back list.
        $this->seedCustomer('Mason Dupree', 'mason.dupree@example.com', 95, 2);
        $this->seedCustomer('Isabella Wren', 'isabella.wren@example.com', 280, 5);
        $this->seedCustomer('Ethan Marsh', 'ethan.marsh@example.com', 88, 1);

        // Inactive but far from any reward: must NOT appear in the win-back list.
        $this->seedCustomer('Olivia Hayes', 'olivia.hayes@example.com', 20, 45);
        $this->seedCustomer('Lucas Bright', 'lucas.bright@example.com', 150, 60);
        $this->seedCustomer('Mia Sutton', 'mia.sutton@example.com', 5, 90);

        // Ordinary customers: mixed balances, recently active.
        $this->seedCustomer('James Okafor', 'james.okafor@example.com', 40, 3);
        $this->seedCustomer('Charlotte Nguyen', 'charlotte.nguyen@example.com', 60, 1);
        $this->seedCustomer('Benjamin Voss', 'benjamin.voss@example.com', 110, 5);
        $this->seedCustomer('Amelia Ross', 'amelia.ross@example.com', 15, 4);
        $this->seedCustomer('Henry Castillo', 'henry.castillo@example.com', 200, 6);
        $this->seedCustomer('Grace Whitfield', 'grace.whitfield@example.com', 70, 7);

        // Edge case: balance already meets/exceeds every reward, so there is
        // nothing left to chase (next_reward is null, never a win-back candidate).
        $this->seedCustomer('William Park', 'william.park@example.com', 320, 10);

        // Never transacted: no activity at all, balance stays at 0.
        Customer::create([
            'name' => 'Zoe Bramwell',
            'email' => 'zoe.bramwell@example.com',
            'points_balance' => 0,
            'last_activity_at' => null,
        ]);
    }

    /**
     * Create a customer with a plausible transaction history: one or two
     * historical purchases whose points sum to the target balance, the most
     * recent one dated on the customer's last activity date.
     */
    private function seedCustomer(string $name, string $email, int $balance, int $daysSinceLastActivity): Customer
    {
        $lastActivityAt = Carbon::now()->subDays($daysSinceLastActivity);

        $customer = Customer::create([
            'name' => $name,
            'email' => $email,
            'points_balance' => $balance,
            'last_activity_at' => $lastActivityAt,
        ]);

        $firstAmount = intdiv($balance, 2);
        $secondAmount = $balance - $firstAmount;

        if ($firstAmount > 0) {
            $this->createTransaction($customer, $firstAmount, $lastActivityAt->copy()->subDays(25));
        }

        $this->createTransaction($customer, $secondAmount, $lastActivityAt);

        return $customer;
    }

    private function createTransaction(Customer $customer, int $amount, Carbon $date): void
    {
        $transaction = new Transaction([
            'customer_id' => $customer->id,
            'amount' => $amount,
            'points_earned' => $amount,
        ]);

        $transaction->created_at = $date;
        $transaction->updated_at = $date;
        $transaction->save();
    }
}
