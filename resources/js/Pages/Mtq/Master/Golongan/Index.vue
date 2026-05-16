<template>
    <AppLayout
        title="Master Golongan"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Master Data' }, { label: 'Golongan' }]"
    >
        <template #page-actions>
            <button
                v-if="selectedIds.length > 0"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1"
                @click="show('bulkDeleteGolonganModal')"
            >
                <i class="ti ti-trash fs-5"></i>
                <span>Hapus ({{ selectedIds.length }})</span>
            </button>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" @click="openCreate">
                <i class="ti ti-plus fs-5"></i>
                <span>Tambah Golongan</span>
            </button>
        </template>

        <AppFlash />

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    v-model:selected="selectedRows"
                    :data="golongans"
                    :columns="columns"
                    :actions="{ edit: true, delete: true }"
                    :hover="true"
                    :show-row-numbers="true"
                    :selectable="true"
                    empty-text="Belum ada golongan"
                    @edit="openEdit"
                    @delete="openDelete"
                >
                    <template #cell-jenis_kelamin="{ value }">
                        <span class="badge text-bg-info rounded-pill">{{ jenisKelaminLabel(value as string) }}</span>
                    </template>
                </AppTable>
            </div>
        </div>

        <!-- Form Modal -->
        <AppFormModal
            id="golonganModal"
            :title="isEditing ? 'Edit Golongan' : 'Tambah Golongan'"
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
                label="Nama Golongan"
                placeholder="contoh: Golongan Dewasa Putra"
                :error="form.errors.nama"
                required
                class="mb-3"
            />
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <AppInput
                        v-model="form.min_usia"
                        type="number"
                        label="Min. Usia"
                        placeholder="0"
                        :error="form.errors.min_usia"
                    />
                </div>
                <div class="col-6">
                    <AppInput
                        v-model="form.max_usia"
                        type="number"
                        label="Maks. Usia"
                        placeholder="99"
                        :error="form.errors.max_usia"
                    />
                </div>
            </div>
            <AppSelect
                v-model="form.jenis_kelamin"
                label="Jenis Kelamin"
                placeholder="— Pilih —"
                :options="jenisKelaminOptions"
                :error="form.errors.jenis_kelamin"
                required
            />
        </AppFormModal>

        <!-- Delete Single Modal -->
        <AppDeleteModal
            id="deleteGolonganModal"
            :processing="deleteProcessing"
            @confirm="doDelete"
        >
            Golongan <strong>{{ deleteTarget?.nama }}</strong> akan dihapus permanen.
        </AppDeleteModal>

        <!-- Bulk Delete Modal -->
        <AppDeleteModal
            id="bulkDeleteGolonganModal"
            title="Hapus Golongan Terpilih?"
            confirm-label="Ya, Hapus Semua"
            :processing="bulkProcessing"
            @confirm="doBulkDelete"
        >
            <strong>{{ selectedIds.length }} golongan</strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
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

interface GolonganRow {
    id: number;
    cabang_id: number;
    cabang_nama: string;
    nama: string;
    min_usia: number | null;
    max_usia: number | null;
    jenis_kelamin: 'L' | 'P' | 'LK';
}

interface CabangOption {
    id: number;
    nama: string;
}

interface GolonganForm {
    cabang_id: number | string;
    nama: string;
    min_usia: number | string;
    max_usia: number | string;
    jenis_kelamin: string;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    golongans: GolonganRow[];
    cabangs: CabangOption[];
}>();

// ── Table ─────────────────────────────────────────────────────────────────────

const columns: TableColumn[] = [
    { key: 'cabang_nama',   label: 'Cabang' },
    { key: 'nama',          label: 'Nama Golongan' },
    { key: 'min_usia',      label: 'Min. Usia' },
    { key: 'max_usia',      label: 'Maks. Usia' },
    { key: 'jenis_kelamin', label: 'Jenis Kelamin' },
];

// ── Options ───────────────────────────────────────────────────────────────────

const cabangOptions = computed(() =>
    props.cabangs.map(c => ({ label: c.nama, value: c.id }))
);

const jenisKelaminOptions = [
    { label: 'Laki-Laki',             value: 'L'  },
    { label: 'Perempuan',             value: 'P'  },
    { label: 'Laki-Laki & Perempuan', value: 'LK' },
];

function jenisKelaminLabel(value: string): string {
    return jenisKelaminOptions.find(o => o.value === value)?.label ?? value;
}

// ── Modal ─────────────────────────────────────────────────────────────────────

const { show, hide } = useBootstrapModal(['golonganModal', 'deleteGolonganModal', 'bulkDeleteGolonganModal']);

// ── Selection ─────────────────────────────────────────────────────────────────

const selectedRows = ref<GolonganRow[]>([]);
const selectedIds  = computed(() => selectedRows.value.map(r => r.id));

// ── Form ──────────────────────────────────────────────────────────────────────

const isEditing = ref<boolean>(false);
const editingId = ref<number | null>(null);

const form = useForm<GolonganForm>({
    cabang_id:     '',
    nama:          '',
    min_usia:      '',
    max_usia:      '',
    jenis_kelamin: '',
});

function resetForm(): void {
    form.cabang_id     = '';
    form.nama          = '';
    form.min_usia      = '';
    form.max_usia      = '';
    form.jenis_kelamin = '';
    form.clearErrors();
}

function openCreate(): void {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    show('golonganModal');
}

function openEdit(row: Record<string, unknown>): void {
    const g = row as unknown as GolonganRow;
    isEditing.value    = true;
    editingId.value    = g.id;
    form.cabang_id     = g.cabang_id;
    form.nama          = g.nama;
    form.min_usia      = g.min_usia ?? '';
    form.max_usia      = g.max_usia ?? '';
    form.jenis_kelamin = g.jenis_kelamin;
    form.clearErrors();
    show('golonganModal');
}

function submitForm(): void {
    const opts = { preserveScroll: true, onSuccess: () => hide('golonganModal') };

    if (isEditing.value && editingId.value) {
        form.put(`/mtq/master/golongan/${editingId.value}`, opts);
    } else {
        form.post('/mtq/master/golongan', { ...opts, onSuccess: () => { hide('golonganModal'); resetForm(); } });
    }
}

// ── Delete single ─────────────────────────────────────────────────────────────

const deleteTarget     = ref<GolonganRow | null>(null);
const deleteProcessing = ref<boolean>(false);

function openDelete(row: Record<string, unknown>): void {
    deleteTarget.value = row as unknown as GolonganRow;
    show('deleteGolonganModal');
}

function doDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/mtq/master/golongan/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            hide('deleteGolonganModal');
        },
    });
}

// ── Bulk delete ───────────────────────────────────────────────────────────────

const bulkProcessing = ref<boolean>(false);

function doBulkDelete(): void {
    bulkProcessing.value = true;
    router.post('/mtq/master/golongan/bulk-destroy', { ids: selectedIds.value }, {
        preserveScroll: true,
        onFinish: () => {
            bulkProcessing.value = false;
            hide('bulkDeleteGolonganModal');
        },
    });
}
</script>
