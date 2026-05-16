<template>
    <AppLayout
        title="Master Venue"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Master Data' }, { label: 'Venue' }]"
    >
        <template #page-actions>
            <button
                v-if="selectedIds.length > 0"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1"
                @click="show('bulkDeleteVenueModal')"
            >
                <i class="ti ti-trash fs-5"></i>
                <span>Hapus ({{ selectedIds.length }})</span>
            </button>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" @click="openCreate">
                <i class="ti ti-plus fs-5"></i>
                <span>Tambah Venue</span>
            </button>
        </template>

        <AppFlash />

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    v-model:selected="selectedRows"
                    :data="venues"
                    :columns="columns"
                    :actions="{ edit: true, delete: true }"
                    :hover="true"
                    :show-row-numbers="true"
                    :selectable="true"
                    empty-text="Belum ada venue terdaftar"
                    @edit="openEdit"
                    @delete="openDelete"
                />
            </div>
        </div>

        <!-- Form Modal -->
        <AppFormModal
            id="venueModal"
            size="lg"
            :title="isEditing ? 'Edit Venue' : 'Tambah Venue'"
            :processing="form.processing"
            :submit-label="isEditing ? 'Simpan Perubahan' : 'Tambah'"
            @submit="submitForm"
        >
            <AppInput
                v-model="form.nama"
                label="Nama Venue"
                placeholder="contoh: Gedung Serba Guna Blangpidie"
                :error="form.errors.nama"
                required
                class="mb-3"
            />
            <AppInput
                v-model="form.alamat"
                label="Alamat"
                placeholder="Alamat lengkap venue"
                :error="form.errors.alamat"
                required
                class="mb-3"
            />
            <AppInput
                v-model="form.kapasitas"
                type="number"
                label="Kapasitas (orang)"
                placeholder="Opsional"
                :error="form.errors.kapasitas"
                class="mb-3"
            />
            <AppMapPicker
                :lat="form.lat"
                :lng="form.lng"
                label="Lokasi Venue"
                hint="Klik pada peta atau drag pin untuk menentukan koordinat"
                :error="form.errors.lat ?? form.errors.lng"
                @update:lat="(v: number | null) => form.lat = v ?? ''"
                @update:lng="(v: number | null) => form.lng = v ?? ''"
            />
        </AppFormModal>

        <!-- Delete Single Modal -->
        <AppDeleteModal
            id="deleteVenueModal"
            :processing="deleteProcessing"
            @confirm="doDelete"
        >
            Venue <strong>{{ deleteTarget?.nama }}</strong> akan dihapus permanen.
        </AppDeleteModal>

        <!-- Bulk Delete Modal -->
        <AppDeleteModal
            id="bulkDeleteVenueModal"
            title="Hapus Venue Terpilih?"
            confirm-label="Ya, Hapus Semua"
            :processing="bulkProcessing"
            @confirm="doBulkDelete"
        >
            <strong>{{ selectedIds.length }} venue</strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
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
import AppMapPicker from '@/Components/UI/Form/AppMapPicker.vue';
import { useBootstrapModal } from '@/composables/useBootstrapModal';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

// ── Types ─────────────────────────────────────────────────────────────────────

interface VenueRow {
    id: number;
    nama: string;
    alamat: string;
    lat: string | null;
    lng: string | null;
    kapasitas: number | null;
}

interface VenueForm {
    nama: string;
    alamat: string;
    lat: number | string;
    lng: number | string;
    kapasitas: number | string;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    venues: VenueRow[];
}>();

// ── Table ─────────────────────────────────────────────────────────────────────

const columns: TableColumn[] = [
    { key: 'nama',      label: 'Nama Venue' },
    { key: 'alamat',    label: 'Alamat' },
    { key: 'kapasitas', label: 'Kapasitas' },
    { key: 'lat',       label: 'Latitude',  hidden: true },
    { key: 'lng',       label: 'Longitude', hidden: true },
];

// ── Modal ─────────────────────────────────────────────────────────────────────

const { show, hide } = useBootstrapModal(['venueModal', 'deleteVenueModal', 'bulkDeleteVenueModal']);

// ── Selection ─────────────────────────────────────────────────────────────────

const selectedRows = ref<VenueRow[]>([]);
const selectedIds  = computed(() => selectedRows.value.map(r => r.id));

// ── Form ──────────────────────────────────────────────────────────────────────

const isEditing = ref<boolean>(false);
const editingId = ref<number | null>(null);

const form = useForm<VenueForm>({
    nama:      '',
    alamat:    '',
    lat:       '',
    lng:       '',
    kapasitas: '',
});

function resetForm(): void {
    form.nama      = '';
    form.alamat    = '';
    form.lat       = '';
    form.lng       = '';
    form.kapasitas = '';
    form.clearErrors();
}

function openCreate(): void {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    show('venueModal');
}

function openEdit(row: Record<string, unknown>): void {
    const v = row as unknown as VenueRow;
    isEditing.value  = true;
    editingId.value  = v.id;
    form.nama        = v.nama;
    form.alamat      = v.alamat;
    form.lat         = v.lat ?? '';
    form.lng         = v.lng ?? '';
    form.kapasitas   = v.kapasitas ?? '';
    form.clearErrors();
    show('venueModal');
}

function submitForm(): void {
    const opts = { preserveScroll: true, onSuccess: () => hide('venueModal') };

    if (isEditing.value && editingId.value) {
        form.put(`/mtq/master/venue/${editingId.value}`, opts);
    } else {
        form.post('/mtq/master/venue', { ...opts, onSuccess: () => { hide('venueModal'); resetForm(); } });
    }
}

// ── Delete single ─────────────────────────────────────────────────────────────

const deleteTarget     = ref<VenueRow | null>(null);
const deleteProcessing = ref<boolean>(false);

function openDelete(row: Record<string, unknown>): void {
    deleteTarget.value = row as unknown as VenueRow;
    show('deleteVenueModal');
}

function doDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/mtq/master/venue/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            hide('deleteVenueModal');
        },
    });
}

// ── Bulk delete ───────────────────────────────────────────────────────────────

const bulkProcessing = ref<boolean>(false);

function doBulkDelete(): void {
    bulkProcessing.value = true;
    router.post('/mtq/master/venue/bulk-destroy', { ids: selectedIds.value }, {
        preserveScroll: true,
        onFinish: () => {
            bulkProcessing.value = false;
            hide('bulkDeleteVenueModal');
        },
    });
}
</script>
