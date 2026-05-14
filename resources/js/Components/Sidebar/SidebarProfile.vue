<template>
    <div class="fixed-profile p-3 mx-4 mb-2 bg-secondary-subtle rounded mt-3">
        <div class="hstack gap-3">
            <div class="john-img">
                <img src="@images/profile/user-1.jpg"
                     class="rounded-circle" width="40" height="40" alt="user" />
            </div>
            <div class="john-title">
                <h6 class="mb-0 fs-4 fw-semibold">{{ userName }}</h6>
                <span class="fs-2">{{ userRole }}</span>
            </div>
            <button class="border-0 bg-transparent text-primary ms-auto"
                    type="button"
                    title="Logout"
                    @click="$emit('logout')">
                <i class="ti ti-power fs-6"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    userName: { type: String, default: 'User' },
    userRole: { type: String, default: '' },
});

defineEmits(['logout']);
</script>
