<template>
    <AppLayout
        title="Pendaftaran Peserta"
        :breadcrumb="[{ label: 'MTQ' }, { label: 'Pendaftaran Peserta' }]"
    >
        <template #page-actions>
            <button
                v-if="selectedIds.length > 0"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1"
                @click="show('bulkDeletePesertaModal')"
            >
                <i class="ti ti-trash fs-5"></i>
                <span>Hapus ({{ selectedIds.length }})</span>
            </button>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" @click="openCreate">
                <i class="ti ti-plus fs-5"></i>
                <span>Daftar Peserta</span>
            </button>
        </template>

        <AppFlash />

        <!-- Status filter tabs -->
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
                    v-model:selected="selectedRows"
                    :data="filteredPeserta"
                    :columns="columns"
                    :actions="{ edit: true, delete: true }"
                    :hover="true"
                    :show-row-numbers="true"
                    :selectable="true"
                    empty-text="Belum ada peserta terdaftar"
                    @edit="openEdit"
                    @delete="openDelete"
                >
                    <template #cell-jenis_kelamin="{ value }">
                        <span :class="['badge rounded-pill', value === 'L' ? 'text-bg-info' : 'text-bg-danger']">
                            {{ value === 'L' ? 'Putra' : 'Putri' }}
                        </span>
                    </template>

                    <template #cell-status="{ value, row }">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span :class="['badge rounded-pill', statusBadgeClass(value as string)]">
                                {{ statusLabel(value as string) }}
                            </span>
                            <!-- Ajukan (draft) -->
                            <button
                                v-if="value === 'draft'"
                                type="button"
                                class="btn btn-sm bg-info-subtle text-info app-action-btn"
                                title="Ajukan untuk verifikasi"
                                :disabled="submitProcessing === (row as PesertaRow).id"
                                @click="submitPeserta(row as PesertaRow)"
                            >
                                <span
                                    v-if="submitProcessing === (row as PesertaRow).id"
                                    class="spinner-border spinner-border-sm"
                                ></span>
                                <i v-else class="ti ti-send fs-5"></i>
                            </button>
                            <!-- Verifikasi / Tolak (diajukan, superadmin) -->
                            <template v-if="value === 'diajukan' && isSuperadmin">
                                <button
                                    type="button"
                                    class="btn btn-sm bg-success-subtle text-success app-action-btn"
                                    title="Verifikasi peserta"
                                    @click="openVerify(row as PesertaRow)"
                                >
                                    <i class="ti ti-check fs-5"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm bg-danger-subtle text-danger app-action-btn"
                                    title="Tolak peserta"
                                    @click="openReject(row as PesertaRow)"
                                >
                                    <i class="ti ti-x fs-5"></i>
                                </button>
                            </template>
                        </div>
                    </template>
                </AppTable>
            </div>
        </div>

        <!-- ── Form Modal (Create / Edit) ────────────────────────────── -->
        <AppFormModal
            id="pesertaModal"
            size="xl"
            :title="isEditing ? 'Edit Data Peserta' : 'Daftar Peserta Baru'"
            :processing="form.processing"
            :submit-label="isEditing ? 'Simpan Perubahan' : 'Daftarkan'"
            @submit="submitForm"
        >
            <div class="row g-3">
                <!-- Kafilah -->
                <div class="col-md-6">
                    <AppSelect2
                        v-model="form.kafilah_id"
                        :options="kafilahOptions"
                        label="Kafilah (Kabupaten/Kota)"
                        placeholder="Pilih kafilah..."
                        search-placeholder="Cari kafilah..."
                        :error="form.errors.kafilah_id"
                        required
                    />
                </div>
                <!-- Cabang -->
                <div class="col-md-6">
                    <AppSelect
                        v-model="form.cabang_id"
                        :options="cabangOptions"
                        label="Cabang Lomba"
                        placeholder="— Pilih Cabang —"
                        :error="form.errors.cabang_id"
                        required
                        @change="() => (form.golongan_id = '')"
                    />
                </div>
                <!-- Golongan -->
                <div class="col-md-6">
                    <AppSelect
                        v-model="form.golongan_id"
                        :options="filteredGolonganOptions"
                        label="Golongan"
                        placeholder="— Pilih Golongan —"
                        :error="form.errors.golongan_id"
                        :disabled="!form.cabang_id"
                        required
                    />
                </div>
                <!-- Jenis Kelamin -->
                <div class="col-md-6">
                    <AppSelect
                        v-model="form.jenis_kelamin"
                        :options="jkOptions"
                        label="Jenis Kelamin"
                        placeholder="— Pilih —"
                        :error="form.errors.jenis_kelamin"
                        required
                    />
                </div>
                <!-- Nama -->
                <div class="col-12">
                    <AppInput
                        v-model="form.nama"
                        label="Nama Lengkap"
                        placeholder="Nama sesuai akta/dokumen resmi"
                        :error="form.errors.nama"
                        required
                    />
                </div>
                <!-- NIK + Tgl Lahir -->
                <div class="col-md-6">
                    <AppInput
                        v-model="form.nik"
                        label="NIK"
                        placeholder="16 digit NIK"
                        :error="form.errors.nik"
                    />
                </div>
                <div class="col-md-6">
                    <AppInput
                        v-model="form.tgl_lahir"
                        type="date"
                        label="Tanggal Lahir"
                        :error="form.errors.tgl_lahir"
                    />
                </div>
                <!-- Tempat Lahir -->
                <div class="col-12">
                    <AppInput
                        v-model="form.tempat_lahir"
                        label="Tempat Lahir"
                        placeholder="Kota/kabupaten tempat lahir"
                        :error="form.errors.tempat_lahir"
                    />
                </div>
                <!-- Alamat -->
                <div class="col-12">
                    <AppTextarea
                        v-model="form.alamat"
                        label="Alamat"
                        placeholder="Alamat domisili peserta"
                        :error="form.errors.alamat"
                        :rows="2"
                    />
                </div>
            </div>

            <!-- Berkas section — edit mode only -->
            <template v-if="isEditing">
                <hr class="my-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ti ti-files me-1"></i>Berkas Dokumen
                    </h6>
                    <span class="badge text-bg-secondary rounded-pill">
                        {{ editingRow?.berkas?.length ?? 0 }} file
                    </span>
                </div>

                <!-- Existing berkas list -->
                <div v-if="editingRow?.berkas?.length" class="list-group mb-3">
                    <div
                        v-for="b in editingRow.berkas"
                        :key="b.id"
                        class="list-group-item d-flex align-items-center gap-2 py-2"
                    >
                        <i :class="['ti fs-4 text-muted', fileIcon(b.mime_type)]"></i>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="small fw-medium">{{ berkasLabel(b.jenis) }}</div>
                            <a
                                :href="b.url"
                                target="_blank"
                                class="small text-muted text-truncate d-block"
                                style="max-width: 300px"
                            >{{ b.nama_asli }}</a>
                        </div>
                        <span v-if="b.ukuran" class="small text-muted text-nowrap">
                            {{ formatSize(b.ukuran) }}
                        </span>
                        <button
                            type="button"
                            class="btn btn-sm btn-link text-danger p-0 ms-1"
                            title="Hapus berkas"
                            @click="deleteBerkas(b.id)"
                        >
                            <i class="ti ti-trash fs-5"></i>
                        </button>
                    </div>
                </div>
                <p v-else class="text-muted small fst-italic mb-3">Belum ada berkas diunggah.</p>

                <!-- Upload form -->
                <div class="border rounded-3 p-3 bg-body-tertiary">
                    <p class="small fw-semibold text-muted text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em">
                        Upload Berkas Baru
                    </p>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <AppSelect
                                v-model="berkasForm.jenis"
                                :options="berkasJenisOptions"
                                placeholder="— Jenis Berkas —"
                                size="sm"
                            />
                        </div>
                        <div class="col-md-6">
                            <input
                                ref="fileInputRef"
                                type="file"
                                class="form-control form-control-sm"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                @change="onFileChange"
                            />
                        </div>
                        <div class="col-md-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-primary w-100"
                                :disabled="!berkasForm.jenis || !berkasForm.file || berkasForm.processing"
                                @click="uploadBerkas"
                            >
                                <span
                                    v-if="berkasForm.processing"
                                    class="spinner-border spinner-border-sm"
                                ></span>
                                <span v-else>Upload</span>
                            </button>
                        </div>
                    </div>
                    <p class="form-text mb-0 mt-1">
                        Format: PDF, JPG, PNG, DOC, DOCX · Maks. 5 MB
                    </p>
                </div>
            </template>
        </AppFormModal>

        <!-- ── Verify Modal ───────────────────────────────────────────── -->
        <AppFormModal
            id="verifyPesertaModal"
            size="sm"
            title="Verifikasi Peserta"
            :processing="verifyForm.processing"
            submit-label="Verifikasi"
            @submit="doVerify"
        >
            <p class="mb-3">
                Verifikasi <strong>{{ verifyTarget?.nama }}</strong>?
            </p>
            <AppInput
                v-model="verifyForm.nomor_peserta"
                label="Nomor Peserta"
                placeholder="cth: 2025/001"
                :error="verifyForm.errors.nomor_peserta"
                required
            />
        </AppFormModal>

        <!-- ── Reject Modal ───────────────────────────────────────────── -->
        <AppFormModal
            id="rejectPesertaModal"
            size="sm"
            title="Tolak Pendaftaran"
            :processing="rejectForm.processing"
            submit-label="Simpan & Tolak"
            @submit="doReject"
        >
            <p class="mb-3">
                Tolak pendaftaran <strong>{{ rejectTarget?.nama }}</strong>?
            </p>
            <AppTextarea
                v-model="rejectForm.catatan_verifikasi"
                label="Alasan Penolakan"
                placeholder="Tuliskan alasan penolakan..."
                :error="rejectForm.errors.catatan_verifikasi"
                :rows="3"
                required
            />
        </AppFormModal>

        <!-- ── Delete Single ──────────────────────────────────────────── -->
        <AppDeleteModal
            id="deletePesertaModal"
            :processing="deleteProcessing"
            @confirm="doDelete"
        >
            Peserta <strong>{{ deleteTarget?.nama }}</strong> beserta seluruh berkasnya akan dihapus permanen.
        </AppDeleteModal>

        <!-- ── Bulk Delete ─────────────────────────────────────────────── -->
        <AppDeleteModal
            id="bulkDeletePesertaModal"
            title="Hapus Peserta Terpilih?"
            confirm-label="Ya, Hapus Semua"
            :processing="bulkProcessing"
            @confirm="doBulkDelete"
        >
            <strong>{{ selectedIds.length }} peserta</strong> beserta seluruh berkasnya akan dihapus permanen.
            Tindakan ini tidak dapat dibatalkan.
        </AppDeleteModal>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive, watch, onMounted } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { useWilayahSelect } from '@/Composables/useWilayahSelect';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppFlash from '@/Components/UI/AppFlash.vue';
