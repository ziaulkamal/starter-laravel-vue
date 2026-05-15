<template>
    <AppLayout
        title="Table Demo"
        :breadcrumb="[{ label: 'Komponen' }, { label: 'Table Demo' }]"
    >
        <template #page-actions>
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                <i class="ti ti-plus fs-5"></i>
                <span>Tambah Karyawan</span>
            </button>
        </template>

        <!-- ── TABEL UTAMA ──────────────────────────────────────── -->
        <div class="card mb-4">
            <div class="card-body p-0">
                <AppTable
                    :data="employees"
                    :columns="employeeColumns"
                    :actions="{ view: true, edit: true, delete: true }"
                    :action-permissions="{ view: 'employees.view', edit: 'employees.edit', delete: 'employees.delete' }"
                    :show-row-numbers="true"
                    :hover="true"
                    @view="onView"
                    @edit="onEdit"
                    @delete="onDelete"
                >
                    <!-- Slot kiri: info jumlah data -->
                    <template #controls-left>
                        <span class="text-muted small">
                            <i class="ti ti-users me-1"></i>
                            {{ employees.length }} karyawan
                        </span>
                    </template>

                    <!-- Custom cell: kolom 'name' (contoh custom slot) -->
                    <template #cell-name="{ value, row }">
                        <div class="lh-sm">
                            <span class="fw-medium d-block">{{ value }}</span>
                            <span class="text-muted small">ID #{{ row.id }}</span>
                        </div>
                    </template>
                </AppTable>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between text-muted small">
                <span>Menampilkan {{ employees.length }} dari {{ employees.length }} data</span>
                <span>Klik ikon <i class="ti ti-eye"></i> untuk mengungkap data sensitif</span>
            </div>
        </div>

        <!-- ── FEATURE NOTES ────────────────────────────────────── -->
        <div class="row g-4 mb-4">

            <!-- Badge types -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar bg-primary-subtle text-primary rounded-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                                <i class="ti ti-tag fs-5"></i>
                            </span>
                            <h6 class="mb-0 fw-semibold">Badge Dinamis</h6>
                        </div>
                        <p class="text-muted small mb-3">
                            Kolom badge menggunakan <code>badgeMap</code> untuk mapping nilai ke varian warna Bootstrap.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill bg-success">Aktif</span>
                            <span class="badge rounded-pill bg-warning">Cuti</span>
                            <span class="badge rounded-pill bg-danger">Nonaktif</span>
                            <span class="badge rounded-pill bg-primary">Sr. Developer</span>
                            <span class="badge rounded-pill bg-info">Designer</span>
                            <span class="badge rounded-pill bg-secondary">Intern</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sensitive -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar bg-danger-subtle text-danger rounded-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                                <i class="ti ti-lock fs-5"></i>
                            </span>
                            <h6 class="mb-0 fw-semibold">Data Sensitif</h6>
                        </div>
                        <p class="text-muted small mb-3">
                            Kolom <code>type: 'sensitive'</code> menyensor data. Klik ikon mata untuk mengungkap per-baris.
                        </p>
                        <div class="d-flex align-items-center gap-2 font-monospace small p-2 rounded-2 bg-body-secondary">
                            <span class="text-muted">••••••••</span>
                            <i class="ti ti-eye text-muted"></i>
                            <span class="mx-2 text-muted">→</span>
                            <span>ahmad@company.com</span>
                            <i class="ti ti-eye-off text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column toggle -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar bg-warning-subtle text-warning rounded-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                                <i class="ti ti-columns-3 fs-5"></i>
                            </span>
                            <h6 class="mb-0 fw-semibold">Toggle Kolom</h6>
                        </div>
                        <p class="text-muted small mb-3">
                            Kolom <code>hidden: true</code> disembunyikan secara default. Pengguna bisa mengaktifkannya via dropdown <strong>Kolom</strong>.
                        </p>
                        <div class="small text-muted">
                            Coba toggle <strong>"Bergabung"</strong> dan <strong>"ID"</strong> di tabel di atas.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TABEL MINIMAL (tanpa fitur extra) ────────────────── -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Tabel Minimal</h5>
                <p class="card-subtitle mt-1">Auto-generate kolom dari keys data, tanpa column definition</p>
            </div>
            <div class="card-body p-0">
                <AppTable
                    :data="simpleData"
                    :actions="{ view: true }"
                    :show-column-toggle="false"
                    :striped="true"
                    empty-text="Belum ada produk"
                    @view="onViewProduct"
                />
            </div>
        </div>

        <!-- ── TABEL KOSONG (empty state) ───────────────────────── -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Empty State</h5>
                <p class="card-subtitle mt-1">Tampilan saat tidak ada data</p>
            </div>
            <div class="card-body p-0">
                <AppTable
                    :data="[]"
                    :columns="[{ key: 'name', label: 'Nama' }, { key: 'email', label: 'Email' }]"
                    :actions="true"
                    empty-text="Belum ada pengguna terdaftar"
                />
            </div>
        </div>

        <!-- Toast notifikasi aksi -->
        <div
            v-if="toast"
            class="position-fixed bottom-0 end-0 p-3"
            style="z-index: 9999"
        >
            <div
                class="toast show align-items-center border-0"
                :class="`text-bg-${toast.variant}`"
                role="alert"
            >
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i :class="`ti ${toast.icon} fs-5`"></i>
                        {{ toast.message }}
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        @click="toast = null"
                    ></button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

