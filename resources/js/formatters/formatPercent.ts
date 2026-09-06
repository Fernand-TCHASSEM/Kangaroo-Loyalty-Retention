/**
 * A 0..1 fraction as a percentage with two decimals and a trailing percent
 * sign, for example 0.9 becomes "90.00%".
 */
export function formatPercent(fraction: number): string {
    return `${(fraction * 100).toFixed(2)}%`;
}
