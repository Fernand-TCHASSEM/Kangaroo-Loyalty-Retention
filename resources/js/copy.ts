// Centralized dashboard copy, so wording stays consistent across the page.

export const dashboardCopy = {
    metrics: {
        totalCustomers: 'Total customers',
        winBackCount: 'Win-back customers',
        revenueAtRisk: 'Revenue at risk in the win-back segment',
        revenueAtRiskHint: 'Historical spend of the win-back segment',
    },
    winBack: {
        title: (count: number): string =>
            count === 1
                ? '1 customer is close to a reward and slipping away'
                : `${count} customers are close to a reward and slipping away`,
        subtitle: (proximityPercent: number, inactivityDays: number): string =>
            `At least ${proximityPercent}% of the way to a reward, and inactive for ${inactivityDays} days or more.`,
        empty: 'No customers are slipping away right now.',
    },
    allCustomers: {
        title: 'All customers',
    },
};
