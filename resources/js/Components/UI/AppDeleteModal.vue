<template>
    <div class="modal fade" :id="id" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4 px-4">
                    <i class="ti ti-alert-circle text-danger mb-2" style="font-size: 2.5rem;"></i>
                    <h6 class="fw-semibold mt-2 mb-1">{{ title }}</h6>
                    <p class="text-muted mb-0 small">
                        <slot />
                    </p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4 gap-2">
                    <button
                        type="button"
                        class="btn btn-light btn-sm px-4"
                        data-bs-dismiss="modal"
                    >Batal</button>
                    <button
                        type="button"
                        class="btn btn-danger btn-sm px-4"
                        :disabled="processing"
                        @click="emit('confirm')"
                    >
                        <span
                            v-if="processing"
                            class="spinner-border spinner-border-sm me-1"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    id: string;
    title?: string;
    processing?: boolean;
    confirmLabel?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Hapus Data?',
    processing: false,
    confirmLabel: 'Hapus',
});

const emit = defineEmits<{
    confirm: [];
}>();
</script>
