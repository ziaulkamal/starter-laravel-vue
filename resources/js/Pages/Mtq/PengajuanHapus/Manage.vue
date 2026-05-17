<template>
    <AppLayout
        title="Manajemen Pengajuan Hapus"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Manajemen Pengajuan Hapus' }]"
    >
        <AppFlash />

        <!-- Filter tabs -->
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <button
                v-for="tab in statusTabs"
                :key="tab.value"
                :class="['btn btn-sm', activeTab === tab.value ? `btn-${tab.color}` : 'btn-outline-secondary']"
                @click="activeTab = tab.value"
            >
                {{ tab.label }}
                <span
                    class="badge ms-1"
                    :class="activeTab === tab.value ? 'bg-white text-dark' : 'text-bg-secondary'"
                >{{ tab.count }}</span>
            </button>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    :data="filteredPengajuans"
                    :columns="columns"
                    :hover="true"
                    :show-row-numbers="true"
                    empty-text="Belum ada pengajuan hapus"
                >
                    <template #cell-status="{ value }">
                        <span :class="['badge rounded-pill', statusBadgeClass(value as string)]">
                            {{ statusLabel(value as string) }}
                        </span>
                    </template>

                    <template #cell-catatan_admin="{ value, row }">
                        <div v-if="value" class="small">
                            <div class="text-muted">{{ value }}</div>
                            <div v-if="(row as PengajuanRow).reviewer_name" class="text-muted fst-italic">
                                — {{ (row as PengajuanRow).reviewer_name }}, {{ (row as PengajuanRow).reviewed_at }}
                            </div>
                        </div>
                        <span v-else class="text-muted small">—</span>
                    </template>

                    <template #cell-aksi="{ row }">
                        <div class="d-flex align-items-center gap-1">
                            <template v-if="(row as PengajuanRow).status === 'menunggu'">
                                <button
                                    type="button"
                                    class="btn btn-sm bg-success-subtle text-success app-action-btn"
                                    title="Setujui & hapus peserta"
                                    @click="openApprove(row as PengajuanRow)"
                                >
                                    <i class="ti ti-check fs-5"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm bg-danger-subtle text-danger app-action-btn"
                                    title="Tolak pengajuan hapus"
                                    @click="openReject(row as PengajuanRow)"
                                >
                                    <i class="ti ti-x fs-5"></i>
                                </button>
                            </template>
                            <span v-else class="text-muted small">—</span>
                        </div>
                    </template>
                </AppTable>
            </div>
        </div>

        <!-- ── Approve Modal ──────────────────────────────────────────── -->
        <AppFormModal
            id="approveHapusModal"
            size="sm"
            title="Setujui Pengajuan Hapus"
            :processing="approveForm.processing"
            submit-label="Setujui & Hapus Peserta"
            submit-class="btn-danger"
            @submit="doApprove"
        >
            <div class="alert alert-danger py-2 small mb-3">
                <i class="ti ti-alert-triangle me-1"></i>
                Tindakan ini akan menghapus peserta secara permanen dan tidak dapat dibatalkan.
            </div>
            <p class="mb-3">
                Setujui pengajuan hapus untuk peserta
                <strong>{{ approveTarget?.peserta_nama }}</strong>?
            </p>
            <AppTextarea
                v-model="approveForm.catatan_admin"
                label="Catatan (opsional)"
                placeholder="Tambahkan catatan untuk pemohon..."
                :error="approveForm.errors.catatan_admin"
                :rows="3"
            />
        </AppFormModal>

        <!-- ── Reject Modal ───────────────────────────────────────────── -->
        <AppFormModal
            id="rejectHapusModal"
            size="sm"
            title="Tolak Pengajuan Hapus"
            :processing="rejectForm.processing"
            submit-label="Tolak Pengajuan"
            @submit="doReject"
        >
            <p class="mb-3">
                Tolak pengajuan hapus untuk peserta
                <strong>{{ rejectTarget?.peserta_nama }}</strong>?
                Peserta akan tetap ada di sistem.
            </p>
            <AppTextarea
                v-model="rejectForm.catatan_admin"
                label="Alasan Penolakan"
                placeholder="Jelaskan alasan penolakan..."
                :error="rejectForm.errors.catatan_admin"
                :rows="3"
                required
            />
        </AppFormModal>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppFlash from '@/Components/UI/AppFlash.vue';
