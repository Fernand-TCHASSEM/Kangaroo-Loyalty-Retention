<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-vh-100 bg-light">
        <nav class="navbar navbar-expand-sm bg-white border-bottom">
            <div class="container-fluid px-4">
                <Link :href="route('dashboard')" class="navbar-brand d-flex align-items-center">
                    <ApplicationLogo class="d-block" style="height: 2.25rem; width: auto; fill: #212529" />
                </Link>

                <div class="d-none d-sm-flex align-items-center">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </NavLink>
                </div>

                <div class="d-none d-sm-flex align-items-center ms-auto">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="btn btn-light btn-sm d-inline-flex align-items-center">
                                {{ $page.props.auth.user.name }}
                                <svg class="ms-2" style="width: 1rem; height: 1rem" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <button
                    type="button"
                    class="btn btn-light d-sm-none ms-auto"
                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                >
                    <svg style="width: 1.5rem; height: 1.5rem" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path
                            v-if="!showingNavigationDropdown"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div v-show="showingNavigationDropdown" class="d-sm-none w-100 border-top">
                <div class="py-2">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </ResponsiveNavLink>
                </div>

                <div class="border-top py-3">
                    <div class="px-3">
                        <div class="fw-medium">{{ $page.props.auth.user.name }}</div>
                        <div class="text-body-secondary small">{{ $page.props.auth.user.email }}</div>
                    </div>

                    <div class="mt-2">
                        <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="bg-white shadow-sm">
            <div class="container-fluid px-4 py-3">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
