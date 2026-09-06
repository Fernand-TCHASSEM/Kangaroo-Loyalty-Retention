<script setup lang="ts">
import CustomerProgressBar from '@/Components/CustomerProgressBar.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { dashboardCopy } from '@/copy';
import { formatDaysInactive, formatPercent } from '@/formatters';
import type { Reason, WinBackCustomer } from '@/types/loyalty';

defineProps<{
    candidates: WinBackCustomer[];
}>();

defineEmits<{
    remind: [customer: WinBackCustomer];
}>();

const copy = dashboardCopy;

function formatProximity(reason: Reason): string {
    if (reason.proximity.threshold === null || reason.proximity.percent === null) {
        return 'Balance meets every reward';
    }

    return `${reason.proximity.balance}/${reason.proximity.threshold} pts (${formatPercent(reason.proximity.percent)})`;
}

function formatReason(customer: WinBackCustomer): string {
    const reason = customer.reason;

    const proximity =
        reason.proximity.threshold === null || reason.proximity.percent === null
            ? 'has enough points for every reward'
            : `is ${formatPercent(reason.proximity.percent)} of the way to the next reward (${reason.proximity.balance} of ${reason.proximity.threshold} points)`;

    const inactivity =
        reason.inactivity.days_inactive === null
            ? 'has never made a purchase'
            : `has been inactive for ${reason.inactivity.days_inactive} days, past the ${reason.inactivity.limit} day limit`;

    return `${customer.name} ${proximity}, and ${inactivity}.`;
}
</script>

<template>
    <div v-if="candidates.length === 0" class="card-body text-body-secondary">
        {{ copy.winBack.empty }}
    </div>

    <ul v-else class="list-group list-group-flush">
        <li v-for="customer in candidates" :key="customer.id" class="list-group-item">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="fw-semibold">{{ customer.name }}</span>
                    <StatusBadge :status="customer.status" class="ms-2" />
                </div>
                <button type="button" class="btn btn-sm btn-primary" @click="$emit('remind', customer)">
                    Send reminder
                </button>
            </div>

            <CustomerProgressBar
                :progress-percent="customer.progress_percent"
                :current="customer.points_balance"
                :required="customer.next_reward?.points_required ?? null"
            />

            <div class="small mt-2">
                <span class="me-3">{{ formatProximity(customer.reason) }}</span>
                <span>{{ formatDaysInactive(customer.days_inactive) }}</span>
            </div>

            <div class="small text-body-secondary mt-1">
                {{ formatReason(customer) }}
            </div>
        </li>
    </ul>
</template>
