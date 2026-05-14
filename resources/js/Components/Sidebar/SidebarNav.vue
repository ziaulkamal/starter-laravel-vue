<template>
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
</template>

<script setup>
import { onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SimpleBar from 'simplebar';

const page = usePage();

function isActive(path) {
    return page.url === path || page.url.startsWith(path + '/');
}

onMounted(() => {
    const scrollEl = document.querySelector('.scroll-sidebar');
    if (scrollEl) new SimpleBar(scrollEl);
});
</script>
