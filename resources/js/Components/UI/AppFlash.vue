<template>
    <div
        v-if="flash.success"
        class="alert alert-success alert-dismissible fade show mb-3"
        role="alert"
    >
        <i class="ti ti-circle-check me-2"></i>{{ flash.success }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
    <div
        v-if="flash.error"
        class="alert alert-danger alert-dismissible fade show mb-3"
        role="alert"
    >
        <i class="ti ti-alert-circle me-2"></i>{{ flash.error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Flash {
    success?: string | null;
    error?: string | null;
}

const page = usePage();
const flash = computed<Flash>(() => (page.props.flash as Flash) ?? {});
</script>
