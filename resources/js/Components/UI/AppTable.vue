<script lang="ts">
// Named exports — importable by consumers
export interface TableColumn {
    key: string;
    label?: string;
    /** Sembunyikan kolom ini secara default (masih bisa di-toggle via UI) */
    hidden?: boolean;
    /** Tipe tampilan cell */
    type?: 'text' | 'badge' | 'image' | 'avatar' | 'sensitive';

    // ── Badge ──────────────────────────────────────────────
    /** Nama varian Bootstrap: 'primary' | 'success' | 'danger' | ... */
    badgeVariant?: string | ((value: unknown, row: Record<string, unknown>) => string);
    /** Mapping nilai → varian, mis: { Aktif: 'success', Nonaktif: 'danger' } */
    badgeMap?: Record<string, string>;

    // ── Image / Avatar ─────────────────────────────────────
    imageSize?: number;       // px, default 40
    imageRound?: boolean;     // default true untuk avatar
    /** Key field lain yang dipakai untuk initial/label teks */
    fallbackKey?: string;
    /** Tampilkan nilai fallbackKey sebagai teks di samping gambar */
    showLabelWithImage?: boolean;

    // ── Extra classes ──────────────────────────────────────
    thClass?: string | string[];
    tdClass?: string | string[];
}

export interface TableActions {
    view?: boolean;
    edit?: boolean;
    delete?: boolean;
}
</script>

<script setup lang="ts">
import { computed, reactive, ref, useSlots, watch, watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';
// import type { TableColumn, TableActions } from './AppTable.vue';

type InternalColumn = TableColumn & { label: string };

interface ActionPermissions {
    view?: string;
    edit?: string;
    delete?: string;
}

interface Props {
    data: Record<string, unknown>[];
    columns?: TableColumn[];
    /** true = tampilkan semua aksi; object = pilih spesifik */
    actions?: boolean | TableActions;
    /**
     * Kunci permission per aksi. Jika tidak didefinisikan → tampil default.
     * Jika didefinisikan → cek terhadap page.props.auth.permissions (string[]).
     * Jika array permissions belum ada di props → tetap tampil (default allow).
     */
    actionPermissions?: ActionPermissions;
    /** Override daftar permission (untuk demo/testing). Jika diisi, tidak membaca page.props. */
    permissionList?: string[];
    /** Per-row guard: jika di-set, tombol view hanya muncul jika fungsi return true */
    rowCanView?: (row: Record<string, unknown>) => boolean;
    /** Per-row guard: jika di-set, tombol edit hanya muncul jika fungsi return true */
    rowCanEdit?: (row: Record<string, unknown>) => boolean;
    /** Per-row guard: jika di-set, tombol delete hanya muncul jika fungsi return true */
    rowCanDelete?: (row: Record<string, unknown>) => boolean;
    striped?: boolean;
    hover?: boolean;
    bordered?: boolean;
    small?: boolean;
    showRowNumbers?: boolean;
    showColumnToggle?: boolean;
    showSearch?: boolean;
    showPagination?: boolean;
    perPageOptions?: number[];
    emptyText?: string;
    caption?: string;
    selectable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    data: () => [],
    hover: true,
    showColumnToggle: true,
    showSearch: true,
    showPagination: true,
    perPageOptions: () => [10, 25, 50, 100],
    selectable: false,
});

const emit = defineEmits<{
    view: [row: Record<string, unknown>, index: number];
    edit: [row: Record<string, unknown>, index: number];
    delete: [row: Record<string, unknown>, index: number];
    'update:selected': [rows: Record<string, unknown>[]];
}>();

const slots = useSlots();
const page = usePage();

// ─── Permission gate ──────────────────────────────────────────────────────────

function can(permissionKey: string | undefined): boolean {
    if (!permissionKey) return true;
    const perms = props.permissionList
        ?? (page.props.auth as Record<string, unknown>)?.permissions as string[] | undefined;
    if (!perms) return true;
    return perms.includes(permissionKey);
}

// ─── Search & Pagination ─────────────────────────────────────────────────────

const searchQuery = ref('');
const perPage = ref(props.perPageOptions[0] ?? 10);
const currentPage = ref(1);