import AppFormModal from '@/Components/UI/AppFormModal.vue';
import AppDeleteModal from '@/Components/UI/AppDeleteModal.vue';
import AppInput from '@/Components/UI/Form/AppInput.vue';
import AppSelect from '@/Components/UI/Form/AppSelect.vue';
import AppSelect2 from '@/Components/UI/Form/AppSelect2.vue';
import AppTextarea from '@/Components/UI/Form/AppTextarea.vue';
import { useBootstrapModal } from '@/Composables/useBootstrapModal';
import type { TableColumn } from '@/Components/UI/AppTable.vue';
import type { Select2Option } from '@/Components/UI/Form/AppSelect2.vue';

// ── Types ─────────────────────────────────────────────────────────────────────

interface BerkasRow {
    id: number;
    jenis: string;
    url: string;
    nama_asli: string;
    mime_type: string | null;
    ukuran: number | null;
}

interface PesertaRow {
    id: number;
    kafilah_id: number;
    kafilah_nama: string | null;
    cabang_id: number;
    cabang_nama: string | null;
    golongan_id: number;
    golongan_nama: string | null;
    nama: string;
    nik: string | null;
    jenis_kelamin: 'L' | 'P';
    tempat_lahir: string | null;
    tgl_lahir: string | null;
    alamat: string | null;
    kode_wilayah_desa: string | null;
    foto_url: string | null;
    status: 'draft' | 'diajukan' | 'diverifikasi' | 'ditolak';
    catatan_verifikasi: string | null;
    nomor_peserta: string | null;
    berkas: BerkasRow[];
}

