<template>
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
                    <slot />
                </div>
            </div>
        </div>
    </div>

    <div class="dark-transparent sidebartoggler" @click="closeMobileSidebar"></div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import defaultAvatar from '@images/profile/user-1.jpg';
import AppSidebar from '@/Components/Sidebar/AppSidebar.vue';
import AppHeader from '@/Components/Header/AppHeader.vue';
import { useSidebar } from '@/Composables/useSidebar';

defineProps({
    title: { type: String, default: '' },
});

const loading = ref(true);
const page = usePage();

const userName = page.props.auth?.user?.name ?? 'User';
const userRole = page.props.auth?.user?.role ?? '';
const userAvatar = page.props.auth?.user?.avatar ?? defaultAvatar;

const { toggleSidebar, closeMobileSidebar } = useSidebar();

function logout() {
    router.post('/logout');
}

onMounted(() => {
    loading.value = false;
});
</script>
