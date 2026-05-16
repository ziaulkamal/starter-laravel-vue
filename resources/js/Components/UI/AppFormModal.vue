<template>
    <div
        class="modal fade"
        :id="id"
        tabindex="-1"
        :aria-labelledby="`${id}Label`"
        aria-hidden="true"
    >
        <div :class="['modal-dialog modal-dialog-centered modal-dialog-scrollable', sizeClass]">
            <form class="modal-content" @submit.prevent="emit('submit')">
                <div class="modal-header">
                    <h5 class="modal-title" :id="`${id}Label`">{{ title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <slot />
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" :disabled="processing">
                        <span
                            v-if="processing"
                            class="spinner-border spinner-border-sm me-1"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        {{ submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    id: string;
    title: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    processing?: boolean;
    submitLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    processing: false,
    submitLabel: 'Simpan',
});

const emit = defineEmits<{
    submit: [];
}>();

const sizeClass = computed(() => ({
    'modal-sm': props.size === 'sm',
    'modal-lg': props.size === 'lg',
    'modal-xl': props.size === 'xl',
}));
</script>
