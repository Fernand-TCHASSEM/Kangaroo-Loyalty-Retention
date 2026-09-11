<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimulatePurchaseRequest;
use App\Models\Customer;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;

class TransactionController extends Controller
{
    public function store(SimulatePurchaseRequest $request, Customer $customer, PurchaseService $service): RedirectResponse
    {
        $amount = (float) $request->validated('amount');

        $transaction = $service->recordPurchase($customer, $amount);

        return back()->with(
            'success',
            'Simulated a $'.number_format($amount, 2)." purchase for {$customer->name}: +{$transaction->points_earned} points."
        );
    }
}
