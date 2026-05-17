<template>
    <AppLayout
        title="Pengajuan Edit Peserta"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Pengajuan Edit' }]"
    >
        <AppFlash />

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    :data="pengajuans"
                    :columns="columns"
                    :hover="true"
                    :show-row-numbers="true"
                    empty-text="Belum ada pengajuan edit yang dibuat"
                >
                    <template #cell-status="{ value }">
                        <span :class="['badge rounded-pill', statusBadgeClass(value as string)]">
                            {{ statusLabel(value as string) }}
                        </span>
                    </template>

                    <template #cell-peserta_status="{ value }">
                        <span :class="['badge rounded-pill', pesertaStatusBadgeClass(value as string)]">
                            {{ pesertaStatusLabel(value as string) }}
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
                </AppTable>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppFlash from '@/Components/UI/AppFlash.vue';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

interface PengajuanRow {
    id: number;
    peserta_id: number;
    peserta_nama: string | null;
    peserta_status: string | null;
    kafilah_nama: string | null;
    pesan: string;
    status: 'menunggu' | 'disetujui' | 'ditolak';
    catatan_admin: string | null;
    reviewer_name: string | null;
    reviewed_at: string | null;
    created_at: string;
}

defineProps<{ pengajuans: PengajuanRow[] }>();

const columns: TableColumn[] = [
    { key: 'peserta_nama',   label: 'Peserta' },
    { key: 'kafilah_nama',   label: 'Kafilah' },
    { key: 'peserta_status', label: 'Status Peserta' },
    { key: 'pesan',          label: 'Alasan Permohonan' },
    { key: 'status',         label: 'Status Pengajuan' },
    { key: 'catatan_admin',  label: 'Catatan Admin' },
    { key: 'created_at',     label: 'Tanggal Kirim' },
];

const statusConfig: Record<string, { label: string; badgeClass: string }> = {
    menunggu:  { label: 'Menunggu',   badgeClass: 'text-bg-warning'   },
    disetujui: { label: 'Disetujui',  badgeClass: 'text-bg-success'   },
    ditolak:   { label: 'Ditolak',    badgeClass: 'text-bg-danger'    },
};

const pesertaStatusConfig: Record<string, { label: string; badgeClass: string }> = {
    draft:        { label: 'Draft',         badgeClass: 'text-bg-secondary' },
    diajukan:     { label: 'Diajukan',      badgeClass: 'text-bg-info'      },
    diverifikasi: { label: 'Terverifikasi', badgeClass: 'text-bg-success'   },
    ditolak:      { label: 'Ditolak',       badgeClass: 'text-bg-danger'    },
};

function statusLabel(v: string): string         { return statusConfig[v]?.label        ?? v; }
function statusBadgeClass(v: string): string    { return statusConfig[v]?.badgeClass   ?? 'text-bg-secondary'; }
function pesertaStatusLabel(v: string): string  { return pesertaStatusConfig[v]?.label      ?? v; }
function pesertaStatusBadgeClass(v: string): string { return pesertaStatusConfig[v]?.badgeClass ?? 'text-bg-secondary'; }
</script>
