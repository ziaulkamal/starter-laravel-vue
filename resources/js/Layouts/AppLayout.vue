<template>
    <!-- Preloader -->
    <div class="preloader" v-if="loading">
        <img src="/favicon.png" alt="loader" class="lds-ripple img-fluid" />
    </div>

    <div id="main-wrapper">
        <!-- Sidebar -->
        <aside class="left-sidebar with-vertical">
            <div>
                <AppSidebar
                    :user-name="userName"
                    :user-role="userRole"
                    @toggle-sidebar="toggleSidebar"
                    @close-sidebar="closeMobileSidebar"
                    @logout="logout"
                >
                    <slot name="sidebar-menu" />
                </AppSidebar>
            </div>
        </aside>

        <div class="page-wrapper">
            <!-- Header -->
            <header class="topbar">
                <div class="with-vertical">
                    <AppHeader
                        :user-name="userName"
                        :user-role="userRole"
                        :user-avatar="userAvatar"
                        @toggle-sidebar="toggleSidebar"
                        @logout="logout"
                    />
                </div>
            </header>

            <!-- Page Content -->
            <div class="body-wrapper">
                <div class="container-fluid">
                    <slot />
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay mobile — harus di LUAR #main-wrapper agar selector CSS `.show-sidebar + .dark-transparent` bekerja -->
    <div class="dark-transparent sidebartoggler" @click="closeMobileSidebar"></div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import defaultAvatar from '@images/profile/user-1.jpg';
import AppSidebar from '@/Components/AppSidebar.vue';
import AppHeader from '@/Components/AppHeader.vue';

defineProps({
    title: { type: String, default: '' },
});

// xl breakpoint tema = 1300px (lihat _variables.scss $grid-breakpoints)
const XL_BREAKPOINT = 1300;

const loading = ref(true);
const page = usePage();

const userName = page.props.auth?.user?.name ?? 'User';
const userRole = page.props.auth?.user?.role ?? '';
const userAvatar = page.props.auth?.user?.avatar ?? defaultAvatar;

function isMobile() {
    return window.innerWidth < XL_BREAKPOINT;
}

function toggleSidebar() {
    if (isMobile()) {
        // Mobile: show/hide sidebar dengan class show-sidebar pada #main-wrapper
        document.getElementById('main-wrapper')?.classList.toggle('show-sidebar');
    } else {
        // Desktop: menyusut/melebar sidebar dengan data-sidebartype pada body
        const current = document.body.getAttribute('data-sidebartype');
        document.body.setAttribute('data-sidebartype', current === 'full' ? 'mini-sidebar' : 'full');
    }
}

function closeMobileSidebar() {
    document.getElementById('main-wrapper')?.classList.remove('show-sidebar');
}

function logout() {
    router.post('/logout');
}

// Tutup sidebar mobile saat resize ke desktop
function onResize() {
    if (!isMobile()) {
        closeMobileSidebar();
    }
}

onMounted(() => {
    loading.value = false;
    window.addEventListener('resize', onResize);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', onResize);
});
</script>
