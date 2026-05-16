<template>
    <li class="nav-item dropdown">
        <a class="nav-link pe-0" href="javascript:void(0)"
           data-bs-toggle="dropdown" aria-expanded="false">
            <div class="d-flex align-items-center">
                <div class="user-profile-img">
                    <img v-if="userAvatar" :src="userAvatar"
                         class="rounded-circle object-fit-cover" width="35" height="35" alt="user" />
                    <span v-else
                          class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-semibold"
                          style="width:35px;height:35px;font-size:0.75rem;flex-shrink:0">
                        {{ initials(userName) }}
                    </span>
                </div>
            </div>
        </a>

        <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" style="min-width:270px">
            <div class="profile-dropdown" data-simplebar>
                <div class="py-3 px-7 pb-0">
                    <h5 class="mb-0 fs-5 fw-semibold">User Profile</h5>
                </div>

                <!-- User info -->
                <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                    <img v-if="userAvatar" :src="userAvatar"
                         class="rounded-circle object-fit-cover flex-shrink-0"
                         width="64" height="64" alt="user" />
                    <span v-else
                          class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                          style="width:64px;height:64px;font-size:1.25rem">
                        {{ initials(userName) }}
                    </span>

                    <div class="ms-3 overflow-hidden">
                        <h5 class="mb-0 fs-3 fw-semibold text-truncate">{{ userName }}</h5>
                        <p class="mb-1 text-muted small text-truncate">{{ userEmail }}</p>
                        <!-- Login method badge -->
                        <span v-if="userLoginMethod === 'google'"
                              class="badge d-inline-flex align-items-center gap-1"
                              style="background:#f1f3f4;color:#3c4043;font-weight:500;font-size:0.7rem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="10" height="10">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66 2.84-.18-.68z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Google
                        </span>
                        <span v-else-if="userLoginMethod === 'sso'"
                              class="badge text-bg-primary d-inline-flex align-items-center gap-1"
                              style="font-size:0.7rem">
                            <i class="ti ti-shield-lock" style="font-size:0.65rem"></i>
                            SSO
                        </span>
                        <span v-else
                              class="badge text-bg-secondary d-inline-flex align-items-center gap-1"
                              style="font-size:0.7rem">
                            <i class="ti ti-key" style="font-size:0.65rem"></i>
                            Password
                        </span>
                    </div>
                </div>

                <!-- Dynamic profile menu items -->
                <ul v-if="profileMenu.length" class="list-unstyled px-4 pt-3 pb-0 mb-0">
                    <li v-for="item in profileMenu" :key="item.href">
                        <Link :href="item.href"
                              class="dropdown-item rounded-2 d-flex align-items-center gap-2 py-2">
                            <i v-if="item.icon" :class="item.icon" class="fs-5 flex-shrink-0"></i>
                            <span>{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>

                <!-- Logout -->
                <div class="d-grid py-4 px-7" :class="profileMenu.length ? 'pt-3' : 'pt-8'">
                    <a href="/logout" class="btn btn-logout btn-outline-primary"
                       @click.prevent="$emit('logout')">Log Out</a>
                </div>
            </div>
        </div>
    </li>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    userName:        { type: String, default: 'User' },
    userRole:        { type: String, default: '' },
    userAvatar:      { type: String, default: '' },
    userEmail:       { type: String, default: '' },
    userLoginMethod: { type: String, default: 'password' },
});

defineEmits(['logout']);

const profileMenu = computed(() => usePage().props.profile_menu ?? []);

function initials(name) {
    return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
}
</script>

<style scoped>
.btn-logout:hover,
.btn-logout:focus {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #fff !important;
}
</style>
