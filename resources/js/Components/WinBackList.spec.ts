import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import WinBackList from '@/Components/WinBackList.vue';
import { dashboardCopy } from '@/copy';
import { makeWinBackCustomer } from '@/test-support/factories';

describe('WinBackList', () => {
    it('shows both signals and the plain-language reason for a candidate', () => {
        const customer = makeWinBackCustomer({
            name: 'Emma Clarke',
            reason: {
                proximity: { balance: 92, threshold: 100, percent: 0.92 },
                inactivity: { days_inactive: 24, limit: 14 },
            },
            days_inactive: 24,
        });

        const text = mount(WinBackList, { props: { candidates: [customer] } }).text();

        expect(text).toContain('92/100 pts (92.00%)');
        expect(text).toContain('24 days inactive');
        expect(text).toContain(
            'Emma Clarke is 92.00% of the way to the next reward (92 of 100 points), and has been inactive for 24 days, past the 14 day limit.',
        );
    });

    it('renders the empty-state message when there are no candidates', () => {
        const wrapper = mount(WinBackList, { props: { candidates: [] } });

        expect(wrapper.find('ul').exists()).toBe(false);
        expect(wrapper.text()).toContain(dashboardCopy.winBack.empty);
    });

    it('emits remind with the customer when the reminder button is clicked', async () => {
        const customer = makeWinBackCustomer({ id: 7 });
        const wrapper = mount(WinBackList, { props: { candidates: [customer] } });

        await wrapper.get('button').trigger('click');

        expect(wrapper.emitted('remind')).toHaveLength(1);
        expect(wrapper.emitted('remind')?.[0]).toEqual([customer]);
    });
});
