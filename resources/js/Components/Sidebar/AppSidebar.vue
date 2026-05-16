<template>
    <div>
        <SidebarLogo @close-sidebar="$emit('close-sidebar')" />
        <SidebarNav>
            <slot />
        </SidebarNav>
        <SidebarProfile
            :user-name="userName"
            :user-role="userRole"
            :user-avatar="userAvatar"
            @logout="$emit('logout')"
        />
    </div>
</template>

<script setup>
import SidebarLogo from './SidebarLogo.vue';
import SidebarNav from './SidebarNav.vue';
import SidebarProfile from './SidebarProfile.vue';

defineProps({
    userName:   { type: String, default: 'User' },
    userRole:   { type: String, default: '' },
    userAvatar: { type: String, default: '' },
});

defineEmits(['toggle-sidebar', 'close-sidebar', 'logout']);
</script>
