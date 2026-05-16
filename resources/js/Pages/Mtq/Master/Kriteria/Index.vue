<template>
    <AppLayout
        title="Master Kriteria"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Master Data' }, { label: 'Kriteria' }]"
    >
        <template #page-actions>
            <button
                v-if="selectedIds.length > 0"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1"
                @click="show('bulkDeleteKriteriaModal')"
            >
                <i class="ti ti-trash fs-5"></i>
                <span>Hapus ({{ selectedIds.length }})</span>
            </button>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" @click="openCreate">
                <i class="ti ti-plus fs-5"></i>
                <span>Tambah Kriteria</span>
            </button>
        </template>

        <AppFlash />

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    v-model:selected="selectedRows"
                    :data="kriterias"
                    :columns="columns"
                    :actions="{ edit: true, delete: true }"
                    :hover="true"
                    :show-row-numbers="true"
                    :selectable="true"
                    empty-text="Belum ada kriteria penilaian"
                    @edit="openEdit"
                    @delete="openDelete"
                >
                    <template #cell-bobot="{ value }">
                        <span class="badge text-bg-primary rounded-pill">{{ value }}%</span>
                    </template>
                </AppTable>
            </div>
        </div>

        <!-- Form Modal -->
        <AppFormModal
            id="kriteriaModal"
            :title="isEditing ? 'Edit Kriteria' : 'Tambah Kriteria'"
            :processing="form.processing"
            :submit-label="isEditing ? 'Simpan Perubahan' : 'Tambah'"
            @submit="submitForm"
        >
            <AppSelect
                v-model="form.cabang_id"
                label="Cabang Lomba"
                placeholder="— Pilih Cabang —"
                :options="cabangOptions"
                :error="form.errors.cabang_id"
                required
                class="mb-3"
            />
            <AppInput
                v-model="form.nama"
                label="Nama Kriteria"
                placeholder="contoh: Tajwid"
                :error="form.errors.nama"
                required
                class="mb-3"
            />
            <AppInput
                v-model="form.bobot"
                type="number"
                label="Bobot (%)"
                placeholder="contoh: 30"
                hint="Total bobot per cabang harus = 100%"
                :error="form.errors.bobot"
                append-text="%"
                required
                class="mb-3"
            />
            <div class="row g-3">
                <div class="col-6">
                    <AppInput
                        v-model="form.min_nilai"
                        type="number"
                        label="Nilai Minimum"
                        placeholder="0"
                        :error="form.errors.min_nilai"
                        required
                    />
                </div>
                <div class="col-6">
                    <AppInput
                        v-model="form.max_nilai"
                        type="number"
                        label="Nilai Maksimum"
                        placeholder="100"
                        :error="form.errors.max_nilai"
                        required
                    />
                </div>
            </div>
        </AppFormModal>

        <!-- Delete Single Modal -->
        <AppDeleteModal
            id="deleteKriteriaModal"
            :processing="deleteProcessing"
            @confirm="doDelete"
        >
            Kriteria <strong>{{ deleteTarget?.nama }}</strong> akan dihapus permanen.
        </AppDeleteModal>

        <!-- Bulk Delete Modal -->
        <AppDeleteModal
            id="bulkDeleteKriteriaModal"
            title="Hapus Kriteria Terpilih?"
            confirm-label="Ya, Hapus Semua"
            :processing="bulkProcessing"
            @confirm="doBulkDelete"
        >
            <strong>{{ selectedIds.length }} kriteria</strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
        </AppDeleteModal>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppFlash from '@/Components/UI/AppFlash.vue';
import AppFormModal from '@/Components/UI/AppFormModal.vue';
import AppDeleteModal from '@/Components/UI/AppDeleteModal.vue';
import AppInput from '@/Components/UI/Form/AppInput.vue';
import AppSelect from '@/Components/UI/Form/AppSelect.vue';
import { useBootstrapModal } from '@/composables/useBootstrapModal';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

