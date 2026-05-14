<template>
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
</template>

<script setup>
import { computed } from 'vue';
import defaultAvatar from '@images/profile/user-1.jpg';

const props = defineProps({
    userName: { type: String, default: 'User' },
    userRole: { type: String, default: '' },
    userAvatar: { type: String, default: '' },
});

defineEmits(['logout']);

const avatarSrc = computed(() => props.userAvatar || defaultAvatar);
</script>
