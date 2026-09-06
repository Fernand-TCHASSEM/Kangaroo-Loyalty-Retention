<script setup lang="ts">
import { computed } from 'vue';
import type { LoyaltyStatus } from '@/types/loyalty';

const props = defineProps<{
    status: LoyaltyStatus;
}>();

interface Badge {
    class: string;
    label: string;
}

// Each LoyaltyStatus maps to a stock Bootstrap contextual badge class and a
// plain label. No custom colours.
const STATUS_BADGES: Record<LoyaltyStatus, Badge> = {
    win_back: { class: 'text-bg-danger', label: 'Win-back' },
    engaged_close: { class: 'text-bg-primary', label: 'Engaged' },
    fading_far: { class: 'text-bg-warning', label: 'Fading' },
    healthy: { class: 'text-bg-success', label: 'Healthy' },
    no_next_reward: { class: 'text-bg-secondary', label: 'No next reward' },
    never_active: { class: 'text-bg-light', label: 'Never active' },
};

const badge = computed<Badge>(() => STATUS_BADGES[props.status]);
</script>

<template>
    <span class="badge" :class="badge.class">{{ badge.label }}</span>
</template>
