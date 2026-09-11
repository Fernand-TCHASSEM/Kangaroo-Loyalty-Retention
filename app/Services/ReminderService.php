<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Reminder;

class ReminderService
{
    public function __construct(private readonly WinBackService $winBackService) {}

    /**
     * Send a win-back reminder to a customer, or null if they no longer
     * qualify. Re-resolves the candidate against the live win-back set
     * (not the customer as passed in) so a stale request can never send
     * a reminder for a customer who has since won back or fallen out of
     * range.
     */
    public function sendReminder(Customer $customer): ?Reminder
    {
        $winBackCustomer = $this->winBackService->winBackCandidates()->firstWhere('id', $customer->id);

        if ($winBackCustomer === null) {
            return null;
        }

        $message = $this->winBackService->generateReminderMessage(
            $winBackCustomer,
            $winBackCustomer->next_reward,
            $winBackCustomer->points_needed,
        );

        return $customer->reminders()->create([
            'reward_id' => $winBackCustomer->next_reward->id,
            'message' => $message,
        ]);
    }
}
