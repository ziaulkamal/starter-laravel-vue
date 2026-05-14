<template>
    <div>
        <!-- Logo -->
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="/" class="text-nowrap logo-img">
                <img src="@images/logos/dark-logo.svg" class="dark-logo" alt="Logo-Dark" />
                <img src="@images/logos/light-logo.svg" class="light-logo" alt="Logo-Light" />
            </a>
            <a href="javascript:void(0)"
               class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none"
               @click.prevent="$emit('close-sidebar')">
                <i class="ti ti-x"></i>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <ul id="sidebarnav">
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Home</span>
                </li>

                <li class="sidebar-item" :class="{ active: isActive('/') }">
                    <Link class="sidebar-link" href="/" aria-expanded="false">
                        <span><i class="ti ti-layout-dashboard"></i></span>
                        <span class="hide-menu">Dashboard</span>
                    </Link>
                </li>

                <slot />
            </ul>
        </nav>

        <!-- Fixed Profile -->
        <div class="fixed-profile p-3 mx-4 mb-2 bg-secondary-subtle rounded mt-3">
            <div class="hstack gap-3">
                <div class="john-img">
                    <img src="@images/profile/user-1.jpg"
                         class="rounded-circle" width="40" height="40" alt="user" />
                </div>
                <div class="john-title">
                    <h6 class="mb-0 fs-4 fw-semibold">{{ userName }}</h6>
                    <span class="fs-2">{{ userRole }}</span>
                </div>
                <button class="border-0 bg-transparent text-primary ms-auto"
                        type="button"
                        title="Logout"
                        @click="$emit('logout')">
                    <i class="ti ti-power fs-6"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SimpleBar from 'simplebar';

const props = defineProps({
    userName: { type: String, default: 'User' },
    userRole: { type: String, default: '' },
});

defineEmits(['toggle-sidebar', 'close-sidebar', 'logout']);

const page = usePage();

function isActive(path) {
    return page.url === path || page.url.startsWith(path + '/');
}

onMounted(() => {
    const scrollEl = document.querySelector('.scroll-sidebar');
    if (scrollEl) {
        new SimpleBar(scrollEl);
    }
});
</script>