watch([searchQuery, perPage], () => { currentPage.value = 1; });

// ─── Visibility state ────────────────────────────────────────────────────────

const visibilityState = ref<Record<string, boolean>>({});
const imgErrors = reactive(new Set<string>());
const revealedCells = reactive(new Set<string>());

function cellKey(rowIdx: number, colKey: string): string {
    return `${rowIdx}:${colKey}`;
}

function formatKey(key: string): string {
    return key
        .replace(/_/g, ' ')
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .replace(/\b\w/g, c => c.toUpperCase());
}

// ─── Columns ─────────────────────────────────────────────────────────────────

const internalColumns = computed<InternalColumn[]>(() => {
    const defs: TableColumn[] =
        props.columns && props.columns.length > 0
            ? props.columns
            : props.data.length > 0
                ? Object.keys(props.data[0]).map(k => ({ key: k }))
                : [];
    return defs.map(col => ({ ...col, label: col.label ?? formatKey(col.key) }));
});

watch(
    internalColumns,
    cols => {
        cols.forEach(col => {
            if (!(col.key in visibilityState.value)) {
                visibilityState.value[col.key] = col.hidden !== true;
            }
        });
    },
    { immediate: true },
);

const activeColumns = computed(() =>
    internalColumns.value.filter(col => visibilityState.value[col.key] !== false),
);

function toggleColumnVisibility(key: string) {
    visibilityState.value[key] = !visibilityState.value[key];
}

// ─── Filtered & Paginated Data ────────────────────────────────────────────────

const filteredData = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.data;
    return props.data.filter(row =>
        internalColumns.value.some(col => {
            const val = row[col.key];
            return val != null && String(val).toLowerCase().includes(q);
        }),
    );
});

const totalPages = computed(() =>
    props.showPagination === false
        ? 1
        : Math.max(1, Math.ceil(filteredData.value.length / perPage.value)),
);

const pageStart = computed(() =>
    props.showPagination === false ? 0 : (currentPage.value - 1) * perPage.value,
);

const paginatedData = computed(() =>
    props.showPagination === false
        ? filteredData.value
        : filteredData.value.slice(pageStart.value, pageStart.value + perPage.value),
);

const displayPages = computed(() => {
    const total = totalPages.value;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1) as (number | null)[];
    const cur = currentPage.value;
    const pages: (number | null)[] = [1];
    if (cur > 3) pages.push(null);
    for (let p = Math.max(2, cur - 1); p <= Math.min(total - 1, cur + 1); p++) pages.push(p);
    if (cur < total - 2) pages.push(null);
    pages.push(total);
    return pages;
});

const showingText = computed(() => {
    const total = filteredData.value.length;
    if (total === 0) return '';
    if (props.showPagination === false) return `${total} data`;
    const from = pageStart.value + 1;
    const to = Math.min(pageStart.value + perPage.value, total);
    return `${from}–${to} dari ${total} data`;
});

const hasSensitive = computed(() =>
    internalColumns.value.some(col => col.type === 'sensitive')
);

// ─── Actions ─────────────────────────────────────────────────────────────────

const resolvedActions = computed<Required<TableActions>>(() => {
    const ap = props.actionPermissions ?? {};
    if (!props.actions) return { view: false, edit: false, delete: false };
    if (props.actions === true) return {
        view: can(ap.view),
        edit: can(ap.edit),
        delete: can(ap.delete),
    };
    return {
        view: (props.actions.view ?? false) && can(ap.view),
        edit: (props.actions.edit ?? false) && can(ap.edit),
        delete: (props.actions.delete ?? false) && can(ap.delete),
    };
});

const hasActions = computed(() =>
    resolvedActions.value.view || resolvedActions.value.edit || resolvedActions.value.delete
    || !!slots['row-actions'],
);

// ─── Table meta ───────────────────────────────────────────────────────────────

const tableClasses = computed(() => [
    'table mb-0',
    { 'table-striped': props.striped },
    { 'table-hover': props.hover },
    { 'table-bordered': props.bordered },
    { 'table-sm': props.small },
]);

const totalColspan = computed(
    () =>
        activeColumns.value.length +
        (props.selectable ? 1 : 0) +
        (props.showRowNumbers ? 1 : 0) +
        (hasActions.value ? 1 : 0),
);

