/**
 * The inactivity signal in words: "Never active" when there is no activity,
 * otherwise a singular or plural day count, for example "1 day inactive" or
 * "20 days inactive".
 */
export function formatDaysInactive(days: number | null): string {
    if (days === null) {
        return 'Never active';
    }

    return days === 1 ? '1 day inactive' : `${days} days inactive`;
}