// ─── Column definitions ───────────────────────────────────────────────────────

const employeeColumns: TableColumn[] = [
    {
        key: 'id',
        label: 'ID',
        hidden: true,           // disembunyikan default, bisa di-toggle
        thClass: 'text-center',
        tdClass: 'text-center text-muted',
    },
    {
        key: 'avatar',
        label: 'Foto',
        type: 'avatar',
        imageSize: 40,
        imageRound: true,
        fallbackKey: 'name',    // gunakan field 'name' untuk inisial fallback
    },
    {
        key: 'name',
        label: 'Nama Karyawan',
        // Menggunakan custom slot #cell-name di template
    },
    {
        key: 'department',
        label: 'Departemen',
    },
    {
        key: 'role',
        label: 'Jabatan',
        type: 'badge',
        badgeMap: {
            'Senior Developer':  'primary',
            'Junior Developer':  'info',
            'UI/UX Designer':    'info',
            'Product Manager':   'warning',
            'HR Manager':        'success',
            'Finance Analyst':   'secondary',
            'DevOps Engineer':   'dark',
            'Intern':            'secondary',
        },
    },
    {
        key: 'status',
        label: 'Status',
        type: 'badge',
        badgeMap: {
            'Aktif':    'success',
            'Cuti':     'warning',
            'Nonaktif': 'danger',
        },
    },
    {
        key: 'email',
        label: 'Email',
        type: 'sensitive',
    },
    {
        key: 'phone',
        label: 'No. HP',
        // type: 'sensitive',
    },
    {
        key: 'salary',
        label: 'Gaji / Bulan',
        // type: 'sensitive',
    },
    {
        key: 'joined_at',
        label: 'Bergabung',
        hidden: true,           // disembunyikan default
    },
];

// ─── Dummy data: Karyawan ────────────────────────────────────────────────────

