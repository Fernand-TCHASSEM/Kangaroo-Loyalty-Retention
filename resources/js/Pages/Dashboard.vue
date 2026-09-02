<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CustomerProgressBar from '@/Components/CustomerProgressBar.vue';
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

const proximityPercent = computed(() => Math.round(props.config.proximity_threshold * 100));

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
    if (customer.last_activity_at === null) {
        return 'Never active';
    }

    return `${customer.days_inactive} days inactive`;
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
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-body-secondary small text-uppercase">Total customers</div>
                                <div class="fs-2 fw-semibold">{{ summary.total_customers }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100 text-white bg-danger">
                            <div class="card-body">
                                <div class="small text-uppercase opacity-75">Win-back candidates</div>
                                <div class="fs-2 fw-semibold">{{ summary.win_back_count }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-body-secondary small text-uppercase">Points at stake</div>
                                <div class="fs-2 fw-semibold">{{ summary.points_at_stake }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone B: win-back list -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h3 class="h5 mb-1">Customers close to a reward who are slipping away</h3>
                        <div class="small text-body-secondary">
                            At least {{ proximityPercent }}% to a reward, inactive {{ config.inactivity_days }}+ days.
                        </div>
                    </div>

                    <div v-if="winBack.length === 0" class="card-body text-body-secondary">
                        No customers are slipping away right now.
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Progress</th>
                                    <th>Points needed</th>
                                    <th>Inactive</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="customer in winBack" :key="customer.id">
                                    <td>{{ customer.name }}</td>
                                    <td>
                                        <CustomerProgressBar
                                            :progress-percent="customer.progress_percent"
                                            :current="customer.points_balance"
                                            :required="customer.next_reward?.points_required ?? null"
                                        />
                                    </td>
                                    <td>{{ customer.points_needed }} points to {{ customer.next_reward.name }}</td>
                                    <td>{{ formatInactivity(customer) }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary" @click="sendReminder(customer)">
                                            Send reminder
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Zone C: all customers -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h3 class="h5 mb-0">All customers</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
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
