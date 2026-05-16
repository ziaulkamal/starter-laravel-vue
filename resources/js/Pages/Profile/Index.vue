<template>
    <AppLayout title="Profil Saya" :breadcrumb="[{ label: 'Profil Saya' }]">

        <!-- Flash -->
        <div v-if="flash.success"
             class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="ti ti-circle-check me-2"></i>{{ flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="card">
            <!-- Tab nav -->
            <ul class="nav user-profile-tab border-bottom px-2" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link position-relative rounded-0 d-flex align-items-center gap-2 bg-transparent py-3 px-4"
                        :class="{ active: activeTab === 'info' }"
                        type="button"
                        @click="activeTab = 'info'"
                    >
                        <i class="ti ti-user-circle fs-5"></i>
                        <span class="d-none d-sm-inline">Informasi Pribadi</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link position-relative rounded-0 d-flex align-items-center gap-2 bg-transparent py-3 px-4"
                        :class="{ active: activeTab === 'email' }"
                        type="button"
                        @click="activeTab = 'email'"
                    >
                        <i class="ti ti-mail fs-5"></i>
                        <span class="d-none d-sm-inline">Ubah Email</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link position-relative rounded-0 d-flex align-items-center gap-2 bg-transparent py-3 px-4"
                        :class="{ active: activeTab === 'password' }"
                        type="button"
                        @click="activeTab = 'password'"
                    >
                        <i class="ti ti-lock fs-5"></i>
                        <span class="d-none d-sm-inline">Ubah Kata Sandi</span>
                    </button>
                </li>
            </ul>

            <!-- Tab content -->
            <div class="card-body p-4">
                <UpdateInfoForm
                    v-if="activeTab === 'info'"
                    :profile-data="profileData"
                />
                <UpdateEmailForm
                    v-else-if="activeTab === 'email'"
                    :current-email="profileData.email"
                />
                <UpdatePasswordForm
                    v-else-if="activeTab === 'password'"
                />
            </div>
        </div>

    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UpdateInfoForm     from './Partials/UpdateInfoForm.vue';
import UpdateEmailForm    from './Partials/UpdateEmailForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';

defineProps<{
    profileData: { name: string; email: string; phone: string };
}>();

const activeTab = ref<'info' | 'email' | 'password'>('info');
const flash = computed(() => usePage().props.flash as { success: string | null; error: string | null });
</script>

<style scoped>
.user-profile-tab .nav-link {
    color: var(--bs-secondary-color);
    border-bottom: 3px solid transparent;
    transition: color 0.2s, border-color 0.2s;
}

.user-profile-tab .nav-link:hover {
    color: var(--bs-primary);
    background: var(--bs-primary-bg-subtle) !important;
    border-radius: 4px 4px 0 0 !important;
}

.user-profile-tab .nav-link.active {
    color: var(--bs-primary);
    border-bottom-color: var(--bs-primary);
    font-weight: 600;
}
</style>