import AppFormModal from '@/Components/UI/AppFormModal.vue';
import AppTextarea from '@/Components/UI/Form/AppTextarea.vue';
import { useBootstrapModal } from '@/Composables/useBootstrapModal';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

interface PengajuanRow {
    id: number;
    peserta_id: number;
    peserta_nama: string | null;
    peserta_status: string | null;
    kafilah_nama: string | null;
    requester_name: string | null;
    pesan: string;
    status: 'menunggu' | 'disetujui' | 'ditolak';
    catatan_admin: string | null;
    reviewer_name: string | null;
    reviewed_at: string | null;
    created_at: string;
}

const props = defineProps<{
    pengajuans: PengajuanRow[];
}>();

const { show, hide } = useBootstrapModal(['approveHapusModal', 'rejectHapusModal']);

// ── Status filter tabs ────────────────────────────────────────────────────────

const activeTab = ref<string>('menunggu');

const statusTabs = computed(() => [
    { value: '',          label: 'Semua',     color: 'secondary', count: props.pengajuans.length },
    { value: 'menunggu',  label: 'Menunggu',  color: 'warning',   count: props.pengajuans.filter(p => p.status === 'menunggu').length },
    { value: 'disetujui', label: 'Disetujui', color: 'success',   count: props.pengajuans.filter(p => p.status === 'disetujui').length },
    { value: 'ditolak',   label: 'Ditolak',   color: 'danger',    count: props.pengajuans.filter(p => p.status === 'ditolak').length },
]);

const filteredPengajuans = computed(() =>
    activeTab.value ? props.pengajuans.filter(p => p.status === activeTab.value) : props.pengajuans
);

// ── Table columns ─────────────────────────────────────────────────────────────

const columns: TableColumn[] = [
    { key: 'peserta_nama',   label: 'Peserta' },
    { key: 'kafilah_nama',   label: 'Kafilah' },
    { key: 'requester_name', label: 'Pemohon (Admin)' },
    { key: 'pesan',          label: 'Alasan' },
    { key: 'status',         label: 'Status' },
    { key: 'catatan_admin',  label: 'Catatan Superadmin' },
    { key: 'created_at',     label: 'Tanggal' },
    { key: 'aksi',           label: 'Aksi' },
];

// ── Status helpers ────────────────────────────────────────────────────────────

const statusConfig: Record<string, { label: string; badgeClass: string }> = {
    menunggu:  { label: 'Menunggu',  badgeClass: 'text-bg-warning' },
    disetujui: { label: 'Disetujui', badgeClass: 'text-bg-success' },
    ditolak:   { label: 'Ditolak',   badgeClass: 'text-bg-danger'  },
};

function statusLabel(v: string): string      { return statusConfig[v]?.label      ?? v; }
function statusBadgeClass(v: string): string { return statusConfig[v]?.badgeClass ?? 'text-bg-secondary'; }

// ── Approve ───────────────────────────────────────────────────────────────────

const approveTarget = ref<PengajuanRow | null>(null);
const approveForm   = useForm<{ catatan_admin: string }>({ catatan_admin: '' });

function openApprove(row: PengajuanRow): void {
    approveTarget.value       = row;
    approveForm.catatan_admin = '';
    approveForm.clearErrors();
    show('approveHapusModal');
}

function doApprove(): void {
    if (!approveTarget.value) return;
    approveForm.post(`/mtq/pengajuan-hapus/${approveTarget.value.id}/approve`, {
        preserveScroll: true,
        onSuccess: () => hide('approveHapusModal'),
    });
}

// ── Reject ────────────────────────────────────────────────────────────────────

const rejectTarget = ref<PengajuanRow | null>(null);
const rejectForm   = useForm<{ catatan_admin: string }>({ catatan_admin: '' });

function openReject(row: PengajuanRow): void {
    rejectTarget.value        = row;
    rejectForm.catatan_admin  = '';
    rejectForm.clearErrors();
    show('rejectHapusModal');
}

function doReject(): void {
    if (!rejectTarget.value) return;
    rejectForm.post(`/mtq/pengajuan-hapus/${rejectTarget.value.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => hide('rejectHapusModal'),
    });
}
</script>

<style lang="scss" scoped>
.app-action-btn {
    width: 30px;
    height: 30px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px !important;
    flex-shrink: 0;
}
</style>
