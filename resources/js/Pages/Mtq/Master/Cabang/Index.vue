<template>
    <AppLayout
        title="Master Cabang"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Master Data' }, { label: 'Cabang' }]"
    >
        <template #page-actions>
            <button
                v-if="selectedIds.length > 0"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1"
                @click="show('bulkDeleteCabangModal')"
            >
                <i class="ti ti-trash fs-5"></i>
                <span>Hapus ({{ selectedIds.length }})</span>
            </button>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" @click="openCreate">
                <i class="ti ti-plus fs-5"></i>
                <span>Tambah Cabang</span>
            </button>
        </template>

        <AppFlash />

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    v-model:selected="selectedRows"
                    :data="cabangs"
                    :columns="columns"
                    :actions="{ edit: true, delete: true }"
                    :action-permissions="{ edit: 'peserta.edit', delete: 'peserta.delete' }"
                    :hover="true"
                    :show-row-numbers="true"
                    :selectable="true"
                    empty-text="Belum ada cabang lomba"
                    @edit="openEdit"
                    @delete="openDelete"
                >
                    <template #cell-is_active="{ value }">
                        <span :class="['badge rounded-pill', value ? 'text-bg-success' : 'text-bg-secondary']">
                            {{ value ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </template>
                </AppTable>
            </div>
        </div>

        <!-- Form Modal -->
        <AppFormModal
            id="cabangModal"
            :title="isEditing ? 'Edit Cabang' : 'Tambah Cabang'"
            :processing="form.processing"
            :submit-label="isEditing ? 'Simpan Perubahan' : 'Tambah'"
            @submit="submitForm"
        >
            <AppInput
                v-model="form.nama"
                label="Nama Cabang"
                placeholder="contoh: Tilawah Al-Qur'an"
                :error="form.errors.nama"
                required
                class="mb-3"
            />
            <AppInput
                v-model="form.deskripsi"
                label="Deskripsi"
                placeholder="Keterangan singkat cabang lomba"
                :error="form.errors.deskripsi"
                class="mb-3"
            />
            <AppToggle
                v-model="form.is_active"
                label="Cabang aktif"
                :error="form.errors.is_active"
            />
        </AppFormModal>

        <!-- Delete Single Modal -->
        <AppDeleteModal
            id="deleteCabangModal"
            :processing="deleteProcessing"
            @confirm="doDelete"
        >
            <strong>{{ deleteTarget?.nama }}</strong> akan dihapus beserta seluruh golongan dan kriteria terkait.
        </AppDeleteModal>

        <!-- Bulk Delete Modal -->
        <AppDeleteModal
            id="bulkDeleteCabangModal"
            title="Hapus Cabang Terpilih?"
            confirm-label="Ya, Hapus Semua"
            :processing="bulkProcessing"
            @confirm="doBulkDelete"
        >
            <strong>{{ selectedIds.length }} cabang</strong> akan dihapus beserta seluruh golongan dan kriteria terkait. Tindakan ini tidak dapat dibatalkan.
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
import AppToggle from '@/Components/UI/Form/AppToggle.vue';
import { useBootstrapModal } from '@/composables/useBootstrapModal';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

// ── Types ─────────────────────────────────────────────────────────────────────

interface CabangRow {
    id: number;
    nama: string;
    deskripsi: string | null;
    is_active: boolean;
}

interface CabangForm {
    nama: string;
    deskripsi: string;
    is_active: boolean;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    cabangs: CabangRow[];
}>();

// ── Table ─────────────────────────────────────────────────────────────────────

const columns: TableColumn[] = [
    { key: 'nama',      label: 'Nama Cabang' },
    { key: 'deskripsi', label: 'Deskripsi' },
    { key: 'is_active', label: 'Status' },
];

// ── Modal ─────────────────────────────────────────────────────────────────────

const { show, hide } = useBootstrapModal(['cabangModal', 'deleteCabangModal', 'bulkDeleteCabangModal']);

// ── Selection ─────────────────────────────────────────────────────────────────

const selectedRows = ref<CabangRow[]>([]);
const selectedIds  = computed(() => selectedRows.value.map(r => r.id));

// ── Form ──────────────────────────────────────────────────────────────────────

const isEditing = ref<boolean>(false);
const editingId = ref<number | null>(null);

const form = useForm<CabangForm>({
    nama: '',
    deskripsi: '',
    is_active: true,
});

function resetForm(): void {
    form.nama      = '';
    form.deskripsi = '';
    form.is_active = true;
    form.clearErrors();
}

function openCreate(): void {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    show('cabangModal');
}

function openEdit(row: Record<string, unknown>): void {
    const cabang = row as unknown as CabangRow;
    isEditing.value = true;
    editingId.value  = cabang.id;
    form.nama        = cabang.nama;
    form.deskripsi   = cabang.deskripsi ?? '';
    form.is_active   = cabang.is_active;
    form.clearErrors();
    show('cabangModal');
}

function submitForm(): void {
    const opts = { preserveScroll: true, onSuccess: () => hide('cabangModal') };

    if (isEditing.value && editingId.value) {
        form.put(`/mtq/master/cabang/${editingId.value}`, opts);
    } else {
        form.post('/mtq/master/cabang', { ...opts, onSuccess: () => { hide('cabangModal'); resetForm(); } });
    }
}

// ── Delete single ─────────────────────────────────────────────────────────────

const deleteTarget     = ref<CabangRow | null>(null);
const deleteProcessing = ref<boolean>(false);

function openDelete(row: Record<string, unknown>): void {
    deleteTarget.value = row as unknown as CabangRow;
    show('deleteCabangModal');
}

function doDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/mtq/master/cabang/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            hide('deleteCabangModal');
        },
    });
}

// ── Bulk delete ───────────────────────────────────────────────────────────────

const bulkProcessing = ref<boolean>(false);

function doBulkDelete(): void {
    bulkProcessing.value = true;
    router.post('/mtq/master/cabang/bulk-destroy', { ids: selectedIds.value }, {
        preserveScroll: true,
        onFinish: () => {
            bulkProcessing.value = false;
            hide('bulkDeleteCabangModal');
        },
    });
}
</script>
