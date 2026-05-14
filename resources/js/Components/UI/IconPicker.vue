<template>
    <!-- Trigger area -->
    <div>
        <div class="d-flex gap-2">
            <button type="button"
                class="btn btn-outline-secondary d-flex align-items-center gap-2 flex-grow-1"
                :class="{ 'is-invalid': hasError }"
                @click="openPicker">
                <template v-if="currentName">
                    <i :class="modelValue" class="fs-5"></i>
                    <span class="text-truncate small">{{ modelValue }}</span>
                </template>
                <template v-else>
                    <i class="ti ti-photo-off text-muted fs-5"></i>
                    <span class="text-muted small">Pilih icon...</span>
                </template>
            </button>
            <button v-if="currentName" type="button"
                class="btn btn-outline-danger"
                title="Hapus icon"
                @click="clearIcon">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="form-text">Klik untuk memilih icon Tabler. Kosongkan jika tidak perlu icon.</div>
    </div>

    <!-- Picker Modal (teleport ke body agar tidak terpotong modal parent) -->
    <Teleport to="body">
        <div class="modal fade" :id="modalId" tabindex="-1" aria-hidden="true" ref="modalEl">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header pb-2">
                        <h5 class="modal-title">Pilih Icon Tabler</h5>
                        <button type="button" class="btn-close" @click="closePicker"></button>
                    </div>

                    <!-- Search -->
                    <div class="px-3 pt-2 pb-1 border-bottom">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input ref="searchInput" v-model="query" type="text"
                                class="form-control"
                                placeholder="Cari icon... (contoh: home, user, settings)"
                                autocomplete="off" />
                            <button v-if="query" class="btn btn-outline-secondary" type="button"
                                @click="query = ''">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                        <div class="form-text mt-1">
                            Menampilkan {{ displayedIcons.length }} dari {{ filteredIcons.length }} icon
                            <span v-if="query">(filter: "{{ query }}")</span>
                        </div>
                    </div>

                    <!-- Icon grid -->
                    <div class="modal-body p-2" style="min-height: 400px;">
                        <div v-if="loading" class="d-flex justify-content-center align-items-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>

                        <div v-else-if="displayedIcons.length === 0" class="text-center text-muted py-5">
                            <i class="ti ti-mood-sad fs-1 d-block mb-2"></i>
                            Tidak ada icon yang cocok dengan "{{ query }}"
                        </div>

                        <div v-else class="row g-1 row-cols-auto">
                            <div v-for="name in displayedIcons" :key="name"
                                class="col">
                                <button type="button"
                                    class="icon-picker-item btn btn-sm d-flex flex-column align-items-center justify-content-center p-2 gap-1"
                                    :class="currentName === name ? 'btn-primary' : 'btn-outline-secondary'"
                                    :title="'ti ti-' + name"
                                    style="width: 72px; height: 64px;"
                                    @click="selectIcon(name)">
                                    <i :class="'ti ti-' + name" class="fs-4"></i>
                                    <span class="text-truncate w-100 text-center"
                                        style="font-size: 9px; line-height: 1.2;">
                                        {{ name }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Load more -->
                        <div v-if="!loading && filteredIcons.length > displayLimit"
                            class="text-center mt-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                @click="displayLimit += 200">
                                Tampilkan lebih banyak
                                ({{ filteredIcons.length - displayedIcons.length }} tersisa)
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer py-2">
                        <span class="text-muted small me-auto">
                            <template v-if="currentName">
                                Dipilih: <strong>{{ modelValue }}</strong>
                            </template>
                            <template v-else>Belum ada icon dipilih</template>
                        </span>
                        <button type="button" class="btn btn-light btn-sm" @click="closePicker">
                            Tutup
                        </button>
                        <button v-if="modelValue" type="button" class="btn btn-danger btn-sm"
                            @click="clearIcon">
                            <i class="ti ti-x me-1"></i>Hapus Icon
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { Modal } from 'bootstrap';

const props = defineProps({
    modelValue: { type: String, default: '' },
    hasError:   { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

// Unique modal ID to avoid conflict if multiple pickers exist
const modalId  = `icon-picker-modal-${Math.random().toString(36).slice(2, 7)}`;
const modalEl  = ref(null);
const searchInput = ref(null);

// modelValue stores full class e.g. "ti ti-home"; currentName is just "home"
const currentName = computed(() =>
    props.modelValue?.startsWith('ti ti-') ? props.modelValue.slice(6) : ''
);

const loading      = ref(false);
const icons        = ref([]);
const query        = ref('');
const displayLimit = ref(200);

// Load icon list lazily on first open
async function loadIcons() {
    if (icons.value.length) return;
    loading.value = true;
    const { tablerIcons } = await import('@/config/tabler-icons.js');
    icons.value   = tablerIcons;
    loading.value = false;
}

const filteredIcons = computed(() => {
    if (!query.value.trim()) return icons.value;
    const q = query.value.toLowerCase().trim();
    return icons.value.filter(name => name.includes(q));
});

const displayedIcons = computed(() =>
    filteredIcons.value.slice(0, displayLimit.value)
);

// Reset limit when search query changes
watch(query, () => { displayLimit.value = 200; });

function getModal() {
    return Modal.getOrCreateInstance(modalEl.value);
}

async function openPicker() {
    await loadIcons();
    getModal().show();
    await nextTick();
    searchInput.value?.focus();
}

function closePicker() {
    getModal().hide();
}

function selectIcon(name) {
    emit('update:modelValue', `ti ti-${name}`);
    closePicker();
}

function clearIcon() {
    emit('update:modelValue', '');
}
</script>
