// Types for the dashboard payload shaped by CustomerWinBackResource (WI-3).

export type LoyaltyStatus =
    | 'win_back'
    | 'engaged_close'
    | 'fading_far'
    | 'healthy'
    | 'no_next_reward'
    | 'never_active';

export interface Reason {
    proximity: {
        balance: number;
        threshold: number | null;
        percent: number | null;
    };
    inactivity: {
        days_inactive: number | null;
        limit: number;
    };
}

export interface NextReward {
    name: string;
    points_required: number;
}

export interface WinBackCustomer {
    id: number;
    name: string;
    email: string;
    points_balance: number;
    points_needed: number | null;
    progress_percent: number | null;
    days_inactive: number | null;
    status: LoyaltyStatus;
    reason: Reason;
    next_reward: NextReward | null;
}

export interface DashboardSummary {
    total_customers: number;
    win_back_count: number;
    revenue_at_risk: number;
}

export interface DashboardConfig {
    proximity_threshold: number;
    inactivity_days: number;
}
