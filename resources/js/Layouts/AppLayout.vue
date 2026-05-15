<template>
    <Head :title="title ? `${title} - ${appName}` : appName" />

    <div class="preloader" v-if="loading">
        <img src="/favicon.png" alt="loader" class="lds-ripple img-fluid" />
    </div>

    <div id="main-wrapper">
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

            <div class="body-wrapper">
                <div class="container-fluid">
                    <AppBreadcrumb
                        v-if="showBreadcrumb"
                        :title="title"
                        :items="breadcrumb"
                    >
                        <template v-if="$slots['page-actions']" #actions>
                            <slot name="page-actions" />
                        </template>
                    </AppBreadcrumb>
                    <slot />
                </div>
            </div>
        </div>
    </div>

    <div class="dark-transparent sidebartoggler" @click="closeMobileSidebar"></div>

    <InactiveOverlay v-if="!isActive" @logout="logout" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import defaultAvatar from '@images/profile/user-1.jpg';
import AppSidebar from '@/Components/Sidebar/AppSidebar.vue';
import AppHeader from '@/Components/Header/AppHeader.vue';
import AppBreadcrumb from '@/Components/UI/AppBreadcrumb.vue';
import InactiveOverlay from '@/Components/UI/InactiveOverlay.vue';
import { useSidebar } from '@/Composables/useSidebar';

defineProps({
    title:      { type: String, default: '' },
    breadcrumb: { type: Array,  default: () => [] },
});

const loading = ref(true);
const page = usePage();

const userName = page.props.auth?.user?.name ?? 'User';
const userRole = page.props.auth?.user?.role ?? '';
const userAvatar = page.props.auth?.user?.avatar ?? defaultAvatar;
const isActive = computed(() => page.props.auth?.user?.is_active !== false);

const appName = page.props.appName ?? document.title;

// Skip breadcrumb on the root dashboard page only.
// Login/Register use AuthLayout so they never reach here.
const showBreadcrumb = computed(() => {
    const pathname = page.url.split('?')[0];
    return pathname !== '/';
});

const { toggleSidebar, closeMobileSidebar } = useSidebar();

function logout() {
    router.post('/logout');
}

onMounted(() => {
    loading.value = false;
});
</script>
