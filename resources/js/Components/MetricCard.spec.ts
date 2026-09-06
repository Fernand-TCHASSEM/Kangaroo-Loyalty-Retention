import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import MetricCard from '@/Components/MetricCard.vue';

const HINT = '[data-testid="metric-card-hint"]';

describe('MetricCard', () => {
    it('renders the label and the value', () => {
        const wrapper = mount(MetricCard, { props: { label: 'Total customers', value: 19 } });

        expect(wrapper.text()).toContain('Total customers');
        expect(wrapper.text()).toContain('19');
    });

    it('renders the hint element when a hint is given', () => {
        const wrapper = mount(MetricCard, {
            props: {
                label: 'Revenue at risk',
                value: '$773.00',
                hint: 'Historical spend of the win-back segment',
            },
        });

        expect(wrapper.find(HINT).exists()).toBe(true);
        expect(wrapper.find(HINT).text()).toBe('Historical spend of the win-back segment');
    });

    it('omits the hint element when no hint is given', () => {
        const wrapper = mount(MetricCard, { props: { label: 'Win-back customers', value: 5 } });

        expect(wrapper.find(HINT).exists()).toBe(false);
    });
});