const hasControls = computed(
    () =>
        props.showColumnToggle !== false ||
        props.showSearch !== false ||
        !!slots['controls-left'] ||
        !!slots['controls-right'],
);

// ─── Badge ────────────────────────────────────────────────────────────────────

function resolveBadgeClass(
    col: InternalColumn,
    value: unknown,
    row: Record<string, unknown>,
): string {
    if (typeof col.badgeVariant === 'function') {
        return `bg-${col.badgeVariant(value, row)}`;
    }
    if (col.badgeMap) {
        const mapped = col.badgeMap[String(value)];
        if (mapped) return `bg-${mapped}`;
    }
    return `bg-${typeof col.badgeVariant === 'string' ? col.badgeVariant : 'secondary'}`;
}

// ─── Image / Avatar ───────────────────────────────────────────────────────────

function avatarStyle(col: InternalColumn): Record<string, string> {
    const size = col.imageSize ?? 40;
    return {
        width: `${size}px`,
        height: `${size}px`,
        minWidth: `${size}px`,
        fontSize: `${Math.round(size * 0.35)}px`,
        backgroundColor: 'var(--bs-primary-bg-subtle)',
        color: 'var(--bs-primary)',
    };
}

function imgStyle(col: InternalColumn): Record<string, string> {
    const size = col.imageSize ?? 40;
    return { width: `${size}px`, height: `${size}px`, minWidth: `${size}px`, objectFit: 'cover' };
}

function getInitials(text: string): string {
    return text
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(w => w[0])
        .join('')
        .toUpperCase();
}

// ─── Sensitive / reveal ───────────────────────────────────────────────────────

function isRevealed(rowIdx: number, colKey: string): boolean {
    return revealedCells.has(cellKey(rowIdx, colKey));
}

function toggleReveal(rowIdx: number, colKey: string) {
    const k = cellKey(rowIdx, colKey);
    revealedCells.has(k) ? revealedCells.delete(k) : revealedCells.add(k);
}

// ─── Selection ────────────────────────────────────────────────────────────────

const selectedIds       = reactive(new Set<string>());
const headerCheckboxRef = ref<HTMLInputElement | null>(null);

function rowKey(row: Record<string, unknown>): string {
    return String(row.id ?? JSON.stringify(row));
}

function isRowSelected(row: Record<string, unknown>): boolean {
    return selectedIds.has(rowKey(row));
}

const allPageSelected = computed(() =>
    paginatedData.value.length > 0 &&
    paginatedData.value.every(row => selectedIds.has(rowKey(row)))
);

const somePageSelected = computed(() =>
    !allPageSelected.value && paginatedData.value.some(row => selectedIds.has(rowKey(row)))
);

function toggleRow(row: Record<string, unknown>): void {
    const k = rowKey(row);
    selectedIds.has(k) ? selectedIds.delete(k) : selectedIds.add(k);
    emitSelected();
}

function toggleAll(): void {
    if (allPageSelected.value) {
        paginatedData.value.forEach(row => selectedIds.delete(rowKey(row)));
    } else {
        paginatedData.value.forEach(row => selectedIds.add(rowKey(row)));
    }
    emitSelected();
}

function emitSelected(): void {
    emit('update:selected', props.data.filter(row => selectedIds.has(rowKey(row))));
}

watchEffect(() => {
    if (headerCheckboxRef.value) {
        headerCheckboxRef.value.indeterminate = somePageSelected.value;
    }
});

watch(() => props.data, () => {
    selectedIds.clear();
    emitSelected();
});

// ─── Custom slots ─────────────────────────────────────────────────────────────

function hasCellSlot(key: string): boolean {
    return !!slots[`cell-${key}`];
}
</script>

