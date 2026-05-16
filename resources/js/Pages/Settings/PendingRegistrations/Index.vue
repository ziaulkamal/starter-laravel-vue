<template>
    <AppLayout
        title="Pending Registrasi"
        :breadcrumb="[{ label: 'Pengaturan' }, { label: 'Pending Registrasi' }]"
    >
        <!-- Flash -->
        <div v-if="flash.success" class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            {{ flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <div v-if="flash.error" class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            {{ flash.error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    :data="items"
                    :columns="columns"
                    :hover="true"
                    :show-row-numbers="true"
                    empty-text="Tidak ada pendaftaran yang menunggu persetujuan"
                >
                    <template #controls-left>
                        <span class="text-muted small">
                            <i class="ti ti-clock-hour-4 me-1"></i>
                            {{ pendingCount }} menunggu persetujuan
                        </span>
                    </template>

                    <template #cell-phone="{ value }">
                        <span v-if="value" class="font-monospace small">{{ value }}</span>
                        <span v-else class="text-muted">—</span>
                    </template>

                    <template #cell-ip_address="{ value }">
                        <code class="small text-danger-emphasis">{{ value ?? '—' }}</code>
                    </template>

                    <template #cell-status="{ value }">
                        <span :class="statusBadge(value as string)">
                            {{ statusLabel(value as string) }}
                        </span>
                    </template>

                    <template #cell-aksi="{ row }">
                        <template v-if="row.status === 'pending'">
                            <div class="d-flex gap-1 justify-content-center">
                                <button
                                    class="btn btn-sm btn-success"
                                    :disabled="processing === row.id"
                                    @click="approve(row)"
                                >
                                    <i class="ti ti-check me-1"></i>Setujui
                                </button>
                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    :disabled="processing === row.id"
                                    @click="openReject(row)"
                                >
                                    <i class="ti ti-x me-1"></i>Tolak
                                </button>
                            </div>
                        </template>
                        <span v-else class="text-muted small">Sudah diproses</span>
                    </template>
                </AppTable>
            </div>
        </div>

        <!-- Reject modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Pendaftaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            Tolak pendaftaran dari <strong>{{ rejectTarget?.name }}</strong>
                            (<span class="text-muted">{{ rejectTarget?.email }}</span>)?
                        </p>
                        <label class="form-label">Alasan Penolakan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea v-model="rejectNotes" class="form-control" rows="3"
                                  placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger"
                                :disabled="processing !== null"
                                @click="confirmReject">
                            <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                            Tolak Pendaftaran
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
// @ts-expect-error — bootstrap ships no .d.ts; typed via env.d.ts ambient declaration
import { Modal } from 'bootstrap';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

interface PendingRow extends Record<string, unknown> {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    ip_address: string | null;
    status: 'pending' | 'approved' | 'rejected';
    notes: string | null;
    registered_at: string;
}

const props = defineProps<{ items: PendingRow[] }>();

const flash = computed(() => usePage().props.flash as { success: string | null; error: string | null });

const columns: TableColumn[] = [
    { key: 'name',          label: 'Nama' },
    { key: 'email',         label: 'Email' },
    { key: 'phone',         label: 'Nomor HP' },
    { key: 'ip_address',    label: 'IP Public' },
    { key: 'registered_at', label: 'Tanggal Daftar' },
    { key: 'status',        label: 'Status' },
    { key: 'aksi',          label: 'Aksi', thClass: 'text-center' },
];

const pendingCount = computed(() => props.items.filter(i => i.status === 'pending').length);

const processing   = ref<number | null>(null);
const rejectTarget = ref<PendingRow | null>(null);
const rejectNotes  = ref('');

const statusMap: Record<string, { label: string; cls: string }> = {
    pending:  { label: 'Menunggu',  cls: 'badge text-bg-warning' },
    approved: { label: 'Disetujui', cls: 'badge text-bg-success' },
    rejected: { label: 'Ditolak',   cls: 'badge text-bg-danger' },
};

function statusBadge(status: string): string {
    return statusMap[status]?.cls ?? 'badge text-bg-secondary';
}

function statusLabel(status: string): string {
    return statusMap[status]?.label ?? status;
}

function approve(row: Record<string, unknown>) {
    if (!confirm(`Setujui pendaftaran dari ${row.name}?`)) return;
    processing.value = row.id as number;
    router.post(`/settings/pending-registrations/${row.id}/approve`, {}, {
        preserveScroll: true,
        onFinish: () => (processing.value = null),
    });
}

function openReject(row: Record<string, unknown>) {
    rejectTarget.value = row as unknown as PendingRow;
    rejectNotes.value  = '';
    Modal.getOrCreateInstance(document.getElementById('rejectModal')!).show();
}

function confirmReject() {
    if (!rejectTarget.value) return;
    processing.value = rejectTarget.value.id;
    router.post(
        `/settings/pending-registrations/${rejectTarget.value.id}/reject`,
        { notes: rejectNotes.value },
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = null;
                Modal.getInstance(document.getElementById('rejectModal')!)?.hide();
            },
        },
    );
}
</script>
