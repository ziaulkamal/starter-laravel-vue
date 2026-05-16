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
                                    @click="openApprove(row)"
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

        <!-- Approve modal -->
        <div class="modal fade" id="approveModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center px-4 pb-2">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle"
                                  style="width: 64px; height: 64px">
                                <i class="ti ti-user-check text-success" style="font-size: 2rem"></i>
                            </span>
                        </div>
                        <h5 class="fw-semibold mb-1">Setujui Pendaftaran</h5>
                        <p class="text-muted mb-1">
                            Anda akan menyetujui pendaftaran dari
                        </p>
                        <p class="mb-0">
                            <strong class="text-dark">{{ approveTarget?.name }}</strong>
                            <br>
                            <span class="text-muted small">{{ approveTarget?.email }}</span>
                            <span v-if="approveTarget?.phone" class="text-muted small"> · {{ approveTarget?.phone }}</span>
                        </p>
                        <p class="mt-2 mb-0 small text-muted">
                            Akun akan segera dibuat dan user dapat login.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success px-4"
                                :disabled="processing !== null"
                                @click="confirmApprove">
                            <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="ti ti-check me-1"></i>
                            Ya, Setujui
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center px-4 pb-2">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle"
                                  style="width: 64px; height: 64px">
                                <i class="ti ti-user-x text-danger" style="font-size: 2rem"></i>
                            </span>
                        </div>
                        <h5 class="fw-semibold mb-1">Tolak Pendaftaran</h5>
                        <p class="text-muted mb-1">
                            Anda akan menolak pendaftaran dari
                        </p>
                        <p class="mb-3">
                            <strong class="text-dark">{{ rejectTarget?.name }}</strong>
                            <br>
                            <span class="text-muted small">{{ rejectTarget?.email }}</span>
                        </p>
                        <div class="text-start">
                            <label class="form-label small fw-semibold">
                                Alasan Penolakan
                                <span class="text-muted fw-normal">(opsional)</span>
                            </label>
                            <textarea v-model="rejectNotes" class="form-control form-control-sm" rows="3"
                                      placeholder="Masukkan alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-2 justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger px-4"
                                :disabled="processing !== null"
                                @click="confirmReject">
                            <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="ti ti-x me-1"></i>
                            Ya, Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onBeforeUnmount } from 'vue';
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

const processing    = ref<number | null>(null);
const approveTarget = ref<PendingRow | null>(null);
const rejectTarget  = ref<PendingRow | null>(null);
const rejectNotes   = ref('');

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

function openApprove(row: Record<string, unknown>) {
    approveTarget.value = row as unknown as PendingRow;
    Modal.getOrCreateInstance(document.getElementById('approveModal')!).show();
}

function confirmApprove() {
    if (!approveTarget.value) return;
    processing.value = approveTarget.value.id;
    router.post(`/settings/pending-registrations/${approveTarget.value.id}/approve`, {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = null;
            Modal.getInstance(document.getElementById('approveModal')!)?.hide();
        },
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

onBeforeUnmount(() => {
    ['approveModal', 'rejectModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) Modal.getInstance(el)?.dispose();
    });
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
});
</script>
