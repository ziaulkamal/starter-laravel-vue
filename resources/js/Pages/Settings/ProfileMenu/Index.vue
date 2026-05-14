<template>
    <AppLayout title="Pengaturan Menu Profil">

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
                    <h5 class="card-title fw-semibold mb-0">Manajemen Menu Profil</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-danger btn-sm" @click="openDestroyAll"
                            :disabled="!items.length">
                            <i class="ti ti-trash-x me-1"></i> Hapus Semua
                        </button>
                        <button class="btn btn-primary btn-sm" @click="openCreate">
                            <i class="ti ti-plus me-1"></i> Tambah Item
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Label</th>
                                <th>Icon</th>
                                <th>Href</th>
                                <th class="text-center">Urutan</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id">
                                <td class="fw-semibold">{{ item.label }}</td>
                                <td>
                                    <span v-if="item.icon" class="d-flex align-items-center gap-1">
                                        <i :class="item.icon" class="fs-5"></i>
                                        <small class="text-muted">{{ item.icon }}</small>
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td><small class="text-muted">{{ item.href }}</small></td>
                                <td class="text-center">{{ item.order_index }}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill"
                                        :class="item.is_active ? 'text-bg-success' : 'text-bg-secondary'">
                                        {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary me-1" title="Edit"
                                        @click="openEdit(item)">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus"
                                        @click="confirmDelete(item)">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="6" class="text-center text-muted py-4">Belum ada item menu profil.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <div class="modal fade" id="profileMenuModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" @submit.prevent="submitForm">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'Edit Item' : 'Tambah Item' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Label -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Label <span class="text-danger">*</span></label>
                            <input v-model="form.label" type="text" class="form-control"
                                :class="{ 'is-invalid': form.errors.label }"
                                list="datalist-profile-labels"
                                placeholder="Nama item"
                                autocomplete="off" />
                            <datalist id="datalist-profile-labels">
                                <option v-for="l in allLabels" :key="l" :value="l" />
                            </datalist>
                            <div class="invalid-feedback">{{ form.errors.label }}</div>
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
                            <label class="form-label fw-medium">Href / Route <span class="text-danger">*</span></label>
                            <select v-model="form.href" class="form-select"
                                :class="{ 'is-invalid': form.errors.href }">
                                <option value="">— Pilih Route GET —</option>
                                <option v-for="route in selectableRoutes" :key="route" :value="route">
                                    {{ route }}
                                </option>
                            </select>
                            <div class="invalid-feedback">{{ form.errors.href }}</div>
                            <div class="form-text">
                                Route yang sudah dipakai item lain tidak ditampilkan.
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
                        <div class="mb-1">
                            <div class="form-check form-switch">
                                <input v-model="form.is_active" class="form-check-input" type="checkbox"
                                    id="switchProfileItemActive" />
                                <label class="form-check-label" for="switchProfileItemActive">
                                    Tampilkan di menu profil
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEditing ? 'Simpan Perubahan' : 'Tambah Item' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="profileDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-circle text-danger mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="fw-semibold mt-2">Hapus Item?</h6>
                        <p class="text-muted mb-0 small">
                            <strong>{{ deleteTarget?.label }}</strong> akan dihapus dari menu profil.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0 pb-4 gap-2">
                        <button type="button" class="btn btn-light btn-sm px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger btn-sm px-4"
                            :disabled="deleteProcessing" @click="doDelete">
                            <span v-if="deleteProcessing" class="spinner-border spinner-border-sm me-1"></span>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Destroy All Confirmation Modal -->
        <div class="modal fade" id="profileDestroyAllModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger">
                            <i class="ti ti-alert-triangle me-2"></i>Hapus Semua Item
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Seluruh item menu profil akan dihapus permanen.
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
                                class="spinner-border spinner-border-sm me-1"></span>
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

const props = defineProps({
    items:           { type: Array, required: true },
    availableRoutes: { type: Array, required: true },
    usedHrefs:       { type: Array, required: true },
});

const allLabels = computed(() => props.items.map(i => i.label));

// ─── Form Modal ────────────────────────────────────────────────
const isEditing    = ref(false);
const editingId    = ref(null);
const originalHref = ref(null);

const form = useForm({
    label:       '',
    icon:        '',
    href:        '',
    order_index: 0,
    is_active:   true,
});

const selectableRoutes = computed(() => {
    const used = new Set(props.usedHrefs);
    if (originalHref.value) used.delete(originalHref.value);
    return props.availableRoutes.filter(r => !used.has(r));
});

function resetForm() {
    form.label       = '';
    form.icon        = '';
    form.href        = '';
    form.order_index = 0;
    form.is_active   = true;
    form.clearErrors();
}

function openCreate() {
    isEditing.value    = false;
    editingId.value    = null;
    originalHref.value = null;
    resetForm();
    getModal('profileMenuModal').show();
}

function openEdit(item) {
    isEditing.value    = true;
    editingId.value    = item.id;
    originalHref.value = item.href ?? null;
    form.label         = item.label;
    form.icon          = item.icon ?? '';
    form.href          = item.href;
    form.order_index   = item.order_index;
    form.is_active     = item.is_active;
    form.clearErrors();
    getModal('profileMenuModal').show();
}

function submitForm() {
    const options = {
        preserveScroll: true,
        onSuccess: () => getModal('profileMenuModal').hide(),
    };

    if (isEditing.value) {
        form.put(`/settings/profile-menu/${editingId.value}`, options);
    } else {
        form.post('/settings/profile-menu', {
            ...options,
            onSuccess: () => {
                getModal('profileMenuModal').hide();
                resetForm();
            },
        });
    }
}

// ─── Delete Modal ──────────────────────────────────────────────
const deleteTarget     = ref(null);
const deleteProcessing = ref(false);

function confirmDelete(item) {
    deleteTarget.value = item;
    getModal('profileDeleteModal').show();
}

function doDelete() {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/settings/profile-menu/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            getModal('profileDeleteModal').hide();
        },
    });
}

// ─── Destroy All Modal ─────────────────────────────────────────
const DESTROY_ALL_KEYWORD    = 'hapus menu';
const destroyAllConfirm      = ref('');
const destroyAllProcessing   = ref(false);
const destroyAllConfirmValid = computed(
    () => destroyAllConfirm.value === DESTROY_ALL_KEYWORD
);

function openDestroyAll() {
    destroyAllConfirm.value = '';
    getModal('profileDestroyAllModal').show();
}

function doDestroyAll() {
    if (!destroyAllConfirmValid.value) return;
    destroyAllProcessing.value = true;
    router.delete('/settings/profile-menu/destroy-all', {
        preserveScroll: true,
        onFinish: () => {
            destroyAllProcessing.value = false;
            getModal('profileDestroyAllModal').hide();
        },
    });
}

// ─── Helper ────────────────────────────────────────────────────
function getModal(id) {
    return Modal.getOrCreateInstance(document.getElementById(id));
}
</script>
