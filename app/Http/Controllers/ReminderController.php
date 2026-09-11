<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ReminderService;
use Illuminate\Http\RedirectResponse;

class ReminderController extends Controller
{
    public function store(Customer $customer, ReminderService $service): RedirectResponse
    {
        $reminder = $service->sendReminder($customer);

        if ($reminder === null) {
            return back()->with('error', 'This customer is no longer a win-back candidate.');
        }

        return back()->with('success', "Reminder sent to {$customer->name}.");
    }
}