// ── Types ─────────────────────────────────────────────────────────────────────

interface KriteriaRow {
    id: number;
    cabang_id: number;
    cabang_nama: string;
    nama: string;
    bobot: string;
    min_nilai: string;
    max_nilai: string;
}

interface CabangOption {
    id: number;
    nama: string;
}

interface KriteriaForm {
    cabang_id: number | string;
    nama: string;
    bobot: number | string;
    min_nilai: number | string;
    max_nilai: number | string;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    kriterias: KriteriaRow[];
    cabangs: CabangOption[];
}>();

// ── Table ─────────────────────────────────────────────────────────────────────

const columns: TableColumn[] = [
    { key: 'cabang_nama', label: 'Cabang' },
    { key: 'nama',        label: 'Nama Kriteria' },
    { key: 'bobot',       label: 'Bobot' },
    { key: 'min_nilai',   label: 'Nilai Min' },
    { key: 'max_nilai',   label: 'Nilai Maks' },
];

// ── Options ───────────────────────────────────────────────────────────────────

const cabangOptions = computed(() =>
    props.cabangs.map(c => ({ label: c.nama, value: c.id }))
);

// ── Modal ─────────────────────────────────────────────────────────────────────

const { show, hide } = useBootstrapModal(['kriteriaModal', 'deleteKriteriaModal', 'bulkDeleteKriteriaModal']);

// ── Selection ─────────────────────────────────────────────────────────────────

const selectedRows = ref<KriteriaRow[]>([]);
const selectedIds  = computed(() => selectedRows.value.map(r => r.id));

// ── Form ──────────────────────────────────────────────────────────────────────

const isEditing = ref<boolean>(false);
const editingId = ref<number | null>(null);

const form = useForm<KriteriaForm>({
    cabang_id: '',
    nama:      '',
    bobot:     '',
    min_nilai: 0,
    max_nilai: 100,
});

function resetForm(): void {
    form.cabang_id = '';
    form.nama      = '';
    form.bobot     = '';
    form.min_nilai = 0;
    form.max_nilai = 100;
    form.clearErrors();
}

function openCreate(): void {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    show('kriteriaModal');
}

function openEdit(row: Record<string, unknown>): void {
    const k = row as unknown as KriteriaRow;
    isEditing.value  = true;
    editingId.value  = k.id;
    form.cabang_id   = k.cabang_id;
    form.nama        = k.nama;
    form.bobot       = k.bobot;
    form.min_nilai   = k.min_nilai;
    form.max_nilai   = k.max_nilai;
    form.clearErrors();
    show('kriteriaModal');
}

function submitForm(): void {
    const opts = { preserveScroll: true, onSuccess: () => hide('kriteriaModal') };

    if (isEditing.value && editingId.value) {
        form.put(`/mtq/master/kriteria/${editingId.value}`, opts);
    } else {
        form.post('/mtq/master/kriteria', { ...opts, onSuccess: () => { hide('kriteriaModal'); resetForm(); } });
    }
}

// ── Delete single ─────────────────────────────────────────────────────────────

const deleteTarget     = ref<KriteriaRow | null>(null);
const deleteProcessing = ref<boolean>(false);

function openDelete(row: Record<string, unknown>): void {
    deleteTarget.value = row as unknown as KriteriaRow;
    show('deleteKriteriaModal');
}

function doDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/mtq/master/kriteria/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            hide('deleteKriteriaModal');
        },
    });
}

// ── Bulk delete ───────────────────────────────────────────────────────────────

const bulkProcessing = ref<boolean>(false);

function doBulkDelete(): void {
    bulkProcessing.value = true;
    router.post('/mtq/master/kriteria/bulk-destroy', { ids: selectedIds.value }, {
        preserveScroll: true,
        onFinish: () => {
            bulkProcessing.value = false;
            hide('bulkDeleteKriteriaModal');
        },
    });
}
</script>
