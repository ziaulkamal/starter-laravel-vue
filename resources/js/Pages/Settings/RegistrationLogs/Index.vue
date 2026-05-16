<template>
    <AppLayout
        title="Log Registrasi"
        :breadcrumb="[{ label: 'Pengaturan' }, { label: 'Log Registrasi' }]"
    >
        <!-- Stats bar -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card border-0 bg-success-subtle">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <span class="d-flex align-items-center justify-content-center rounded-circle bg-success text-white"
                              style="width:42px;height:42px;flex-shrink:0">
                            <i class="ti ti-user-check fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-bold fs-4 lh-1">{{ approvedCount }}</div>
                            <div class="text-muted small">Disetujui</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card border-0 bg-danger-subtle">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <span class="d-flex align-items-center justify-content-center rounded-circle bg-danger text-white"
                              style="width:42px;height:42px;flex-shrink:0">
                            <i class="ti ti-user-x fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-bold fs-4 lh-1">{{ rejectedCount }}</div>
                            <div class="text-muted small">Ditolak</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card border-0 bg-body-secondary">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <span class="d-flex align-items-center justify-content-center rounded-circle bg-secondary text-white"
                              style="width:42px;height:42px;flex-shrink:0">
                            <i class="ti ti-list fs-5"></i>
                        </span>
                        <div>
                            <div class="fw-bold fs-4 lh-1">{{ logs.length }}</div>
                            <div class="text-muted small">Total Log</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    :data="logs"
                    :columns="columns"
                    :hover="true"
                    :show-row-numbers="true"
                    empty-text="Belum ada log registrasi"
                >
                    <template #cell-name="{ row }">
                        <div class="fw-semibold small">{{ row.name }}</div>
                        <div class="text-muted" style="font-size:0.75rem">{{ row.email }}</div>
                    </template>

                    <template #cell-phone="{ value }">
                        <span v-if="value" class="font-monospace small">{{ value }}</span>
                        <span v-else class="text-muted">—</span>
                    </template>

                    <template #cell-ip_address="{ value }">
                        <code class="small text-danger-emphasis">{{ value ?? '—' }}</code>
                    </template>

                    <template #cell-action="{ value }">
                        <span :class="value === 'approved' ? 'badge text-bg-success' : 'badge text-bg-danger'">
                            <i :class="value === 'approved' ? 'ti ti-check me-1' : 'ti ti-x me-1'"></i>
                            {{ value === 'approved' ? 'Disetujui' : 'Ditolak' }}
                        </span>
                    </template>

                    <template #cell-notes="{ value }">
                        <span v-if="value" class="text-muted small fst-italic">{{ value }}</span>
                        <span v-else class="text-muted">—</span>
                    </template>

                    <template #cell-processed_by="{ row }">
                        <div class="small">{{ row.processed_by }}</div>
                        <div class="text-muted" style="font-size:0.72rem">{{ row.processed_at }}</div>
                    </template>
                </AppTable>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

interface LogRow extends Record<string, unknown> {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    ip_address: string | null;
    registered_at: string;
    action: 'approved' | 'rejected';
    notes: string | null;
    processed_by: string;
    processed_at: string;
}

const props = defineProps<{ logs: LogRow[] }>();

const columns: TableColumn[] = [
    { key: 'name',          label: 'Pendaftar' },
    { key: 'phone',         label: 'Nomor HP' },
    { key: 'ip_address',    label: 'IP Public' },
    { key: 'registered_at', label: 'Tgl Daftar' },
    { key: 'action',        label: 'Status' },
    { key: 'notes',         label: 'Catatan' },
    { key: 'processed_by',  label: 'Diproses Oleh' },
];

const approvedCount = computed(() => props.logs.filter(l => l.action === 'approved').length);
const rejectedCount = computed(() => props.logs.filter(l => l.action === 'rejected').length);
</script>
