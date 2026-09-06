import { describe, expect, it } from 'vitest';
import {
    formatCurrency,
    formatDaysInactive,
    formatPercent,
    formatPoints,
} from '@/formatters';

describe('formatPercent', () => {
    it('renders a 0..1 fraction with two decimals and a trailing percent sign', () => {
        expect(formatPercent(0.9)).toBe('90.00%');
        expect(formatPercent(0.9012)).toBe('90.12%');
    });
});

describe('formatDaysInactive', () => {
    it('says "Never active" when there is no day count', () => {
        expect(formatDaysInactive(null)).toBe('Never active');
    });

    it('uses the singular for one day and the plural otherwise', () => {
        expect(formatDaysInactive(1)).toBe('1 day inactive');
        expect(formatDaysInactive(20)).toBe('20 days inactive');
    });
});

describe('formatCurrency', () => {
    it('formats an amount as an en-US US dollar string', () => {
        expect(formatCurrency(773)).toBe('$773.00');
    });

    it('treats a null amount as zero', () => {
        expect(formatCurrency(null)).toBe(formatCurrency(0));
    });
});

describe('formatPoints', () => {
    it('renders a whole number with no fractional part', () => {
        expect(formatPoints(92)).toBe('92');
        expect(formatPoints(1234.6)).not.toContain('.');
    });
});
