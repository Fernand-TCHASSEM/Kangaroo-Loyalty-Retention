<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\WinBackService;
use Illuminate\Http\RedirectResponse;

class ReminderController extends Controller
{
    public function store(Customer $customer, WinBackService $service): RedirectResponse
    {
        $winBackCustomer = $service->winBackCandidates()->firstWhere('id', $customer->id);

        if ($winBackCustomer === null) {
            return back()->with('error', 'This customer is no longer a win-back candidate.');
        }

        $message = $service->generateReminderMessage(
            $winBackCustomer,
            $winBackCustomer->next_reward,
            $winBackCustomer->points_needed,
        );

        $customer->reminders()->create([
            'reward_id' => $winBackCustomer->next_reward->id,
            'message' => $message,
        ]);

        return back()->with('success', "Reminder sent to {$customer->name}.");
    }
}
