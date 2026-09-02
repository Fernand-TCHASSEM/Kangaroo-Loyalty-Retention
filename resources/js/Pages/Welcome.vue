<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center bg-light text-center px-3">
        <h1 class="display-6 fw-bold mb-2">Loyalty Retention</h1>
        <p class="text-body-secondary mb-4" style="max-width: 32rem">
            Win-back dashboard for loyalty programs. Surface customers who are
            close to a reward and slipping away, before they churn.
        </p>

        <div v-if="canLogin" class="d-flex gap-2">
            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-primary">
                Dashboard
            </Link>

            <template v-else>
                <Link :href="route('login')" class="btn btn-primary">Log in</Link>
                <Link v-if="canRegister" :href="route('register')" class="btn btn-outline-secondary">
                    Register
                </Link>
            </template>
        </div>
    </div>
</template>
