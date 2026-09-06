<script setup lang="ts">
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CustomerProgressBar from '@/Components/CustomerProgressBar.vue';
import MetricCard from '@/Components/MetricCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import WinBackList from '@/Components/WinBackList.vue';
import { dashboardCopy } from '@/copy';
import { formatCurrency, formatDaysInactive } from '@/formatters';
import { useWinBackDashboard } from '@/composables/useWinBackDashboard';
import { Head, router } from '@inertiajs/vue3';
import type { DashboardConfig, DashboardSummary, WinBackCustomer } from '@/types/loyalty';

const props = defineProps<{
    summary: DashboardSummary;
    winBack: WinBackCustomer[];
    allCustomers: WinBackCustomer[];
    config: DashboardConfig;
}>();

const copy = dashboardCopy;

const proximityPercent = computed(() => Math.round(props.config.proximity_threshold * 100));

const { winBack, count } = useWinBackDashboard(() => props.winBack);

const purchaseAmounts = ref<Record<number, number>>({});
props.allCustomers.forEach((customer) => {
    purchaseAmounts.value[customer.id] = 20;
});

function sendReminder(customer: WinBackCustomer): void {
    router.post(`/customers/${customer.id}/remind`, {}, { preserveScroll: true });
}

function simulatePurchase(customer: WinBackCustomer): void {
    router.post(
        `/customers/${customer.id}/simulate`,
        { amount: purchaseAmounts.value[customer.id] },
        { preserveScroll: true },
    );
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
                        <MetricCard :label="copy.metrics.totalCustomers" :value="summary.total_customers" />
                    </div>
                    <div class="col-md-4">
                        <MetricCard :label="copy.metrics.winBackCount" :value="summary.win_back_count" />
                    </div>
                    <div class="col-md-4">
                        <MetricCard
                            :label="copy.metrics.revenueAtRisk"
                            :value="formatCurrency(summary.revenue_at_risk)"
                            :hint="copy.metrics.revenueAtRiskHint"
                        />
                    </div>
                </div>

                <!-- Zone B: win-back list -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="h5 mb-1">{{ copy.winBack.title(count) }}</h3>
                        <div class="small text-body-secondary">
                            {{ copy.winBack.subtitle(proximityPercent, config.inactivity_days) }}
                        </div>
                    </div>

                    <WinBackList :candidates="winBack" @remind="sendReminder" />
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
                                    <td>{{ formatDaysInactive(customer.days_inactive) }}</td>
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
