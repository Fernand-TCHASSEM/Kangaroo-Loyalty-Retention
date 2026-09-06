import { describe, expect, it } from 'vitest';
import { useWinBackDashboard } from '@/composables/useWinBackDashboard';
import { makeWinBackCustomer } from '@/test-support/factories';

describe('useWinBackDashboard', () => {
    it('orders candidates by points needed ascending', () => {
        const { winBack } = useWinBackDashboard([
            makeWinBackCustomer({ id: 1, points_needed: 30 }),
            makeWinBackCustomer({ id: 2, points_needed: 8 }),
            makeWinBackCustomer({ id: 3, points_needed: 19 }),
        ]);

        expect(winBack.value.map((customer) => customer.id)).toEqual([2, 3, 1]);
    });

    it('flags an empty candidate list', () => {
        const { isEmpty, count } = useWinBackDashboard([]);

        expect(isEmpty.value).toBe(true);
        expect(count.value).toBe(0);
    });

    it('is not empty when there is at least one candidate', () => {
        const { isEmpty, count } = useWinBackDashboard([makeWinBackCustomer()]);

        expect(isEmpty.value).toBe(false);
        expect(count.value).toBe(1);
    });
});
