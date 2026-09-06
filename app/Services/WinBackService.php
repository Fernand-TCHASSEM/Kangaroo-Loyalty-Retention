<?php

namespace App\Services;

use App\Enums\LoyaltyStatus;
use App\Models\Customer;
use App\Models\Reward;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WinBackService
{
    /**
     * Win-back candidates: close to a reward and slipping away, sorted by
     * points_needed ascending (closest to the reward first).
     *
     * The cheap, coarse signals (inactivity and proximity to the next reward)
     * are filtered in SQL so only the small candidate set is hydrated and
     * decorated. classify() then stays the final authority on WIN_BACK.
     */
    public function winBackCandidates(): Collection
    {
        $rewards = Reward::orderBy('points_required')->get();

        return $this->coarseWinBackQuery()->get()
            ->map(fn (Customer $customer) => $this->decorate($customer, $rewards))
            ->filter(fn (Customer $customer) => $customer->status === LoyaltyStatus::WIN_BACK)
            ->sortBy('points_needed')
            ->values();
    }

    /**
     * Customers that clear both coarse win-back signals at the database level:
     *
     * - inactive: last_activity_at is null, or older than the configured
     *   inactivity window;
     * - close: points_balance is at least proximity_threshold of the smallest
     *   reward threshold still above the balance (a correlated subquery). A
     *   balance that meets every reward yields null here and is excluded.
     *
     * Thresholds come from config, not hardcoded in the query.
     */
    private function coarseWinBackQuery(): Builder
    {
        $inactiveOnOrBefore = now()->subDays(config('loyalty.inactivity_days'));

        return Customer::query()
            ->where(function (Builder $query) use ($inactiveOnOrBefore) {
                $query->whereNull('last_activity_at')
                    ->orWhere('last_activity_at', '<=', $inactiveOnOrBefore);
            })
            ->whereRaw(
                'points_balance >= cast(? as decimal) * (select min(points_required) from rewards where points_required > customers.points_balance)',
                [config('loyalty.proximity_threshold')]
            )
            ->orderBy('id');
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
            'revenue_at_risk' => $this->revenueAtRisk($winBack),
        ];
    }

    /**
     * The total historical spend of the win-back segment: the sum of every
     * transaction amount for the recoverable customers. This is money we can
     * lose if they do not come back, not a count of missing points.
     */
    private function revenueAtRisk(Collection $winBack): float
    {
        return (float) Transaction::query()
            ->whereIn('customer_id', $winBack->pluck('id'))
            ->sum('amount');
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

        $closeToReward = $nextReward !== null
            && $progressPercent >= config('loyalty.proximity_threshold');
        $inactive = $daysInactive >= config('loyalty.inactivity_days');

        $customer->next_reward = $nextReward;
        $customer->points_needed = $pointsNeeded;
        $customer->progress_percent = $progressPercent;
        $customer->days_inactive = $daysInactive;
        $customer->status = $this->classify($customer, $nextReward, $closeToReward, $inactive);
        $customer->reason = $this->buildReason($customer, $nextReward, $progressPercent, $daysInactive);

        return $customer;
    }

    /**
     * A small structured explanation of the two signals for this customer,
     * in plain values, so the reason can be displayed without recomputing
     * it. Thresholds come from config, not hardcoded. days_inactive is null
     * for a customer who never transacted; the NEVER_ACTIVE status already
     * carries that meaning.
     */
    private function buildReason(Customer $customer, ?Reward $nextReward, ?float $progressPercent, int $daysInactive): array
    {
        return [
            'proximity' => [
                'balance' => $customer->points_balance,
                'threshold' => $nextReward?->points_required,
                'percent' => $progressPercent,
            ],
            'inactivity' => [
                'days_inactive' => $customer->last_activity_at === null ? null : $daysInactive,
                'limit' => config('loyalty.inactivity_days'),
            ],
        ];
    }

    /**
     * Resolve a customer's LoyaltyStatus from the two win-back signals.
     *
     * WIN_BACK takes precedence over NEVER_ACTIVE so winBackCandidates() still
     * includes a customer who never transacted but is close enough: that
     * customer stays recoverable.
     */
    private function classify(Customer $customer, ?Reward $nextReward, bool $closeToReward, bool $inactive): LoyaltyStatus
    {
        if ($nextReward === null) {
            return LoyaltyStatus::NO_NEXT_REWARD;
        }

        if ($closeToReward && $inactive) {
            return LoyaltyStatus::WIN_BACK;
        }

        if ($customer->last_activity_at === null) {
            return LoyaltyStatus::NEVER_ACTIVE;
        }

        if ($closeToReward) {
            return LoyaltyStatus::ENGAGED_CLOSE;
        }

        if ($inactive) {
            return LoyaltyStatus::FADING_FAR;
        }

        return LoyaltyStatus::HEALTHY;
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
