<script setup>
import { computed } from 'vue';

const props = defineProps({
    progressPercent: {
        type: Number,
        default: null,
    },
    current: {
        type: Number,
        required: true,
    },
    required: {
        type: Number,
        default: null,
    },
});

const percent = computed(() => {
    if (props.progressPercent === null) {
        return 0;
    }

    return Math.round(Math.min(1, Math.max(0, props.progressPercent)) * 100);
});
</script>

<template>
    <div v-if="required !== null" style="min-width: 10rem">
        <div class="progress" style="height: 0.5rem" role="progressbar" :aria-valuenow="percent" aria-valuemin="0" aria-valuemax="100">
            <div
                class="progress-bar"
                :class="percent >= 100 ? 'bg-success' : 'bg-primary'"
                :style="{ width: percent + '%' }"
            ></div>
        </div>
        <div class="small text-body-secondary mt-1">{{ current }} / {{ required }} points</div>
    </div>
    <div v-else class="small text-body-secondary">No reward to chase</div>
</template>
