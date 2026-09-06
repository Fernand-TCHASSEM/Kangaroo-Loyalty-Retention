const currencyFormatter = new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
});

/**
 * A monetary amount in the locale's currency format. A null amount is
 * treated as zero.
 */
export function formatCurrency(value: number | null): string {
    return currencyFormatter.format(value ?? 0);
}
