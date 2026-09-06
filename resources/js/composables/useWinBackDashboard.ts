import { computed, toValue } from 'vue';
import type { ComputedRef, MaybeRefOrGetter } from 'vue';
import type { WinBackCustomer } from '@/types/loyalty';

export interface WinBackDashboardView {
    winBack: ComputedRef<WinBackCustomer[]>;
    isEmpty: ComputedRef<boolean>;
    count: ComputedRef<number>;
}

/**
 * Light view state for the win-back list: the candidates sorted by how few
 * points they need (closest first), plus an empty flag and a count. No
 * advice, no scoring, just what the list needs to render.
 */
export function useWinBackDashboard(
    candidates: MaybeRefOrGetter<WinBackCustomer[]>,
): WinBackDashboardView {
    const winBack = computed(() =>
        [...toValue(candidates)].sort(
            (a, b) => (a.points_needed ?? Infinity) - (b.points_needed ?? Infinity),
        ),
    );

    const isEmpty = computed(() => winBack.value.length === 0);
    const count = computed(() => winBack.value.length);

    return { winBack, isEmpty, count };
}
