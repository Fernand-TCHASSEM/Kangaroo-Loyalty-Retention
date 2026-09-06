// Centralized dashboard copy, so wording stays consistent across the page.
// WI-5 extends this with the value-at-risk labels.

export const dashboardCopy = {
    metrics: {
        totalCustomers: 'Total customers',
        winBackCount: 'Win-back customers',
        pointsAtStake: 'Points still needed across the win-back segment',
    },
    winBack: {
        title: (count) =>
            count === 1
                ? '1 customer is close to a reward and slipping away'
                : `${count} customers are close to a reward and slipping away`,
        subtitle: (proximityPercent, inactivityDays) =>
            `At least ${proximityPercent}% of the way to a reward, and inactive for ${inactivityDays} days or more.`,
        empty: 'No customers are slipping away right now.',
    },
    allCustomers: {
        title: 'All customers',
    },
};
