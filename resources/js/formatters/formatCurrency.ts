const currencyFormatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});

/**
 * A monetary amount as an en-US US dollar string, for example "$773.00".
 * A null amount is treated as zero.
 */
export function formatCurrency(value: number | null): string {
    return currencyFormatter.format(value ?? 0);
}
