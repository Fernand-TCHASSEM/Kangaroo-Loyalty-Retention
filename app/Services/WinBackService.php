<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Reward;
use Illuminate\Support\Collection;

class WinBackService
{
    /**
     * Win-back candidates: close to a reward and slipping away, sorted by
     * points_needed ascending (closest to the reward first).
     */
    public function winBackCandidates(): Collection
    {
        return $this->allCustomersWithProgress()
            ->filter(fn (Customer $customer) => $customer->is_win_back)
            ->sortBy('points_needed')
            ->values();
    }

    /**
     * Every customer, decorated with their next reward and progress toward it.
     */
    public function allCustomersWithProgress(): Collection
    {
        $rewards = Reward::orderBy('points_required')->get();

        return Customer::orderBy('id')->get()
            ->map(fn (Customer $customer) => $this->decorate($customer, $rewards))
            ->values();
    }

    public function summary(): array
    {
        $winBack = $this->winBackCandidates();

        return [
            'total_customers' => Customer::count(),
            'win_back_count' => $winBack->count(),
            'points_at_stake' => (int) $winBack->sum('points_needed'),
        ];
    }

    public function generateReminderMessage(Customer $customer, Reward $reward, int $pointsNeeded): string
    {
        return "Hi {$customer->name}, you're only {$pointsNeeded} points away from your {$reward->name}. Come back soon and claim it!";
    }

    /**
     * Attach the derived win-back fields (section 4.2) to a customer.
     */
    private function decorate(Customer $customer, Collection $rewards): Customer
    {
        $nextReward = $this->nextRewardFor($customer, $rewards);
        $daysInactive = $this->daysInactive($customer);

        $pointsNeeded = $nextReward
            ? max(0, $nextReward->points_required - $customer->points_balance)
            : null;

        $progressPercent = $nextReward
            ? min(1, max(0, $customer->points_balance / $nextReward->points_required))
            : null;

        $customer->next_reward = $nextReward;
        $customer->points_needed = $pointsNeeded;
        $customer->progress_percent = $progressPercent;
        $customer->days_inactive = $daysInactive;
        $customer->is_win_back = $nextReward !== null
            && $progressPercent >= config('loyalty.proximity_threshold')
            && $daysInactive >= config('loyalty.inactivity_days');

        return $customer;
    }

    /**
     * The smallest reward whose threshold is above the customer's balance,
     * or null if the balance already meets or exceeds every reward.
     */
    private function nextRewardFor(Customer $customer, Collection $rewards): ?Reward
    {
        return $rewards
            ->first(fn (Reward $reward) => $reward->points_required > $customer->points_balance);
    }

    /**
     * Whole days since the customer's last activity. A customer who never
     * transacted is treated as inactive for an effectively unbounded time.
     */
    private function daysInactive(Customer $customer): int
    {
        if ($customer->last_activity_at === null) {
            return PHP_INT_MAX;
        }

        return (int) $customer->last_activity_at->diffInDays(now());
    }
}