interface KafilahItem { id: number; nama_kabupaten: string; }
interface CabangItem  { id: number; nama: string; }
interface GolonganItem { id: number; cabang_id: number; nama: string; jenis_kelamin: string; }

interface PesertaForm {
    kafilah_id:        number | string;
    cabang_id:         number | string;
    golongan_id:       number | string;
    nama:              string;
    nik:               string;
    jenis_kelamin:     string;
    tempat_lahir:      string;
    tgl_lahir:         string;
    alamat:            string;
    kode_wilayah_desa: string;
}

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps<{
    peserta:   PesertaRow[];
    kafilahs:  KafilahItem[];
    cabangs:   CabangItem[];
    golongans: GolonganItem[];
}>();

// ── Auth ──────────────────────────────────────────────────────────────────────

const page         = usePage();
const isSuperadmin = computed(() =>
    (page.props.auth as { roles?: string[] }).roles?.includes('superadmin') ?? false
);

// ── Wilayah cascading select ──────────────────────────────────────────────────

const wilayah = reactive(useWilayahSelect());

onMounted(() => wilayah.loadProvinsi());

// ── Table columns ─────────────────────────────────────────────────────────────

const columns: TableColumn[] = [
    { key: 'nomor_peserta',      label: 'No. Peserta' },
    { key: 'nama',               label: 'Nama Peserta' },
    { key: 'kafilah_nama',       label: 'Kafilah' },
    { key: 'cabang_nama',        label: 'Cabang' },
    { key: 'golongan_nama',      label: 'Golongan' },
    { key: 'jenis_kelamin',      label: 'JK' },
    { key: 'status',             label: 'Status' },
    { key: 'kode_wilayah_desa',  label: 'Kode Wilayah', hidden: true },
];

