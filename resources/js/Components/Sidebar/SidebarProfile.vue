<template>
    <div class="fixed-profile p-3 mx-4 mb-2 bg-secondary-subtle rounded mt-3 d-lg-none">
        <div class="hstack gap-3">
            <img v-if="userAvatar" :src="userAvatar"
                 class="rounded-circle object-fit-cover flex-shrink-0"
                 width="40" height="40" alt="user" />
            <span v-else
                  class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-semibold flex-shrink-0"
                  style="width:40px;height:40px;font-size:0.8rem">
                {{ initials(userName) }}
            </span>

            <div class="john-title overflow-hidden">
                <h6 class="mb-0 fs-4 fw-semibold text-truncate">{{ userName }}</h6>
                <span class="fs-2 text-muted">{{ userRole }}</span>
            </div>

            <button class="border-0 bg-transparent text-primary ms-auto flex-shrink-0"
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
    userName:   { type: String, default: 'User' },
    userRole:   { type: String, default: '' },
    userAvatar: { type: String, default: '' },
});

defineEmits(['logout']);

function initials(name) {
    return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
}
</script>
