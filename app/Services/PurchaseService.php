<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Record a simulated purchase: create the transaction, credit the
     * earned points, and mark the customer active again. Wrapped in a
     * single DB transaction so the balance update never drifts from the
     * transaction row.
     */
    public function recordPurchase(Customer $customer, float $amount): Transaction
    {
        $pointsEarned = (int) floor($amount * config('loyalty.points_per_dollar'));

        return DB::transaction(function () use ($customer, $amount, $pointsEarned) {
            $transaction = $customer->transactions()->create([
                'amount' => $amount,
                'points_earned' => $pointsEarned,
            ]);

            $customer->increment('points_balance', $pointsEarned);
            $customer->update(['last_activity_at' => now()]);

            return $transaction;
        });
    }
}
