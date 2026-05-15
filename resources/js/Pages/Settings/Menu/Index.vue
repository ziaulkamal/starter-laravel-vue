<template>
    <AppLayout
        title="Pengaturan Menu"
        :breadcrumb="[{ label: 'Pengaturan' }, { label: 'Pengaturan Menu' }]"
    >
        <template #page-actions>
            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
                    :disabled="!menus.length"
                    @click="openDestroyAll"
                >
                    <i class="ti ti-trash-x fs-5"></i>
                    <span class="d-none d-sm-inline">Hapus Semua</span>
                </button>
                <button
                    class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                    @click="openCreate"
                >
                    <i class="ti ti-plus fs-5"></i>
                    <span>Tambah Menu</span>
                </button>
            </div>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Label</th>
                                <th>Tipe</th>
                                <th>Icon</th>
                                <th>Href</th>
                                <th class="text-center">Urutan</th>
                                <th class="text-center">Status</th>
                                <th>Akses Role</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="menu in menus" :key="menu.id">
                                <!-- Root row -->
                                <tr>
                                    <td class="fw-semibold">{{ menu.label }}</td>
                                    <td>
                                        <span class="badge rounded-pill"
                                            :class="menu.type === 'section' ? 'text-bg-secondary' : 'text-bg-primary'">
                                            {{ menu.type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="menu.icon" class="d-flex align-items-center gap-1">
                                            <i :class="menu.icon"></i>
                                            <small class="text-muted">{{ menu.icon }}</small>
                                        </span>
                                        <span v-else class="text-muted">—</span>
                                    </td>
                                    <td><small class="text-muted">{{ menu.href ?? '—' }}</small></td>
                                    <td class="text-center">{{ menu.order_index }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill"
                                            :class="menu.is_active ? 'text-bg-success' : 'text-bg-secondary'">
                                            {{ menu.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <RoleBadges :roles="menu.roles" />
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Edit"
                                            @click="openEdit(menu)">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"
                                            @click="confirmDelete(menu)">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Child rows -->
                                <tr v-for="child in menu.children" :key="child.id" class="bg-body-tertiary">
                                    <td class="ps-4">
                                        <i class="ti ti-corner-down-right text-muted me-1 fs-5"></i>
                                        {{ child.label }}
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-primary">{{ child.type }}</span>
                                    </td>
                                    <td>
                                        <span v-if="child.icon" class="d-flex align-items-center gap-1">
                                            <i :class="child.icon"></i>
                                            <small class="text-muted">{{ child.icon }}</small>
                                        </span>
                                        <span v-else class="text-muted">—</span>
                                    </td>
                                    <td><small class="text-muted">{{ child.href ?? '—' }}</small></td>
                                    <td class="text-center">{{ child.order_index }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill"
                                            :class="child.is_active ? 'text-bg-success' : 'text-bg-secondary'">
                                            {{ child.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <RoleBadges :roles="child.roles" />
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Edit"
                                            @click="openEdit(child)">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"
                                            @click="confirmDelete(child)">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <tr v-if="!menus.length">
                                <td colspan="8" class="text-center text-muted py-4">Belum ada menu.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" @submit.prevent="submitForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="menuModalLabel">
                            {{ isEditing ? 'Edit Menu' : 'Tambah Menu' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Type -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Tipe <span class="text-danger">*</span></label>
                            <select v-model="form.type" class="form-select"
                                :class="{ 'is-invalid': form.errors.type }">
                                <option value="item">Item (link navigasi)</option>
                                <option value="section">Section (header group)</option>
                            </select>
                            <div class="invalid-feedback">{{ form.errors.type }}</div>
                        </div>

                        <!-- Label -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Label <span class="text-danger">*</span></label>
                            <input v-model="form.label" type="text" class="form-control"
                                :class="{ 'is-invalid': form.errors.label }"
                                list="datalist-menu-labels"
                                placeholder="Nama menu"
                                autocomplete="off" />
                            <datalist id="datalist-menu-labels">
                                <option v-for="label in allMenuLabels" :key="label" :value="label" />
                            </datalist>
                            <div class="invalid-feedback">{{ form.errors.label }}</div>
                        </div>

                        <div v-if="form.type === 'item'">
                            <!-- Parent -->
                            <div class="mb-3">
                                <label class="form-label fw-medium">Parent Menu</label>
                                <select v-model="form.parent_id" class="form-select"
                                    :class="{ 'is-invalid': form.errors.parent_id }">
                                    <option :value="null">— Tidak ada (root) —</option>
                                    <option v-for="m in rootMenuItems" :key="m.id" :value="m.id">
                                        {{ m.label }}
                                    </option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.parent_id }}</div>
                            </div>

                            <!-- Icon -->
                            <div class="mb-3">
                                <label class="form-label fw-medium">Icon</label>
                                <IconPicker
                                    :model-value="form.icon"
                                    :has-error="!!form.errors.icon"
                                    @update:model-value="form.icon = $event" />
                                <div v-if="form.errors.icon" class="text-danger small mt-1">
                                    {{ form.errors.icon }}
                                </div>
                            </div>

                            <!-- Href -->
                            <div class="mb-3">
                                <label class="form-label fw-medium">Href / Route</label>
                                <select v-model="form.href" class="form-select"
                                    :class="{ 'is-invalid': form.errors.href }">
                                    <option value="">— Pilih Route GET —</option>
                                    <option v-for="route in selectableRoutes" :key="route" :value="route">
                                        {{ route }}
                                    </option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.href }}</div>
                                <div class="form-text">
                                    Hanya route GET tanpa parameter yang tersedia.
                                    Route yang sudah dipakai menu lain tidak ditampilkan.
                                </div>
                            </div>
                        </div>

                        <!-- Order Index -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Urutan</label>
                            <input v-model.number="form.order_index" type="number" class="form-control"
                                :class="{ 'is-invalid': form.errors.order_index }"
                                min="0" />
                            <div class="invalid-feedback">{{ form.errors.order_index }}</div>
                        </div>

                        <!-- Is Active -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input v-model="form.is_active" class="form-check-input" type="checkbox"
                                    id="switchIsActive" />
                                <label class="form-check-label" for="switchIsActive">
                                    Menu aktif (tampil di sidebar)
                                </label>
                            </div>
                        </div>

                        <!-- Akses Role -->
                        <div class="mb-1">
                            <label class="form-label fw-medium d-flex align-items-center gap-1">
                                <i class="ti ti-shield-lock text-muted"></i>
                                Akses Role
                            </label>
                            <div class="d-flex flex-wrap gap-3">
                                <div v-for="role in roles" :key="role.id" class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :id="`role-${role.id}`"
                                        :value="role.id"
                                        v-model="form.role_ids"
                                    />
                                    <label class="form-check-label text-capitalize" :for="`role-${role.id}`">
                                        {{ role.name }}
                                    </label>
                                </div>
                            </div>
                            <div class="form-text">
                                Kosongkan agar menu terlihat oleh semua role.
                                Superadmin selalu bisa melihat semua menu.
                            </div>
                            <div v-if="form.errors.role_ids" class="text-danger small mt-1">
                                {{ form.errors.role_ids }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"
                                role="status"></span>
                            {{ isEditing ? 'Simpan Perubahan' : 'Tambah Menu' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-circle text-danger mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="fw-semibold mt-2">Hapus Menu?</h6>
                        <p class="text-muted mb-0 small">
                            <strong>{{ deleteTarget?.label }}</strong> akan dihapus beserta seluruh submenu-nya.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0 pb-4 gap-2">
                        <button type="button" class="btn btn-light btn-sm px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger btn-sm px-4"
                            :disabled="deleteProcessing" @click="doDelete">
                            <span v-if="deleteProcessing" class="spinner-border spinner-border-sm me-1"
                                role="status"></span>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Destroy All Confirmation Modal -->
        <div class="modal fade" id="destroyAllModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger">
                            <i class="ti ti-alert-triangle me-2"></i>Hapus Semua Menu
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Tindakan ini akan menghapus <strong>seluruh menu dan section</strong>
                            secara permanen dan tidak dapat dibatalkan.
                        </p>
                        <p class="mb-2 fw-medium">
                            Ketik <code class="text-danger fs-6">hapus menu</code> untuk konfirmasi:
                        </p>
                        <input v-model="destroyAllConfirm" type="text" class="form-control"
                            :class="{ 'is-valid': destroyAllConfirmValid, 'is-invalid': destroyAllConfirm && !destroyAllConfirmValid }"
                            placeholder="hapus menu"
                            autocomplete="off"
                            @keyup.enter="destroyAllConfirmValid && doDestroyAll()" />
                        <div class="valid-feedback">Konfirmasi sesuai.</div>
                        <div class="invalid-feedback">Teks tidak sesuai.</div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger"
                            :disabled="!destroyAllConfirmValid || destroyAllProcessing"
                            @click="doDestroyAll">
                            <span v-if="destroyAllProcessing"
                                class="spinner-border spinner-border-sm me-1" role="status"></span>
                            <i v-else class="ti ti-trash-x me-1"></i>
                            Hapus Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Modal } from 'bootstrap';
import AppLayout from '@/Layouts/AppLayout.vue';
import IconPicker from '@/Components/UI/IconPicker.vue';

// ── Sub-component: role badges ──────────────────────────────────
const RoleBadges = {
    props: { roles: Array },
    template: `
        <span v-if="roles?.length" class="d-flex flex-wrap gap-1">
            <span v-for="r in roles" :key="r.id"
                class="badge rounded-pill text-bg-warning text-capitalize">{{ r.name }}</span>
        </span>
        <span v-else class="text-muted small">Semua</span>
    `,
};

const props = defineProps({
    menus:           { type: Array, required: true },
    availableRoutes: { type: Array, required: true },
    usedHrefs:       { type: Array, required: true },
    roles:           { type: Array, required: true },
});

const rootMenuItems = computed(() =>
    props.menus.filter(m => m.type === 'item')
);

const allMenuLabels = computed(() => {
    const labels = new Set();
    for (const menu of props.menus) {
        labels.add(menu.label);
        for (const child of menu.children ?? []) labels.add(child.label);
    }
    return [...labels];
});

// ─── Form Modal ────────────────────────────────────────────────
const isEditing   = ref(false);
const editingId   = ref(null);
const originalHref = ref(null);

const form = useForm({
    parent_id:   null,
    type:        'item',
    label:       '',
    icon:        '',
    href:        '',
    order_index: 0,
    is_active:   true,
    role_ids:    [],
});

function resetForm() {
    form.parent_id   = null;
    form.type        = 'item';
    form.label       = '';
    form.icon        = '';
    form.href        = '';
    form.order_index = 0;
    form.is_active   = true;
    form.role_ids    = [];
    form.clearErrors();
}

const selectableRoutes = computed(() => {
    const used = new Set(props.usedHrefs);
    if (originalHref.value) used.delete(originalHref.value);
    return props.availableRoutes.filter(r => !used.has(r));
});

function openCreate() {
    isEditing.value    = false;
    editingId.value    = null;
    originalHref.value = null;
    resetForm();
    getModal('menuModal').show();
}

function openEdit(menu) {
    isEditing.value    = true;
    editingId.value    = menu.id;
    originalHref.value = menu.href ?? null;
    form.parent_id     = menu.parent_id ?? null;
    form.type          = menu.type;
    form.label         = menu.label;
    form.icon          = menu.icon ?? '';
    form.href          = menu.href ?? '';
    form.order_index   = menu.order_index;
    form.is_active     = menu.is_active;
    form.role_ids      = (menu.roles ?? []).map(r => r.id);
    form.clearErrors();
    getModal('menuModal').show();
}

function submitForm() {
    const options = {
        preserveScroll: true,
        onSuccess: () => getModal('menuModal').hide(),
    };

    if (isEditing.value) {
        form.put(`/settings/menus/${editingId.value}`, options);
    } else {
        form.post('/settings/menus', {
            ...options,
            onSuccess: () => { getModal('menuModal').hide(); resetForm(); },
        });
    }
}

// ─── Delete Modal ──────────────────────────────────────────────
const deleteTarget     = ref(null);
const deleteProcessing = ref(false);

function confirmDelete(menu) {
    deleteTarget.value = menu;
    getModal('deleteModal').show();
}

function doDelete() {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/settings/menus/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleteProcessing.value = false; getModal('deleteModal').hide(); },
    });
}

// ─── Destroy All Modal ─────────────────────────────────────────
const DESTROY_ALL_KEYWORD    = 'hapus menu';
const destroyAllConfirm      = ref('');
const destroyAllProcessing   = ref(false);
const destroyAllConfirmValid = computed(() => destroyAllConfirm.value === DESTROY_ALL_KEYWORD);

function openDestroyAll() {
    destroyAllConfirm.value = '';
    getModal('destroyAllModal').show();
}

function doDestroyAll() {
    if (!destroyAllConfirmValid.value) return;
    destroyAllProcessing.value = true;
    router.delete('/settings/menus/destroy-all', {
        preserveScroll: true,
        onFinish: () => { destroyAllProcessing.value = false; getModal('destroyAllModal').hide(); },
    });
}

function getModal(id) {
    return Modal.getOrCreateInstance(document.getElementById(id));
}
</script>
