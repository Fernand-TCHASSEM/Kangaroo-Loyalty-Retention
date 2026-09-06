import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import StatusBadge from '@/Components/StatusBadge.vue';
import type { LoyaltyStatus } from '@/types/loyalty';

const CASES: Array<[LoyaltyStatus, string, string]> = [
    ['win_back', 'text-bg-danger', 'Win-back'],
    ['engaged_close', 'text-bg-primary', 'Engaged'],
    ['fading_far', 'text-bg-warning', 'Fading'],
    ['healthy', 'text-bg-success', 'Healthy'],
    ['no_next_reward', 'text-bg-secondary', 'No next reward'],
    ['never_active', 'text-bg-light', 'Never active'],
];

describe('StatusBadge', () => {
    it.each(CASES)(
        'renders the %s status with its Bootstrap contextual class and label',
        (status, contextualClass, label) => {
            const wrapper = mount(StatusBadge, { props: { status } });

            expect(wrapper.classes()).toContain('badge');
            expect(wrapper.classes()).toContain(contextualClass);
            expect(wrapper.text()).toBe(label);
        },
    );
});
