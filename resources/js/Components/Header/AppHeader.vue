<template>
    <nav class="navbar navbar-expand-lg p-0">
        <ul class="navbar-nav">
            <li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
                <a class="nav-link sidebartoggler" href="javascript:void(0)"
                   @click.prevent="$emit('toggle-sidebar')">
                    <i class="ti ti-menu-2"></i>
                </a>
            </li>
            <HeaderSearch />
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
                    <HeaderThemeToggle />
                    <HeaderNotifications />
                    <HeaderUserMenu
                        :user-name="userName"
                        :user-role="userRole"
                        :user-avatar="userAvatar"
                        :user-email="userEmail"
                        :user-login-method="userLoginMethod"
                        @logout="$emit('logout')"
                    />
                </ul>
            </div>
        </div>
    </nav>
</template>

<script setup>
import HeaderSearch from './HeaderSearch.vue';
import HeaderThemeToggle from './HeaderThemeToggle.vue';
import HeaderNotifications from './HeaderNotifications.vue';
import HeaderUserMenu from './HeaderUserMenu.vue';

defineProps({
    userName:        { type: String, default: 'User' },
    userRole:        { type: String, default: '' },
    userAvatar:      { type: String, default: '' },
    userEmail:       { type: String, default: '' },
    userLoginMethod: { type: String, default: 'password' },
});

defineEmits(['toggle-sidebar', 'logout']);
</script>