// ── Status helpers ────────────────────────────────────────────────────────────

const statusConfig: Record<string, { label: string; color: string; badgeClass: string }> = {
    draft:        { label: 'Draft',         color: 'secondary', badgeClass: 'text-bg-secondary' },
    diajukan:     { label: 'Diajukan',      color: 'info',      badgeClass: 'text-bg-info'      },
    diverifikasi: { label: 'Terverifikasi', color: 'success',   badgeClass: 'text-bg-success'   },
    ditolak:      { label: 'Ditolak',       color: 'danger',    badgeClass: 'text-bg-danger'     },
};

function statusLabel(value: string): string {
    return statusConfig[value]?.label ?? value;
}

function statusBadgeClass(value: string): string {
    return statusConfig[value]?.badgeClass ?? 'text-bg-secondary';
}

// ── Status filter tabs ────────────────────────────────────────────────────────

const activeTab = ref<string>('');

const statusTabs = computed(() => [
    { value: '',             label: 'Semua',         color: 'secondary', count: props.peserta.length },
    { value: 'draft',        label: 'Draft',         color: 'secondary', count: props.peserta.filter(p => p.status === 'draft').length },
    { value: 'diajukan',     label: 'Diajukan',      color: 'info',      count: props.peserta.filter(p => p.status === 'diajukan').length },
    { value: 'diverifikasi', label: 'Terverifikasi', color: 'success',   count: props.peserta.filter(p => p.status === 'diverifikasi').length },
    { value: 'ditolak',      label: 'Ditolak',       color: 'danger',    count: props.peserta.filter(p => p.status === 'ditolak').length },
]);

