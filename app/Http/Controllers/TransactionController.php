<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimulatePurchaseRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(SimulatePurchaseRequest $request, Customer $customer): RedirectResponse
    {
        $amount = (float) $request->validated('amount');
        $pointsEarned = (int) floor($amount * config('loyalty.points_per_dollar'));

        DB::transaction(function () use ($customer, $amount, $pointsEarned) {
            $customer->transactions()->create([
                'amount' => $amount,
                'points_earned' => $pointsEarned,
            ]);

            $customer->increment('points_balance', $pointsEarned);
            $customer->update(['last_activity_at' => now()]);
        });

        return back()->with(
            'success',
            "Simulated a \$".number_format($amount, 2)." purchase for {$customer->name}: +{$pointsEarned} points."
        );
    }
}
