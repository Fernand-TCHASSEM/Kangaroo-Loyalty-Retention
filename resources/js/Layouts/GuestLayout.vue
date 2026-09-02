<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center bg-light py-4">
        <div class="mb-4">
            <Link href="/">
                <ApplicationLogo style="height: 5rem; width: 5rem; fill: #6c757d" />
            </Link>
        </div>

        <div class="card shadow-sm w-100" style="max-width: 28rem">
            <div class="card-body p-4">
                <slot />
            </div>
        </div>
    </div>
</template>
