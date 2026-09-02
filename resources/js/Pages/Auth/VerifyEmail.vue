<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-4 small text-body-secondary">
            Thanks for signing up! Before getting started, could you verify your
            email address by clicking on the link we just emailed to you? If you
            didn't receive the email, we will gladly send you another.
        </div>

        <div v-if="verificationLinkSent" class="mb-4 small fw-medium text-success">
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="d-flex align-items-center justify-content-between">
                <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                    Resend Verification Email
                </PrimaryButton>

                <Link :href="route('logout')" method="post" as="button" class="small link-secondary">
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
