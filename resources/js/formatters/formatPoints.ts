const pointsFormatter = new Intl.NumberFormat(undefined, {
    maximumFractionDigits: 0,
});

/**
 * A points figure as a whole number, with the locale's grouping separator.
 */
export function formatPoints(value: number): string {
    return pointsFormatter.format(value);
}
