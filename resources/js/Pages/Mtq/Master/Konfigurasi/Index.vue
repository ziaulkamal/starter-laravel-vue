<template>
    <AppLayout
        title="Konfigurasi Event MTQ"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Master Data' }, { label: 'Konfigurasi Event' }]"
    >
        <AppFlash />

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">Pengaturan Event MTQ</h6>
            </div>
            <div class="card-body">
                <form @submit.prevent="submitForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <AppInput
                                v-model="form.tahun"
                                type="number"
                                label="Tahun"
                                placeholder="2025"
                                :error="form.errors.tahun"
                                required
                            />
                        </div>
                        <div class="col-md-9">
                            <AppInput
                                v-model="form.tema"
                                label="Tema Event"
                                placeholder="contoh: MTQ Nasional ke-30"
                                :error="form.errors.tema"
                                required
                            />
                        </div>
                        <div class="col-md-4">
                            <AppInput
                                v-model="form.tgl_mulai_daftar"
                                type="date"
                                label="Tanggal Mulai Pendaftaran"
                                :error="form.errors.tgl_mulai_daftar"
                                required
                            />
                        </div>
                        <div class="col-md-4">
                            <AppInput
                                v-model="form.tgl_tutup_daftar"
                                type="date"
                                label="Tanggal Tutup Pendaftaran"
                                :error="form.errors.tgl_tutup_daftar"
                                required
                            />
                        </div>
                        <div class="col-md-4">
                            <AppInput
                                v-model="form.tgl_pelaksanaan"
                                type="date"
                                label="Tanggal Pelaksanaan"
                                :error="form.errors.tgl_pelaksanaan"
                                required
                            />
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span
                                v-if="form.processing"
                                class="spinner-border spinner-border-sm me-1"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            Simpan Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-danger mt-4">
            <div class="card-header bg-danger-subtle border-bottom border-danger d-flex align-items-center gap-2">
                <i class="ti ti-alert-triangle text-danger fs-5"></i>
                <h6 class="mb-0 fw-semibold text-danger">Zona Berbahaya</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div>
                        <p class="fw-semibold mb-1 small">Reset Semua Data Master M1</p>
                        <p class="text-muted mb-0 small">
                            Menghapus seluruh data <strong>Cabang, Golongan, Kriteria, Kafilah, Venue, dan Konfigurasi Event</strong>,
                            lalu mengisi ulang dengan data default. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm flex-shrink-0"
                        @click="show('resetModal')"
                    >
                        <i class="ti ti-refresh-alert me-1"></i>
                        Reset Semua
                    </button>
                </div>
            </div>
        </div>

        <!-- Reset Confirmation Modal -->
        <AppDeleteModal
            id="resetModal"
            title="Reset Semua Data Master M1?"
            confirm-label="Ya, Reset Sekarang"
            :processing="resetProcessing"
            @confirm="doReset"
        >
            Seluruh data <strong>Cabang, Golongan, Kriteria, Kafilah, Venue, dan Konfigurasi Event</strong>
            akan dihapus dan dikembalikan ke data default. Tindakan ini <strong>tidak dapat dibatalkan</strong>.
        </AppDeleteModal>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppFlash from '@/Components/UI/AppFlash.vue';
import AppInput from '@/Components/UI/Form/AppInput.vue';
import AppDeleteModal from '@/Components/UI/AppDeleteModal.vue';
import { useBootstrapModal } from '@/composables/useBootstrapModal';

// ── Types ─────────────────────────────────────────────────────────────────────

interface KonfigurasiData {
    id: number;
    tahun: number;
    tema: string;
    tgl_mulai_daftar: string;
    tgl_tutup_daftar: string;
    tgl_pelaksanaan: string;
}

interface KonfigurasiForm {
    tahun: number | string;
    tema: string;
    tgl_mulai_daftar: string;
    tgl_tutup_daftar: string;
    tgl_pelaksanaan: string;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    konfigurasi: KonfigurasiData | null;
}>();

// ── Modal ─────────────────────────────────────────────────────────────────────

const { show, hide } = useBootstrapModal(['resetModal']);

// ── Konfigurasi Form ──────────────────────────────────────────────────────────

const form = useForm<KonfigurasiForm>({
    tahun:             props.konfigurasi?.tahun            ?? new Date().getFullYear(),
    tema:              props.konfigurasi?.tema             ?? '',
    tgl_mulai_daftar:  props.konfigurasi?.tgl_mulai_daftar ?? '',
    tgl_tutup_daftar:  props.konfigurasi?.tgl_tutup_daftar ?? '',
    tgl_pelaksanaan:   props.konfigurasi?.tgl_pelaksanaan  ?? '',
});

function submitForm(): void {
    form.put('/mtq/master/konfigurasi', { preserveScroll: true });
}

// ── Reset ─────────────────────────────────────────────────────────────────────

const resetProcessing = ref<boolean>(false);

function doReset(): void {
    resetProcessing.value = true;
    router.post('/mtq/master/reset', {}, {
        preserveScroll: true,
        onFinish: () => {
            resetProcessing.value = false;
            hide('resetModal');
        },
    });
}
</script>