const filteredPeserta = computed(() =>
    activeTab.value
        ? props.peserta.filter(p => p.status === activeTab.value)
        : props.peserta
);

// ── Modal ─────────────────────────────────────────────────────────────────────

const { show, hide } = useBootstrapModal([
    'pesertaModal',
    'verifyPesertaModal',
    'rejectPesertaModal',
    'deletePesertaModal',
    'bulkDeletePesertaModal',
]);

// ── Selection ─────────────────────────────────────────────────────────────────

const selectedRows = ref<PesertaRow[]>([]);
const selectedIds  = computed(() => selectedRows.value.map(r => r.id));

// ── Options ───────────────────────────────────────────────────────────────────

const kafilahOptions = computed<Select2Option[]>(() =>
    props.kafilahs.map(k => ({ value: k.id, label: k.nama_kabupaten }))
);

const cabangOptions = computed(() =>
    props.cabangs.map(c => ({ value: c.id, label: c.nama }))
);

const filteredGolonganOptions = computed(() =>
    props.golongans
        .filter(g => String(g.cabang_id) === String(form.cabang_id))
        .map(g => ({ value: g.id, label: g.nama }))
);

const jkOptions = [
    { label: 'Laki-Laki (Putra)',   value: 'L' },
    { label: 'Perempuan (Putri)',    value: 'P' },
];

const berkasJenisOptions = [
    { label: 'Akta Lahir',          value: 'akte'              },
    { label: 'KTP / Kartu Pelajar', value: 'ktp'               },
    { label: 'Kartu Keluarga',      value: 'kk'                },
    { label: 'Surat Keterangan',    value: 'surat_keterangan'  },
    { label: 'Lainnya',             value: 'lainnya'           },
];

function berkasLabel(jenis: string): string {
    return berkasJenisOptions.find(o => o.value === jenis)?.label ?? jenis;
}

function fileIcon(mime: string | null): string {
    if (!mime) return 'ti-file';
    if (mime.includes('pdf'))   return 'ti-file-type-pdf';
    if (mime.includes('image')) return 'ti-photo';
    if (mime.includes('word') || mime.includes('document')) return 'ti-file-type-doc';
    return 'ti-file';
}

function formatSize(bytes: number): string {
    if (bytes < 1024)         return bytes + ' B';
    if (bytes < 1024 * 1024)  return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// ── Create / Edit form ────────────────────────────────────────────────────────

const isEditing = ref<boolean>(false);
const editingId = ref<number | null>(null);

// Computed so berkas list auto-refreshes after upload/delete (Inertia updates props)
const editingRow = computed<PesertaRow | null>(() =>
    isEditing.value && editingId.value !== null
        ? props.peserta.find(p => p.id === editingId.value) ?? null
        : null
);

const form = useForm<PesertaForm>({
    kafilah_id:    '',
    cabang_id:     '',
    golongan_id:   '',
    nama:          '',
    nik:           '',
    jenis_kelamin: '',
    tempat_lahir:  '',
    tgl_lahir:     '',
    alamat:        '',
});

function resetForm(): void {
    form.kafilah_id    = '';
    form.cabang_id     = '';
    form.golongan_id   = '';
    form.nama          = '';
    form.nik           = '';
    form.jenis_kelamin = '';
    form.tempat_lahir  = '';
    form.tgl_lahir     = '';
    form.alamat        = '';
    form.clearErrors();
}

function openCreate(): void {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    show('pesertaModal');
}

function openEdit(row: Record<string, unknown>): void {
    const p = row as unknown as PesertaRow;
    isEditing.value    = true;
    editingId.value    = p.id;
    form.kafilah_id    = p.kafilah_id;
    form.cabang_id     = p.cabang_id;
    form.golongan_id   = p.golongan_id;
    form.nama          = p.nama;
    form.nik           = p.nik ?? '';
    form.jenis_kelamin = p.jenis_kelamin;
    form.tempat_lahir  = p.tempat_lahir ?? '';
    form.tgl_lahir     = p.tgl_lahir ?? '';
    form.alamat        = p.alamat ?? '';
    form.clearErrors();
    show('pesertaModal');
}

function submitForm(): void {
    const opts = { preserveScroll: true, onSuccess: () => hide('pesertaModal') };
    if (isEditing.value && editingId.value) {
        form.put(`/mtq/peserta/${editingId.value}`, opts);
    } else {
        form.post('/mtq/peserta', { ...opts, onSuccess: () => { hide('pesertaModal'); resetForm(); } });
    }
}

// ── Berkas upload ─────────────────────────────────────────────────────────────

const berkasForm  = useForm<{ jenis: string; file: File | null }>({ jenis: '', file: null });
const fileInputRef = ref<HTMLInputElement | null>(null);

function onFileChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    berkasForm.file = input.files?.[0] ?? null;
}