<template>
    <div class="app-table-wrapper">

        <!-- Controls bar -->
        <div
            v-if="hasControls"
            class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"
        >
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <!-- Search input -->
                <div v-if="showSearch !== false" class="app-search-wrap">
                    <span class="app-search-icon text-muted">
                        <i class="ti ti-search fs-5"></i>
                    </span>
                    <input
                        v-model="searchQuery"
                        type="search"
                        class="form-control form-control-sm app-search-input"
                        placeholder="Cari data..."
                        autocomplete="off"
                    />
                </div>

                <slot name="controls-left" />
                <span
                    v-if="selectable && selectedIds.size > 0"
                    class="badge bg-primary-subtle text-primary rounded-pill px-2 small"
                >
                    {{ selectedIds.size }} terpilih
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <slot name="controls-right" />

                <!-- Per-page selector -->
                <select
                    v-if="showPagination !== false"
                    v-model="perPage"
                    class="form-select form-select-sm app-perpage-select"
                    title="Jumlah baris per halaman"
                >
                    <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }} / hal</option>
                </select>

                <!-- Column toggle dropdown -->
                <div v-if="showColumnToggle !== false" class="dropdown">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <i class="ti ti-columns-3 fs-5"></i>
                        <span class="d-none d-sm-inline">Kolom</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm app-col-dropdown p-0">
                        <!-- Header -->
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <span class="app-col-header">Tampilkan Kolom</span>
                            <span class="badge rounded-pill bg-primary-subtle text-primary app-col-count">
                                {{ Object.values(visibilityState).filter(v => v !== false).length }}
                            </span>
                        </div>
                        <!-- List -->
                        <div class="app-col-list">
                            <label
                                v-for="col in internalColumns"
                                :key="col.key"
                                class="app-col-toggle-item d-flex align-items-center gap-2 px-3 py-2"
                                :class="{ 'app-col-active': visibilityState[col.key] !== false }"
                            >
                                <input
                                    type="checkbox"
                                    class="form-check-input mt-0 flex-shrink-0"
                                    :checked="visibilityState[col.key] !== false"
                                    @change="toggleColumnVisibility(col.key)"
                                />
                                <span class="flex-grow-1">{{ col.label }}</span>
                                <i
                                    v-if="visibilityState[col.key] !== false"
                                    class="ti ti-check text-primary app-col-check"
                                ></i>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive rounded-3 border border-1" style="border-color: var(--bs-border-color) !important;">
            <table :class="tableClasses">
                <caption v-if="caption" class="px-3 pb-2">{{ caption }}</caption>

                <thead class="app-thead">
                    <tr>
                        <th v-if="selectable" class="app-th-select" scope="col">
                            <input
                                ref="headerCheckboxRef"
                                type="checkbox"
                                class="form-check-input mt-0"
                                :checked="allPageSelected"
                                @change="toggleAll"
                            />
                        </th>
                        <th v-if="showRowNumbers" class="text-center app-th-no" scope="col">#</th>
                        <th
                            v-for="col in activeColumns"
                            :key="col.key"
                            scope="col"
                            :class="col.thClass"
                        >
                            {{ col.label }}
                        </th>
                        <th v-if="hasActions" class="text-center app-th-actions" scope="col">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Empty state -->
                    <tr v-if="paginatedData.length === 0">
                        <td :colspan="totalColspan" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2 text-muted py-2">
                                <i class="ti ti-table-off app-empty-icon"></i>
                                <span class="small">
                                    {{ data.length > 0
                                        ? 'Tidak ada data yang cocok dengan pencarian'
                                        : (emptyText ?? 'Tidak ada data untuk ditampilkan') }}
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- Data rows -->
                    <tr
                        v-for="(row, rowIdx) in paginatedData"
                        :key="pageStart + rowIdx"
                        class="align-middle"
                        :class="{ 'table-active': selectable && isRowSelected(row) }"
                    >
                        <!-- Select checkbox -->
                        <td v-if="selectable" class="app-th-select">
                            <input
                                type="checkbox"
                                class="form-check-input mt-0"
                                :checked="isRowSelected(row)"
                                @change="toggleRow(row)"
                            />
                        </td>
                        <!-- Row number -->
                        <td v-if="showRowNumbers" class="text-center text-muted small app-th-no">
                            {{ pageStart + rowIdx + 1 }}
                        </td>

                        <!-- Data cells -->
                        <td
                            v-for="col in activeColumns"
                            :key="col.key"
                            :class="col.tdClass"
                        >
                            <!-- ① Custom slot override: <template #cell-fieldname="{ value, row, index }"> -->
                            <template v-if="hasCellSlot(col.key)">
                                <slot
                                    :name="`cell-${col.key}`"
                                    :value="row[col.key]"
                                    :row="row"
                                    :index="pageStart + rowIdx"
                                />
                            </template>

                            <!-- ② Avatar / Image -->
                            <template v-else-if="col.type === 'avatar' || col.type === 'image'">
                                <div class="d-flex align-items-center gap-2">
                                    <img
                                        v-if="!imgErrors.has(cellKey(pageStart + rowIdx, col.key))"
                                        :src="String(row[col.key] ?? '')"
                                        :alt="String(col.fallbackKey ? (row[col.fallbackKey] ?? '') : '')"
                                        class="app-table-img flex-shrink-0"
                                        :class="{ 'rounded-circle': col.imageRound !== false }"
                                        :style="imgStyle(col)"
                                        @error="imgErrors.add(cellKey(pageStart + rowIdx, col.key))"
                                    />
                                    <!-- Fallback initials -->
                                    <div
                                        v-else
                                        class="app-avatar-fallback flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center fw-bold text-uppercase"
                                        :style="avatarStyle(col)"
                                    >
                                        {{ getInitials(String(col.fallbackKey ? (row[col.fallbackKey] ?? '') : (row[col.key] ?? ''))) }}
                                    </div>
                                    <!-- Label di samping gambar -->
                                    <div v-if="col.showLabelWithImage && col.fallbackKey" class="lh-sm">
                                        <span class="fw-medium d-block">{{ row[col.fallbackKey] }}</span>
                                    </div>
                                </div>
                            </template>

                            <!-- ③ Badge -->
                            <template v-else-if="col.type === 'badge'">
                                <span
                                    :class="['badge rounded-pill fw-medium', resolveBadgeClass(col, row[col.key], row)]"
                                >
                                    {{ row[col.key] }}
                                </span>
                            </template>

                            <!-- ④ Sensitive / censored -->
                            <template v-else-if="col.type === 'sensitive'">
                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="font-monospace small"
                                        :class="{ 'app-censored': !isRevealed(pageStart + rowIdx, col.key) }"
                                    >
                                        {{ isRevealed(pageStart + rowIdx, col.key) ? row[col.key] : '••••••••' }}
                                    </span>
                                    <button
                                        type="button"
                                        class="btn btn-link btn-sm p-0 text-muted app-reveal-btn"
                                        :title="isRevealed(pageStart + rowIdx, col.key) ? 'Sembunyikan' : 'Tampilkan'"
                                        @click="toggleReveal(pageStart + rowIdx, col.key)"
                                    >
                                        <i
                                            :class="[
                                                'fs-5',
                                                isRevealed(pageStart + rowIdx, col.key)
                                                    ? 'ti ti-eye-off'
                                                    : 'ti ti-eye',
                                            ]"
                                        ></i>
                                    </button>
                                </div>
                            </template>

                            <!-- ⑤ Default text -->
                            <template v-else>
                                <span :class="{ 'text-muted': row[col.key] == null }">
                                    {{ row[col.key] ?? '—' }}
                                </span>
                            </template>
                        </td>

                        <!-- Actions cell -->
                        <td v-if="hasActions">
                            <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                <button
                                    v-if="resolvedActions.view && (!rowCanView || rowCanView(row))"
                                    type="button"
                                    class="btn btn-sm bg-primary-subtle text-primary app-action-btn"
                                    title="Lihat detail"
                                    @click="emit('view', row, pageStart + rowIdx)"
                                >
                                    <i class="ti ti-eye fs-5"></i>
                                </button>
                                <button
                                    v-if="resolvedActions.edit && (!rowCanEdit || rowCanEdit(row))"
                                    type="button"
                                    class="btn btn-sm bg-warning-subtle text-warning app-action-btn"
                                    title="Edit"
                                    @click="emit('edit', row, pageStart + rowIdx)"
                                >
                                    <i class="ti ti-pencil fs-5"></i>
                                </button>
                                <button
                                    v-if="resolvedActions.delete && (!rowCanDelete || rowCanDelete(row))"
                                    type="button"
                                    class="btn btn-sm bg-danger-subtle text-danger app-action-btn"
                                    title="Hapus"
                                    @click="emit('delete', row, pageStart + rowIdx)"
                                >
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                                <slot name="row-actions" :row="row" :index="pageStart + rowIdx" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination footer -->
        <div
            v-if="showPagination !== false && totalPages > 1"
            class="app-pagination-bar d-flex align-items-center justify-content-end flex-wrap gap-3"
        >
            <!-- Navigasi halaman -->
            <nav v-if="totalPages > 1" aria-label="Navigasi halaman">
                <ul class="app-pagination mb-0">

                    <!-- First -->
                    <li :class="['app-page-item', { disabled: currentPage === 1 }]">
                        <button
                            class="app-page-btn"
                            :disabled="currentPage === 1"
                            title="Halaman pertama"
                            @click="currentPage = 1"
                        >
                            <i class="ti ti-chevrons-left"></i>
                        </button>
                    </li>

                    <!-- Prev -->
                    <li :class="['app-page-item', { disabled: currentPage === 1 }]">
                        <button
                            class="app-page-btn"
                            :disabled="currentPage === 1"
                            title="Sebelumnya"
                            @click="currentPage--"
                        >
                            <i class="ti ti-chevron-left"></i>
                        </button>
                    </li>

                    <!-- Page numbers -->
                    <li
                        v-for="(p, i) in displayPages"
                        :key="i"
                        :class="['app-page-item', { active: p === currentPage }]"
                    >
                        <button v-if="p !== null" class="app-page-btn" @click="currentPage = p">
                            {{ p }}
                        </button>
                        <span v-else class="app-page-ellipsis">···</span>
                    </li>

                    <!-- Next -->
                    <li :class="['app-page-item', { disabled: currentPage === totalPages }]">
                        <button
                            class="app-page-btn"
                            :disabled="currentPage === totalPages"
                            title="Berikutnya"
                            @click="currentPage++"
                        >
                            <i class="ti ti-chevron-right"></i>
                        </button>
                    </li>

                    <!-- Last -->
                    <li :class="['app-page-item', { disabled: currentPage === totalPages }]">
                        <button
                            class="app-page-btn"
                            :disabled="currentPage === totalPages"
                            title="Halaman terakhir"
                            @click="currentPage = totalPages"
                        >
                            <i class="ti ti-chevrons-right"></i>
                        </button>
                    </li>

                </ul>
            </nav>
        </div>

        <!-- Info bar -->
        <div
            v-if="data.length > 0"
            class="app-info-bar"
            :class="hasSensitive ? 'justify-content-between' : 'justify-content-center'"
        >
            <!-- <span class="app-info-text">
                Menampilkan
                <strong>{{ filteredData.length }}</strong>
                dari
                <strong>{{ data.length }}</strong>
                data
                <span v-if="filteredData.length !== data.length" class="app-info-filter">
                    (filter aktif)
                </span>
            </span> -->
            <span v-if="hasSensitive" class="app-info-text">
                Klik ikon <i class="ti ti-eye" style="font-size:0.85rem;vertical-align:-1px"></i>
                untuk mengungkap data sensitif
            </span>
        </div>

        <!-- Footer slot -->
        <slot name="footer" />
    </div>
