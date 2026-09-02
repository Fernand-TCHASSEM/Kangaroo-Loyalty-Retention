<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 fw-semibold mb-0">Dashboard</h2>
        </template>

        <div class="py-4">
            <div class="container-fluid px-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        You're logged in!
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
