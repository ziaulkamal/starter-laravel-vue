<template>
    <AppLayout title="Pengaturan Menu">

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
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold mb-0">Manajemen Menu</h5>
                    <button class="btn btn-primary btn-sm" @click="openCreate">
                        <i class="ti ti-plus me-1"></i> Tambah Menu
                    </button>
                </div>

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
                                <tr v-for="child in menu.children" :key="child.id" class="bg-light">
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
                                <td colspan="7" class="text-center text-muted py-4">Belum ada menu.</td>
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

                        <template v-if="form.type === 'item'">
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
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i :class="form.icon || 'ti ti-layout-sidebar'" class="fs-5"></i>
                                    </span>
                                    <input v-model="form.icon" type="text" class="form-control"
                                        :class="{ 'is-invalid': form.errors.icon }"
                                        placeholder="ti ti-home" />
                                    <div class="invalid-feedback">{{ form.errors.icon }}</div>
                                </div>
                                <div class="form-text">Gunakan class Tabler Icons, contoh: <code>ti ti-home</code></div>
                            </div>

                            <!-- Href -->
                            <div class="mb-3">
                                <label class="form-label fw-medium">Href / Route</label>
                                <input v-model="form.href" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.href }"
                                    placeholder="/dashboard" />
                                <div class="invalid-feedback">{{ form.errors.href }}</div>
                            </div>
                        </template>

                        <!-- Order Index -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Urutan</label>
                            <input v-model.number="form.order_index" type="number" class="form-control"
                                :class="{ 'is-invalid': form.errors.order_index }"
                                min="0" />
                            <div class="invalid-feedback">{{ form.errors.order_index }}</div>
                        </div>

                        <!-- Is Active -->
                        <div class="mb-1">
                            <div class="form-check form-switch">
                                <input v-model="form.is_active" class="form-check-input" type="checkbox"
                                    id="switchIsActive" />
                                <label class="form-check-label" for="switchIsActive">
                                    Menu aktif (tampil di sidebar)
                                </label>
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

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Modal } from 'bootstrap';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    menus: { type: Array, required: true },
});

// Only root items of type 'item' can be parents
const rootMenuItems = computed(() =>
    props.menus.filter(m => m.type === 'item')
);

// Flat unique list of all existing labels (root + children) for autocomplete
const allMenuLabels = computed(() => {
    const labels = new Set();
    for (const menu of props.menus) {
        labels.add(menu.label);
        for (const child of menu.children ?? []) {
            labels.add(child.label);
        }
    }
    return [...labels];
});

// ─── Form Modal ────────────────────────────────────────────────
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    parent_id:   null,
    type:        'item',
    label:       '',
    icon:        '',
    href:        '',
    order_index: 0,
    is_active:   true,
});

function resetForm() {
    form.parent_id   = null;
    form.type        = 'item';
    form.label       = '';
    form.icon        = '';
    form.href        = '';
    form.order_index = 0;
    form.is_active   = true;
    form.clearErrors();
}

function openCreate() {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    getModal('menuModal').show();
}

function openEdit(menu) {
    isEditing.value  = true;
    editingId.value  = menu.id;
    form.parent_id   = menu.parent_id ?? null;
    form.type        = menu.type;
    form.label       = menu.label;
    form.icon        = menu.icon ?? '';
    form.href        = menu.href ?? '';
    form.order_index = menu.order_index;
    form.is_active   = menu.is_active;
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
            onSuccess: () => {
                getModal('menuModal').hide();
                resetForm();
            },
        });
    }
}

// ─── Delete Modal ──────────────────────────────────────────────
const deleteTarget    = ref(null);
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
        onFinish: () => {
            deleteProcessing.value = false;
            getModal('deleteModal').hide();
        },
    });
}

// ─── Helper ────────────────────────────────────────────────────
function getModal(id) {
    return Modal.getOrCreateInstance(document.getElementById(id));
}
</script>