</template>

<style lang="scss" scoped>
// ── Header ────────────────────────────────────────────────────────────────────
.app-thead th {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
    color: var(--bs-secondary-color);
    background-color: var(--bs-tertiary-bg);
    border-bottom-width: 1px;
    padding-block: 0.85rem;
}

.app-th-no      { width: 52px; }
.app-th-actions { width: 120px; }
.app-th-select  { width: 44px; text-align: center; vertical-align: middle !important; }

// ── Rows ──────────────────────────────────────────────────────────────────────
.table > tbody > tr > td {
    vertical-align: middle;
    color: var(--bs-body-color);
    font-size: 0.875rem;
}

// ── Avatar / Image ────────────────────────────────────────────────────────────
.app-table-img {
    object-fit: cover;
    display: block;
    flex-shrink: 0;
}

.app-avatar-fallback {
    flex-shrink: 0;
    line-height: 1;
    user-select: none;
    letter-spacing: 0.5px;
    background-color: var(--bs-primary-bg-subtle);
    color: var(--bs-primary);
}

// ── Sensitive ─────────────────────────────────────────────────────────────────
.app-censored {
    letter-spacing: 0.15em;
    opacity: 0.5;
}

.app-reveal-btn {
    line-height: 1;
    text-decoration: none !important;
    opacity: 0.5;
    transition: opacity 0.15s ease, color 0.15s ease;

    &:hover {
        opacity: 1;
        color: var(--bs-primary) !important;
    }
}

