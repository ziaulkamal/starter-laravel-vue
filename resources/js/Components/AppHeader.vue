<template>
    <nav class="navbar navbar-expand-lg p-0">
        <ul class="navbar-nav">
            <!-- Sidebar Toggle -->
            <li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
                <a class="nav-link sidebartoggler" href="javascript:void(0)"
                   @click.prevent="$emit('toggle-sidebar')">
                    <i class="ti ti-menu-2"></i>
                </a>
            </li>
            <!-- Search -->
            <li class="nav-item nav-icon-hover-bg rounded-circle d-none d-lg-flex">
                <a class="nav-link" href="javascript:void(0)"
                   data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="ti ti-search"></i>
                </a>
            </li>
        </ul>

        <!-- Mobile Logo -->
        <div class="d-block d-lg-none py-4">
            <a href="/" class="text-nowrap logo-img">
                <img src="@images/logos/dark-logo.svg" class="dark-logo" alt="Logo-Dark" />
                <img src="@images/logos/light-logo.svg" class="light-logo" alt="Logo-Light" />
            </a>
        </div>

        <!-- Mobile Toggle -->
        <a class="navbar-toggler nav-icon-hover-bg rounded-circle p-0 mx-0 border-0"
           href="javascript:void(0)"
           data-bs-toggle="collapse" data-bs-target="#navbarNav"
           aria-controls="navbarNav" aria-expanded="false">
            <i class="ti ti-dots fs-7"></i>
        </a>

        <!-- Right side -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="d-flex align-items-center justify-content-between">
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">

                    <!-- Dark/Light Mode Toggle -->
                    <li class="nav-item nav-icon-hover-bg rounded-circle">
                        <a class="nav-link" href="javascript:void(0)" @click.prevent="toggleTheme">
                            <i :class="isDark ? 'ti ti-sun sun' : 'ti ti-moon moon'"></i>
                        </a>
                    </li>

                    <!-- Notifications -->
                    <li class="nav-item nav-icon-hover-bg rounded-circle dropdown">
                        <a class="nav-link position-relative" href="javascript:void(0)"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-bell-ringing"></i>
                            <div class="notification bg-primary rounded-circle"></div>
                        </a>
                        <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up p-4">
                            <div class="d-flex align-items-center justify-content-between py-3">
                                <h5 class="mb-0 fs-5 fw-semibold">Notifications</h5>
                            </div>
                            <p class="text-muted text-center py-3 mb-0">No new notifications</p>
                        </div>
                    </li>

                    <!-- Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link pe-0" href="javascript:void(0)"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="user-profile-img">
                                    <img :src="avatarSrc"
                                         class="rounded-circle" width="35" height="35" alt="user" />
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" style="min-width:260px">
                            <div class="profile-dropdown" data-simplebar>
                                <div class="py-3 px-7 pb-0">
                                    <h5 class="mb-0 fs-5 fw-semibold">User Profile</h5>
                                </div>
                                <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                                    <img :src="avatarSrc" class="rounded-circle" width="80" height="80" alt="user" />
                                    <div class="ms-3">
                                        <h5 class="mb-1 fs-3">{{ userName }}</h5>
                                        <span class="mb-1 d-block">{{ userRole }}</span>
                                    </div>
                                </div>
                                <div class="d-grid py-4 px-7 pt-8">
                                    <a href="/logout" class="btn btn-outline-primary"
                                       @click.prevent="$emit('logout')">Log Out</a>
                                </div>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import defaultAvatar from '@images/profile/user-1.jpg';

const props = defineProps({
    userName: { type: String, default: 'User' },
    userRole: { type: String, default: '' },
    userAvatar: { type: String, default: '' },
});

defineEmits(['toggle-sidebar', 'logout']);

const isDark = ref(false);

const avatarSrc = computed(() => props.userAvatar || defaultAvatar);

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.setAttribute('data-bs-theme', isDark.value ? 'dark' : 'light');
}

onMounted(() => {
    isDark.value = document.documentElement.getAttribute('data-bs-theme') === 'dark';
});
</script>
