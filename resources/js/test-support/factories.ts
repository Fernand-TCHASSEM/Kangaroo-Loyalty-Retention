import type { WinBackCustomer } from '@/types/loyalty';

/**
 * A decorated win-back customer for tests. Override any field to shape the
 * case under test.
 */
export function makeWinBackCustomer(overrides: Partial<WinBackCustomer> = {}): WinBackCustomer {
    return {
        id: 1,
        name: 'Slipping Away Sam',
        email: 'sam@example.com',
        points_balance: 90,
        points_needed: 10,
        progress_percent: 0.9,
        days_inactive: 20,
        status: 'win_back',
        reason: {
            proximity: { balance: 90, threshold: 100, percent: 0.9 },
            inactivity: { days_inactive: 20, limit: 14 },
        },
        next_reward: { name: 'Free coffee', points_required: 100 },
        ...overrides,
    };
}