function uploadBerkas(): void {
    if (!editingId.value) return;
    berkasForm.post(`/mtq/peserta/${editingId.value}/berkas`, {
        preserveScroll: true,
        onSuccess: () => {
            berkasForm.reset();
            if (fileInputRef.value) fileInputRef.value.value = '';
        },
    });
}

function deleteBerkas(berkasId: number): void {
    router.delete(`/mtq/berkas/${berkasId}`, { preserveScroll: true });
}

// ── Submit for review ─────────────────────────────────────────────────────────

const submitProcessing = ref<number | null>(null);

function submitPeserta(row: PesertaRow): void {
    submitProcessing.value = row.id;
    router.post(`/mtq/peserta/${row.id}/submit`, {}, {
        preserveScroll: true,
        onFinish: () => { submitProcessing.value = null; },
    });
}

// ── Verify ────────────────────────────────────────────────────────────────────

const verifyTarget = ref<PesertaRow | null>(null);
const verifyForm   = useForm<{ nomor_peserta: string }>({ nomor_peserta: '' });

function openVerify(row: PesertaRow): void {
    verifyTarget.value          = row;
    verifyForm.nomor_peserta    = '';
    verifyForm.clearErrors();
    show('verifyPesertaModal');
}

function doVerify(): void {
    if (!verifyTarget.value) return;
    verifyForm.post(`/mtq/peserta/${verifyTarget.value.id}/verify`, {
        preserveScroll: true,
        onSuccess: () => hide('verifyPesertaModal'),
    });
}

// ── Reject ────────────────────────────────────────────────────────────────────

const rejectTarget = ref<PesertaRow | null>(null);
const rejectForm   = useForm<{ catatan_verifikasi: string }>({ catatan_verifikasi: '' });

function openReject(row: PesertaRow): void {
    rejectTarget.value               = row;
    rejectForm.catatan_verifikasi    = '';
    rejectForm.clearErrors();
    show('rejectPesertaModal');
}

function doReject(): void {
    if (!rejectTarget.value) return;
    rejectForm.post(`/mtq/peserta/${rejectTarget.value.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => hide('rejectPesertaModal'),
    });
}

// ── Delete single ─────────────────────────────────────────────────────────────

const deleteTarget     = ref<PesertaRow | null>(null);
const deleteProcessing = ref<boolean>(false);

function openDelete(row: Record<string, unknown>): void {
    deleteTarget.value = row as unknown as PesertaRow;
    show('deletePesertaModal');
}

function doDelete(): void {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/mtq/peserta/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            hide('deletePesertaModal');
        },
    });
}

// ── Bulk delete ───────────────────────────────────────────────────────────────

const bulkProcessing = ref<boolean>(false);

function doBulkDelete(): void {
    bulkProcessing.value = true;
    router.post('/mtq/peserta/bulk-destroy', { ids: selectedIds.value }, {
        preserveScroll: true,
        onFinish: () => {
            bulkProcessing.value = false;
            hide('bulkDeletePesertaModal');
        },
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
