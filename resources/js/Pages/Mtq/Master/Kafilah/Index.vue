<template>
    <AppLayout
        title="Master Kafilah"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Master Data' }, { label: 'Kafilah' }]"
    >
        <template #page-actions>
            <button
                v-if="selectedIds.length > 0"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1"
                @click="show('bulkDeleteKafilahModal')"
            >
                <i class="ti ti-trash fs-5"></i>
                <span>Hapus ({{ selectedIds.length }})</span>
            </button>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" @click="openCreate">
                <i class="ti ti-plus fs-5"></i>
                <span>Tambah Kafilah</span>
            </button>
        </template>

        <AppFlash />

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    v-model:selected="selectedRows"
                    :data="kafilahs"
                    :columns="columns"
                    :actions="{ edit: true, delete: true }"
                    :hover="true"
                    :show-row-numbers="true"
                    :selectable="true"
                    empty-text="Belum ada kafilah terdaftar"
                    @edit="openEdit"
                    @delete="openDelete"
                />
            </div>
        </div>

        <!-- Form Modal -->
        <AppFormModal
            id="kafilahModal"
            :title="isEditing ? 'Edit Kafilah' : 'Tambah Kafilah'"
            :processing="form.processing"
            :submit-label="isEditing ? 'Simpan Perubahan' : 'Tambah'"
            @submit="submitForm"
        >
            <AppSelect2
                v-model="form.kode_wilayah"
                :options="wilayahAceh"
                label="Kabupaten/Kota"
                placeholder="Pilih kabupaten/kota..."
                search-placeholder="Cari wilayah..."
                :error="form.errors.kode_wilayah || form.errors.nama_kabupaten"
                required
                class="mb-3"
                @change="onWilayahChange"
            />
            <AppInput
                v-model="form.koordinator_nama"
                label="Nama Koordinator"
                placeholder="Opsional"
                :error="form.errors.koordinator_nama"
                class="mb-3"
            />
            <AppInput
                v-model="form.koordinator_kontak"
                label="Kontak Koordinator"
                placeholder="contoh: 08xxxxxxxxxx"
                :error="form.errors.koordinator_kontak"
            />
        </AppFormModal>

        <!-- Delete Single Modal -->
        <AppDeleteModal
            id="deleteKafilahModal"
            :processing="deleteProcessing"
            @confirm="doDelete"
        >
            Kafilah <strong>{{ deleteTarget?.nama_kabupaten }}</strong> akan dihapus permanen.
        </AppDeleteModal>

        <!-- Bulk Delete Modal -->
        <AppDeleteModal
            id="bulkDeleteKafilahModal"
            title="Hapus Kafilah Terpilih?"
            confirm-label="Ya, Hapus Semua"
            :processing="bulkProcessing"
            @confirm="doBulkDelete"
        >
            <strong>{{ selectedIds.length }} kafilah</strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
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
import AppSelect2 from '@/Components/UI/Form/AppSelect2.vue';
import { useBootstrapModal } from '@/composables/useBootstrapModal';
import type { TableColumn } from '@/Components/UI/AppTable.vue';
import type { Select2Option } from '@/Components/UI/Form/AppSelect2.vue';

// ── Types ─────────────────────────────────────────────────────────────────────

interface KafilahRow {
    id: number;
    nama_kabupaten: string;
    kode_wilayah: string;
    koordinator_nama: string | null;
    koordinator_kontak: string | null;
}

interface KafilahForm {
    nama_kabupaten: string;
    kode_wilayah: string;
    koordinator_nama: string;
    koordinator_kontak: string;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    kafilahs: KafilahRow[];
    wilayahAceh: Select2Option[];
}>();

// ── Table ─────────────────────────────────────────────────────────────────────

const columns: TableColumn[] = [
    { key: 'kode_wilayah',       label: 'Kode' },
    { key: 'nama_kabupaten',     label: 'Kabupaten/Kota' },
    { key: 'koordinator_nama',   label: 'Koordinator' },
    { key: 'koordinator_kontak', label: 'Kontak', hidden: true },
];

// ── Modal ─────────────────────────────────────────────────────────────────────

const { show, hide } = useBootstrapModal(['kafilahModal', 'deleteKafilahModal', 'bulkDeleteKafilahModal']);

// ── Selection ─────────────────────────────────────────────────────────────────

const selectedRows = ref<KafilahRow[]>([]);
const selectedIds  = computed(() => selectedRows.value.map(r => r.id));

// ── Form ──────────────────────────────────────────────────────────────────────

const isEditing = ref<boolean>(false);
const editingId = ref<number | null>(null);

const form = useForm<KafilahForm>({
    nama_kabupaten:     '',
    kode_wilayah:       '',
    koordinator_nama:   '',
    koordinator_kontak: '',
});

function onWilayahChange(opt: Select2Option | null): void {
    form.nama_kabupaten = opt?.label ?? '';
}

function resetForm(): void {
    form.nama_kabupaten     = '';
    form.kode_wilayah       = '';
    form.koordinator_nama   = '';
    form.koordinator_kontak = '';
    form.clearErrors();
}

function openCreate(): void {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    show('kafilahModal');
}

function openEdit(row: Record<string, unknown>): void {
    const k = row as unknown as KafilahRow;
    isEditing.value         = true;
    editingId.value         = k.id;
    form.nama_kabupaten     = k.nama_kabupaten;
    form.kode_wilayah       = k.kode_wilayah;
    form.koordinator_nama   = k.koordinator_nama ?? '';
    form.koordinator_kontak = k.koordinator_kontak ?? '';
    form.clearErrors();
    show('kafilahModal');
}

function submitForm(): void {
    const opts = { preserveScroll: true, onSuccess: () => hide('kafilahModal') };

    if (isEditing.value && editingId.value) {
        form.put(`/mtq/master/kafilah/${editingId.value}`, opts);
    } else {
        form.post('/mtq/master/kafilah', { ...opts, onSuccess: () => { hide('kafilahModal'); resetForm(); } });
    }
}

// ── Delete single ─────────────────────────────────────────────────────────────

const deleteTarget     = ref<KafilahRow | null>(null);
const deleteProcessing = ref<boolean>(false);

function openDelete(row: Record<string, unknown>): void {
    deleteTarget.value = row as unknown as KafilahRow;
    show('deleteKafilahModal');
}

function doDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/mtq/master/kafilah/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            hide('deleteKafilahModal');
        },
    });
}

// ── Bulk delete ───────────────────────────────────────────────────────────────

const bulkProcessing = ref<boolean>(false);

function doBulkDelete(): void {
    bulkProcessing.value = true;
    router.post('/mtq/master/kafilah/bulk-destroy', { ids: selectedIds.value }, {
        preserveScroll: true,
        onFinish: () => {
            bulkProcessing.value = false;
            hide('bulkDeleteKafilahModal');
        },
    });
}
</script>