const employees = ref([
    {
        id: 'EMP-001',
        avatar: 'https://i.pravatar.cc/80?img=3',
        name: 'Ahmad Fauzi',
        department: 'Engineering',
        role: 'Senior Developer',
        status: 'Aktif',
        email: 'ahmad.fauzi@company.com',
        phone: '+62 812-3456-7890',
        salary: 'Rp 15.000.000',
        joined_at: '15 Mar 2021',
    },
    {
        id: 'EMP-002',
        avatar: 'https://i.pravatar.cc/80?img=5',
        name: 'Siti Rahayu',
        department: 'Design',
        role: 'UI/UX Designer',
        status: 'Aktif',
        email: 'siti.rahayu@company.com',
        phone: '+62 813-2345-6789',
        salary: 'Rp 12.500.000',
        joined_at: '01 Jul 2020',
    },
    {
        id: 'EMP-003',
        avatar: 'https://i.pravatar.cc/80?img=8',
        name: 'Budi Santoso',
        department: 'Product',
        role: 'Product Manager',
        status: 'Aktif',
        email: 'budi.santoso@company.com',
        phone: '+62 815-9876-5432',
        salary: 'Rp 18.000.000',
        joined_at: '20 Jan 2019',
    },
    {
        id: 'EMP-004',
        avatar: 'https://i.pravatar.cc/80?img=47',
        name: 'Dewi Lestari',
        department: 'HR',
        role: 'HR Manager',
        status: 'Cuti',
        email: 'dewi.lestari@company.com',
        phone: '+62 817-6543-2109',
        salary: 'Rp 14.000.000',
        joined_at: '05 Jun 2022',
    },
    {
        id: 'EMP-005',
        avatar: 'https://i.pravatar.cc/80?img=12',
        name: 'Eko Prasetyo',
        department: 'Finance',
        role: 'Finance Analyst',
        status: 'Aktif',
        email: 'eko.prasetyo@company.com',
        phone: '+62 819-1234-5678',
        salary: 'Rp 11.500.000',
        joined_at: '12 Sep 2023',
    },
    {
        id: 'EMP-006',
        avatar: 'https://i.pravatar.cc/80?img=20',
        name: 'Fitri Handayani',
        department: 'Engineering',
        role: 'Junior Developer',
        status: 'Nonaktif',
        email: 'fitri.h@company.com',
        phone: '+62 811-8765-4321',
        salary: 'Rp 8.500.000',
        joined_at: '08 Feb 2020',
    },
    {
        id: 'EMP-007',
        avatar: 'https://i.pravatar.cc/80?img=15',
        name: 'Galih Nugroho',
        department: 'Engineering',
        role: 'DevOps Engineer',
        status: 'Aktif',
        email: 'galih.n@company.com',
        phone: '+62 822-3456-7891',
        salary: 'Rp 16.000.000',
        joined_at: '30 Nov 2022',
    },
    {
        id: 'EMP-008',
        avatar: 'https://i.pravatar.cc/80?img=44',
        name: 'Hana Permata',
        department: 'Design',
        role: 'Intern',
        status: 'Aktif',
        email: 'hana.p@company.com',
        phone: '+62 831-2345-6789',
        salary: 'Rp 3.500.000',
        joined_at: '17 Apr 2024',
    },
    {
        id: 'EMP-009',
        avatar: 'https://i.pravatar.cc/80?img=33',
        name: 'Irfan Maulana',
        department: 'Engineering',
        role: 'Senior Developer',
        status: 'Aktif',
        email: 'irfan.m@company.com',
        phone: '+62 856-7654-3210',
        salary: 'Rp 17.500.000',
        joined_at: '02 Mar 2018',
    },
    {
        id: 'EMP-010',
        // avatar sengaja kosong → trigger fallback inisial
        avatar: '',
        name: 'Joko Widiyanto',
        department: 'Product',
        role: 'Product Manager',
        status: 'Cuti',
        email: 'joko.w@company.com',
        phone: '+62 877-1234-5678',
        salary: 'Rp 19.000.000',
        joined_at: '10 Oct 2017',
    },
]);

// ─── Dummy data: Tabel minimal (auto-column) ─────────────────────────────────

const simpleData = ref([
    { nama_produk: 'Laptop Asus ROG',     kategori: 'Elektronik', harga: 'Rp 18.500.000', stok: 12 },
    { nama_produk: 'Mechanical Keyboard', kategori: 'Peripheral',  harga: 'Rp 1.250.000',  stok: 45 },
    { nama_produk: 'Monitor 4K 27"',      kategori: 'Elektronik', harga: 'Rp 6.800.000',  stok: 8  },
    { nama_produk: 'Wireless Mouse',      kategori: 'Peripheral',  harga: 'Rp 320.000',    stok: 100 },
    { nama_produk: 'USB-C Hub 7-in-1',    kategori: 'Aksesoris',  harga: 'Rp 450.000',    stok: 60 },
]);

// ─── Action handlers ──────────────────────────────────────────────────────────

interface ToastState {
    message: string;
    variant: string;
    icon: string;
}

const toast = ref<ToastState | null>(null);

function showToast(message: string, variant: string, icon: string) {
    toast.value = { message, variant, icon };
    setTimeout(() => { toast.value = null; }, 3500);
}

function onViewProduct(row: Record<string, unknown>) {
    showToast(`Melihat: ${row.nama_produk}`, 'primary', 'ti-eye');
}

function onView(row: Record<string, unknown>) {
    showToast(`Melihat detail: ${row.name}`, 'primary', 'ti-eye');
}

function onEdit(row: Record<string, unknown>) {
    showToast(`Edit data: ${row.name}`, 'warning', 'ti-pencil');
}

function onDelete(row: Record<string, unknown>) {
    showToast(`Hapus: ${row.name}`, 'danger', 'ti-trash');
}
</script>
