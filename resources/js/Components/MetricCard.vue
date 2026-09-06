<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
    hint?: string;
}>();
</script>

<template>
    <div class="card h-100">
        <div class="card-body">
            <div class="text-body-secondary small text-uppercase">{{ label }}</div>
            <div class="fs-2 fw-semibold">{{ value }}</div>
            <div v-if="hint" data-testid="metric-card-hint" class="small text-body-secondary mt-1">
                {{ hint }}
            </div>
        </div>
    </div>
</template>
