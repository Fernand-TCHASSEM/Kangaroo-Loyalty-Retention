<?php

namespace App\Http\Resources;

use App\Enums\LoyaltyStatus;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The explicit contract for a decorated customer sent to the dashboard.
 * Only these fields cross to the frontend; raw Eloquent columns such as
 * timestamps and foreign keys stay on the server.
 *
 * @mixin Customer
 */
class CustomerWinBackResource extends JsonResource
{
    /**
     * Inertia consumes this as a plain array, so drop the "data" envelope.
     */
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'points_balance' => $this->points_balance,
            'points_needed' => $this->points_needed,
            'progress_percent' => $this->progress_percent,
            'days_inactive' => $this->status === LoyaltyStatus::NEVER_ACTIVE ? null : $this->days_inactive,
            'status' => $this->status->value,
            'reason' => $this->reason,
            'next_reward' => $this->next_reward === null ? null : [
                'name' => $this->next_reward->name,
                'points_required' => $this->next_reward->points_required,
            ],
        ];
    }
}
