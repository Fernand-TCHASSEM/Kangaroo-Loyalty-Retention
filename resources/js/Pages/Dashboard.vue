<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CustomerProgressBar from '@/Components/CustomerProgressBar.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { dashboardCopy } from '@/copy';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
    winBack: {
        type: Array,
        required: true,
    },
    allCustomers: {
        type: Array,
        required: true,
    },
    config: {
        type: Object,
        required: true,
    },
});

const copy = dashboardCopy;

const proximityPercent = computed(() => Math.round(props.config.proximity_threshold * 100));

// WI-6 will move this into resources/js/formatters/.
const currencyFormatter = new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'USD',
});

function formatCurrency(value) {
    return currencyFormatter.format(value ?? 0);
}

const purchaseAmounts = ref(
    Object.fromEntries(props.allCustomers.map((customer) => [customer.id, 20])),
);

function sendReminder(customer) {
    router.post(`/customers/${customer.id}/remind`, {}, { preserveScroll: true });
}

function simulatePurchase(customer) {
    router.post(
        `/customers/${customer.id}/simulate`,
        { amount: purchaseAmounts.value[customer.id] },
        { preserveScroll: true },
    );
}

function formatInactivity(customer) {
    if (customer.status === 'never_active') {
        return 'Never active';
    }

    return `${customer.days_inactive} days inactive`;
}

function formatProximity(reason) {
    if (reason.proximity.threshold === null) {
        return 'Balance meets every reward';
    }

    const percent = Math.round(reason.proximity.percent * 100);

    return `${reason.proximity.balance}/${reason.proximity.threshold} pts (${percent}%)`;
}

function formatReason(customer) {
    const reason = customer.reason;

    const proximity =
        reason.proximity.threshold === null
            ? 'has enough points for every reward'
            : `is ${Math.round(reason.proximity.percent * 100)}% of the way to the next reward (${reason.proximity.balance} of ${reason.proximity.threshold} points)`;

    const inactivity =
        reason.inactivity.days_inactive === null
            ? 'has never made a purchase'
            : `has been inactive for ${reason.inactivity.days_inactive} days, past the ${reason.inactivity.limit} day limit`;

    return `${customer.name} ${proximity}, and ${inactivity}.`;
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 fw-semibold mb-0">Dashboard</h2>
        </template>

        <div class="py-4">
            <div class="container-fluid px-4">
                <!-- Zone A: summary cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="text-body-secondary small text-uppercase">{{ copy.metrics.totalCustomers }}</div>
                                <div class="fs-2 fw-semibold">{{ summary.total_customers }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="text-body-secondary small text-uppercase">{{ copy.metrics.winBackCount }}</div>
                                <div class="fs-2 fw-semibold">{{ summary.win_back_count }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="text-body-secondary small text-uppercase">{{ copy.metrics.revenueAtRisk }}</div>
                                <div class="fs-2 fw-semibold">{{ formatCurrency(summary.revenue_at_risk) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone B: win-back list -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="h5 mb-1">{{ copy.winBack.title(winBack.length) }}</h3>
                        <div class="small text-body-secondary">
                            {{ copy.winBack.subtitle(proximityPercent, config.inactivity_days) }}
                        </div>
                    </div>

                    <div v-if="winBack.length === 0" class="card-body text-body-secondary">
                        {{ copy.winBack.empty }}
                    </div>

                    <ul v-else class="list-group list-group-flush">
                        <li v-for="customer in winBack" :key="customer.id" class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-semibold">{{ customer.name }}</span>
                                    <StatusBadge :status="customer.status" class="ms-2" />
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" @click="sendReminder(customer)">
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
                                <span>{{ formatInactivity(customer) }}</span>
                            </div>

                            <div class="small text-body-secondary mt-1">
                                {{ formatReason(customer) }}
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Zone C: all customers -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="h5 mb-0">{{ copy.allCustomers.title }}</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Points balance</th>
                                    <th>Next reward</th>
                                    <th>Progress</th>
                                    <th>Inactive</th>
                                    <th>Simulate purchase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="customer in allCustomers" :key="customer.id">
                                    <td>{{ customer.name }}</td>
                                    <td><StatusBadge :status="customer.status" /></td>
                                    <td>{{ customer.points_balance }}</td>
                                    <td>{{ customer.next_reward ? customer.next_reward.name : 'N/A' }}</td>
                                    <td>
                                        <CustomerProgressBar
                                            :progress-percent="customer.progress_percent"
                                            :current="customer.points_balance"
                                            :required="customer.next_reward?.points_required ?? null"
                                        />
                                    </td>
                                    <td>{{ formatInactivity(customer) }}</td>
                                    <td>
                                        <div class="d-flex gap-2" style="max-width: 12rem">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                max="100000"
                                                class="form-control form-control-sm"
                                                v-model.number="purchaseAmounts[customer.id]"
                                            />
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary text-nowrap"
                                                @click="simulatePurchase(customer)"
                                            >
                                                Simulate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
