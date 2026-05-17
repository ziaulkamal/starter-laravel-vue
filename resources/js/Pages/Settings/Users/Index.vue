<template>
    <AppLayout
        title="Manajemen User"
        :breadcrumb="[{ label: 'Pengaturan' }, { label: 'Manajemen User' }]"
    >
        <template #page-actions>
            <button
                class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                @click="openCreate"
            >
                <i class="ti ti-plus fs-5"></i>
                <span>Tambah User</span>
            </button>
        </template>

        <!-- Flash -->
        <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            {{ $page.props.flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            {{ $page.props.flash.error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <AppTable
                    :data="users"
                    :columns="columns"
                    :actions="{ edit: true, delete: true }"
                    :action-permissions="{ edit: 'users.edit', delete: 'users.delete' }"
                    :row-can-edit="rowCanEdit"
                    :row-can-delete="rowCanDelete"
                    :hover="true"
                    :show-row-numbers="true"
                    empty-text="Belum ada user terdaftar"
                    @edit="openEdit"
                    @delete="confirmDelete"
                >
                    <template #controls-left>
                        <span class="text-muted small">
                            <i class="ti ti-users me-1"></i>
                            {{ users.length }} user
                        </span>
                    </template>

                    <template #cell-name="{ value, row }">
                        <div class="d-flex align-items-center gap-2">
                            <span
                                v-if="row.avatar"
                                class="avatar avatar-sm rounded-circle overflow-hidden"
                                style="width:32px;height:32px"
                            >
                                <img :src="(row.avatar as string)" :alt="(value as string)" class="w-100 h-100 object-fit-cover" />
                            </span>
                            <span
                                v-else
                                class="avatar avatar-sm rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-semibold"
                                style="width:32px;height:32px;font-size:0.75rem"
                            >
                                {{ initials(value as string) }}
                            </span>
                            <span class="fw-medium">{{ value }}</span>
                        </div>
                    </template>

                    <template #cell-roles="{ value }">
                        <span
                            v-for="role in (value as string[])"
                            :key="role"
                            class="badge rounded-pill text-bg-primary me-1"
                        >{{ role }}</span>
                        <span v-if="!(value as string[]).length" class="text-muted small">—</span>
                    </template>

                    <template #cell-kafilah_nama="{ value }">
                        <span v-if="value" class="badge text-bg-light text-dark border">{{ value }}</span>
                        <span v-else class="text-muted small">—</span>
                    </template>

                    <template #cell-login_method="{ value }">
                        <span v-if="value === 'google'" class="badge d-inline-flex align-items-center gap-1" style="background:#f1f3f4;color:#3c4043;font-weight:500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66 2.84-.18-.68z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Google
                        </span>
                        <span v-else-if="value === 'sso'" class="badge text-bg-primary d-inline-flex align-items-center gap-1">
                            <i class="ti ti-shield-lock" style="font-size:0.7rem"></i>
                            SSO
                        </span>
                        <span v-else class="badge text-bg-secondary d-inline-flex align-items-center gap-1">
                            <i class="ti ti-key" style="font-size:0.7rem"></i>
                            Password
                        </span>
                    </template>
                </AppTable>
            </div>
        </div>

        <!-- Form Modal -->
        <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" @submit.prevent="submitForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">
                            {{ isEditing ? 'Edit User' : 'Tambah User' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Name -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama <span class="text-danger">*</span></label>
                            <input v-model="form.name" type="text" class="form-control"
                                :class="{ 'is-invalid': form.errors.name }"
                                placeholder="Nama lengkap" />
                            <div class="invalid-feedback">{{ form.errors.name }}</div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                            <input v-model="form.email" type="email" class="form-control"
                                :class="{ 'is-invalid': form.errors.email }"
                                placeholder="email@domain.com" />
                            <div class="invalid-feedback">{{ form.errors.email }}</div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Password
                                <span v-if="!isEditing" class="text-danger">*</span>
                                <span v-else class="text-muted fw-normal small">(kosongkan jika tidak diubah)</span>
                            </label>
                            <input v-model="form.password" type="password" class="form-control"
                                :class="{ 'is-invalid': form.errors.password }"
                                placeholder="Minimal 8 karakter" />
                            <div class="invalid-feedback">{{ form.errors.password }}</div>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Role</label>
                            <select v-model="form.role" class="form-select"
                                :class="{ 'is-invalid': form.errors.role }">
                                <option value="">— Tanpa role —</option>
                                <option v-for="r in selectableRoles" :key="r" :value="r">{{ r }}</option>
                            </select>
                            <div class="invalid-feedback">{{ form.errors.role }}</div>
                        </div>

                        <!-- Kafilah (hanya tampil jika role = user) -->
                        <div v-if="form.role === 'user'" class="mb-3">
                            <label class="form-label fw-medium">
                                Kafilah (Kabupaten/Kota)
                                <span class="text-danger">*</span>
                            </label>
                            <select v-model="form.kafilah_id" class="form-select"
                                :class="{ 'is-invalid': form.errors.kafilah_id }">
                                <option :value="null">— Pilih Kafilah —</option>
                                <option v-for="k in kafilahs" :key="k.id" :value="k.id">
                                    {{ k.nama_kabupaten }}
                                </option>
                            </select>
                            <div class="form-text">User hanya bisa mengakses data dari kafilah ini.</div>
                            <div class="invalid-feedback">{{ form.errors.kafilah_id }}</div>
                        </div>

                        <!-- Avatar URL -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Avatar URL</label>
                            <input v-model="form.avatar" type="url" class="form-control"
                                :class="{ 'is-invalid': form.errors.avatar }"
                                placeholder="https://..." />
                            <div class="invalid-feedback">{{ form.errors.avatar }}</div>
                        </div>

                        <!-- Is Active -->
                        <div class="mb-1">
                            <div class="form-check form-switch">
                                <input v-model="form.is_active" class="form-check-input" type="checkbox"
                                    id="switchUserActive" />
                                <label class="form-check-label" for="switchUserActive">
                                    Akun aktif
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            {{ isEditing ? 'Simpan Perubahan' : 'Tambah User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-circle text-danger mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="fw-semibold mt-2">Hapus User?</h6>
                        <p class="text-muted mb-0 small">
                            <strong>{{ deleteTarget?.name }}</strong> akan dihapus secara permanen.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0 pb-4 gap-2">
                        <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger btn-sm px-4"
                            :disabled="deleteProcessing" @click="doDelete">
                            <span v-if="deleteProcessing" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onBeforeUnmount } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import { Modal } from 'bootstrap';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import type { TableColumn } from '@/Components/UI/AppTable.vue';

interface UserRow {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    is_active: boolean;
    last_login_at: string | null;
    login_method: 'password' | 'google' | 'sso';
    roles: string[];
    kafilah_id: number | null;
    kafilah_nama: string | null;
}

interface KafilahOption {
    id: number;
    nama_kabupaten: string;
}

const props = defineProps<{
    users: UserRow[];
    roles: string[];
    kafilahs: KafilahOption[];
}>();

const page = usePage();
const authRoles = computed<string[]>(() => (page.props.auth as any)?.roles ?? []);
const isSuperAdmin = computed(() => authRoles.value.includes('superadmin'));

const selectableRoles = computed(() =>
    isSuperAdmin.value ? props.roles : props.roles.filter(r => r !== 'superadmin')
);

const rowCanEdit   = (row: Record<string, unknown>) =>
    isSuperAdmin.value || !(row.roles as string[]).includes('superadmin');
const rowCanDelete = (row: Record<string, unknown>) =>
    isSuperAdmin.value || !(row.roles as string[]).includes('superadmin');

const columns: TableColumn[] = [
    { key: 'name',         label: 'Nama' },
    { key: 'email',        label: 'Email' },
    { key: 'roles',        label: 'Role' },
    { key: 'kafilah_nama', label: 'Kafilah' },
    { key: 'login_method', label: 'Metode Login' },
    {
        key: 'is_active',
        label: 'Status',
        type: 'badge',
        badgeMap: { true: 'success', false: 'secondary' },
    },
    { key: 'last_login_at', label: 'Login Terakhir', hidden: true },
];

function initials(name: string): string {
    return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
}

// ── Form Modal ──────────────────────────────────────────────────────────
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name:       '',
    email:      '',
    password:   '',
    role:       '',
    avatar:     '',
    is_active:  true,
    kafilah_id: null as number | null,
});

function resetForm() {
    form.name       = '';
    form.email      = '';
    form.password   = '';
    form.role       = '';
    form.avatar     = '';
    form.is_active  = true;
    form.kafilah_id = null;
    form.clearErrors();
}

function openCreate() {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    getModal('userModal').show();
}

function openEdit(row: Record<string, unknown>) {
    const user = row as unknown as UserRow;
    isEditing.value     = true;
    editingId.value     = user.id;
    form.name           = user.name;
    form.email          = user.email;
    form.password       = '';
    form.role           = user.roles[0] ?? '';
    form.avatar         = user.avatar ?? '';
    form.is_active      = user.is_active;
    form.kafilah_id     = user.kafilah_id;
    form.clearErrors();
    getModal('userModal').show();
}

function submitForm() {
    const options = {
        preserveScroll: true,
        onSuccess: () => getModal('userModal').hide(),
    };

    if (isEditing.value && editingId.value) {
        form.put(`/settings/users/${editingId.value}`, options);
    } else {
        form.post('/settings/users', {
            ...options,
            onSuccess: () => {
                getModal('userModal').hide();
                resetForm();
            },
        });
    }
}

// ── Delete Modal ────────────────────────────────────────────────────────
const deleteTarget    = ref<UserRow | null>(null);
const deleteProcessing = ref(false);

function confirmDelete(row: Record<string, unknown>) {
    deleteTarget.value = row as unknown as UserRow;
    getModal('deleteUserModal').show();
}

function doDelete() {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/settings/users/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            getModal('deleteUserModal').hide();
        },
    });
}

function getModal(id: string) {
    return Modal.getOrCreateInstance(document.getElementById(id)!);
}

onBeforeUnmount(() => {
    ['userModal', 'deleteUserModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) Modal.getInstance(el)?.dispose();
    });
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
});
</script>