// ── Action buttons ────────────────────────────────────────────────────────────
.app-action-btn {
    width: 32px;
    height: 32px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px !important;
    transition: transform 0.1s ease, background-color 0.15s ease;
    line-height: 1;

    &:active { transform: scale(0.88); }
}

// ── Column toggle dropdown ────────────────────────────────────────────────────
.app-col-dropdown {
    min-width: 200px;
    max-width: 240px;
    border-radius: 10px !important;
    overflow: hidden;
}

.app-col-header {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--bs-secondary-color);
}

.app-col-count {
    font-size: 0.65rem;
    line-height: 1.4;
}

.app-col-list {
    max-height: 260px;
    overflow-y: auto;
}

.app-col-toggle-item {
    cursor: pointer;
    user-select: none;
    font-size: 0.8125rem;
    transition: background-color 0.12s ease;
    border: none;
    width: 100%;

    &:hover { background-color: var(--bs-tertiary-bg); }

    &.app-col-active {
        color: var(--bs-primary);
        font-weight: 500;
    }
}

.app-col-check {
    font-size: 0.85rem;
    flex-shrink: 0;
}

// ── Empty state ───────────────────────────────────────────────────────────────
.app-empty-icon {
    font-size: 3rem;
    opacity: 0.3;
}

// ── Search ────────────────────────────────────────────────────────────────────
.app-search-wrap {
    position: relative;
    min-width: 180px;
    max-width: 280px;
    flex: 1 1 auto;
}

.app-search-icon {
    position: absolute;
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    line-height: 1;
}

.app-search-input {
    padding-left: 2rem;

    &::-webkit-search-cancel-button { cursor: pointer; }
}

// ── Per-page select ───────────────────────────────────────────────────────────
.app-perpage-select {
    width: auto;
    min-width: 88px;
}

// ── Info bar ──────────────────────────────────────────────────────────────────
.app-info-bar {
    display: flex;
    align-items: center;
    margin-top: 0.75rem;
    padding-top: 0.625rem;
    padding-bottom: 0.125rem;
    border-top: 1px solid var(--bs-border-color);
}

.app-info-text {
    font-size: 0.775rem;
    color: var(--bs-secondary-color);
    line-height: 1.4;
}

.app-info-filter {
    color: var(--bs-warning-text-emphasis);
    font-style: italic;
}

// ── Pagination ────────────────────────────────────────────────────────────────
.app-pagination-bar {
    padding-top: 0;
    margin-top: 1rem;
}

.app-pagination-info {
    font-size: 0.8rem;
    color: var(--bs-secondary-color);
    line-height: 1.4;
}

.app-pagination {
    display: flex;
    align-items: center;
    gap: 3px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.app-page-item {
    &.disabled .app-page-btn {
        opacity: 0.35;
        cursor: not-allowed;
        pointer-events: none;
    }

    &.active .app-page-btn {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.35);
    }
}

.app-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 7px;
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--bs-body-color);
    background-color: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 8px;
    cursor: pointer;
    line-height: 1;
    transition: background-color 0.15s ease, color 0.15s ease,
                border-color 0.15s ease, box-shadow 0.15s ease;

    i {
        font-size: 1rem;
        line-height: 1;
    }

    &:hover:not(:disabled) {
        background-color: var(--bs-primary-bg-subtle);
        border-color: var(--bs-primary-border-subtle);
        color: var(--bs-primary);
    }

    &:focus-visible {
        outline: 2px solid var(--bs-primary);
        outline-offset: 2px;
        z-index: 1;
    }

    &:active:not(:disabled) {
        transform: scale(0.92);
    }
}

.app-page-ellipsis {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 34px;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--bs-secondary-color);
    letter-spacing: 0.08em;
    user-select: none;
}
</style>
