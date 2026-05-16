<template>
    <div ref="wrapperRef" class="app-s2-wrap">
        <label v-if="label" :for="uid" class="form-label">
            {{ label }}
            <span v-if="required" class="text-danger ms-1">*</span>
        </label>

        <!-- Trigger -->
        <button
            :id="uid"
            type="button"
            :disabled="disabled"
            :class="['app-s2-control form-control text-start', { 'is-invalid': !!error, 'app-s2-open': isOpen }]"
            @click="toggle"
            @keydown.space.prevent="toggle"
            @keydown.enter.prevent="toggle"
            @keydown.down.prevent="openAndFocus"
            @keydown.esc="close"
        >
            <span v-if="selectedLabel" class="app-s2-selected">{{ selectedLabel }}</span>
            <span v-else class="app-s2-placeholder">{{ placeholder ?? 'Pilih...' }}</span>
            <i :class="['ti ti-chevron-down app-s2-chevron', { 'app-s2-chevron-up': isOpen }]"></i>
        </button>

        <!-- Dropdown -->
        <div v-if="isOpen" class="app-s2-dropdown shadow-sm border rounded-3">
            <!-- Search -->
            <div class="app-s2-search-wrap px-2 pt-2 pb-1">
                <div class="position-relative">
                    <i class="ti ti-search app-s2-search-icon text-muted"></i>
                    <input
                        ref="searchRef"
                        v-model="query"
                        type="search"
                        class="form-control form-control-sm app-s2-search-input"
                        :placeholder="searchPlaceholder ?? 'Cari...'"
                        autocomplete="off"
                        @keydown.esc="close"
                        @keydown.down.prevent="moveHighlight(1)"
                        @keydown.up.prevent="moveHighlight(-1)"
                        @keydown.enter.prevent="confirmHighlighted"
                    />
                </div>
            </div>

            <!-- Options list -->
            <ul ref="listRef" class="app-s2-list">
                <li v-if="filtered.length === 0" class="app-s2-empty text-muted small px-3 py-2">
                    Tidak ada hasil untuk "<em>{{ query }}</em>"
                </li>
                <li
                    v-for="(opt, idx) in filtered"
                    :key="opt.value"
                    :ref="el => setItemRef(el, idx)"
                    :class="[
                        'app-s2-option',
                        { 'app-s2-option-active': opt.value === modelValue },
                        { 'app-s2-option-highlighted': idx === highlighted },
                    ]"
                    @click="select(opt)"
                    @mouseenter="highlighted = idx"
                >
                    <i v-if="opt.value === modelValue" class="ti ti-check app-s2-check"></i>
                    <span>{{ opt.label }}</span>
                </li>
            </ul>
        </div>

        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
        <div v-if="hint && !error" class="form-text">{{ hint }}</div>
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

// ── Types ─────────────────────────────────────────────────────────────────────

export interface Select2Option {
    label: string;
    value: string | number;
}

interface Props {
    modelValue?: string | number | null;
    options?: Array<string | Select2Option>;
    label?: string;
    placeholder?: string;
    searchPlaceholder?: string;
    hint?: string;
    error?: string;
    id?: string;
    disabled?: boolean;
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    options: () => [],
    disabled: false,
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
    'change': [option: Select2Option | null];
}>();

// ── IDs & refs ────────────────────────────────────────────────────────────────

const uid         = computed(() => props.id ?? `s2-${Math.random().toString(36).slice(2, 8)}`);
const wrapperRef  = ref<HTMLElement | null>(null);
const searchRef   = ref<HTMLInputElement | null>(null);
const listRef     = ref<HTMLElement | null>(null);
const itemRefs    = ref<(Element | null)[]>([]);

function setItemRef(el: Element | ComponentPublicInstance | null, idx: number) {
    itemRefs.value[idx] = el instanceof Element ? el : null;
}

// ── Normalized options ────────────────────────────────────────────────────────

const normalized = computed<Select2Option[]>(() =>
    props.options.map(o =>
        typeof o === 'string' ? { label: o, value: o } : o
    )
);

// ── Dropdown state ────────────────────────────────────────────────────────────

const isOpen      = ref(false);
const query       = ref('');
const highlighted = ref(-1);

const filtered = computed<Select2Option[]>(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return normalized.value;
    return normalized.value.filter(o =>
        o.label.toLowerCase().includes(q) || String(o.value).toLowerCase().includes(q)
    );
});

const selectedLabel = computed<string | null>(() => {
    if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
        return null;
    }
    return normalized.value.find(o => o.value === props.modelValue)?.label ?? null;
});

// ── Open / Close ──────────────────────────────────────────────────────────────

async function open(): Promise<void> {
    if (props.disabled) return;
    isOpen.value  = true;
    query.value   = '';
    highlighted.value = normalized.value.findIndex(o => o.value === props.modelValue);
    await nextTick();
    searchRef.value?.focus();
    scrollToHighlighted();
}

function close(): void {
    isOpen.value      = false;
    query.value       = '';
    highlighted.value = -1;
}

function toggle(): void {
    isOpen.value ? close() : open();
}

async function openAndFocus(): Promise<void> {
    if (!isOpen.value) await open();
}

// ── Selection ─────────────────────────────────────────────────────────────────

function select(opt: Select2Option): void {
    emit('update:modelValue', opt.value);
    emit('change', opt);
    close();
}

function confirmHighlighted(): void {
    if (highlighted.value >= 0 && filtered.value[highlighted.value]) {
        select(filtered.value[highlighted.value]);
    }
}

// ── Keyboard navigation ───────────────────────────────────────────────────────

function moveHighlight(dir: 1 | -1): void {
    const max = filtered.value.length - 1;
    if (max < 0) return;
    highlighted.value = highlighted.value < 0
        ? (dir === 1 ? 0 : max)
        : Math.max(0, Math.min(max, highlighted.value + dir));
    scrollToHighlighted();
}

function scrollToHighlighted(): void {
    nextTick(() => {
        const el = itemRefs.value[highlighted.value];
        if (el instanceof HTMLElement) {
            el.scrollIntoView({ block: 'nearest' });
        }
    });
}

// ── Click outside ─────────────────────────────────────────────────────────────

function onDocClick(e: MouseEvent): void {
    if (isOpen.value && wrapperRef.value && !wrapperRef.value.contains(e.target as Node)) {
        close();
    }
}

onMounted(() => document.addEventListener('mousedown', onDocClick));
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocClick));

// Reset highlighted when filtered list changes
watch(filtered, () => { highlighted.value = -1; });
</script>

<style lang="scss" scoped>
// ── Trigger ───────────────────────────────────────────────────────────────────
.app-s2-wrap {
    position: relative;
}

.app-s2-control {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    cursor: pointer;
    background-color: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    padding: 0.375rem 0.625rem;
    border-radius: var(--bs-border-radius);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    user-select: none;

    &:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.2);
        outline: none;
    }

    &.app-s2-open {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.15);
    }

    &:disabled {
        background-color: var(--bs-secondary-bg);
        opacity: 0.65;
        cursor: not-allowed;
    }
}

.app-s2-selected  { font-size: 0.875rem; color: var(--bs-body-color); flex: 1; text-align: left; }
.app-s2-placeholder { font-size: 0.875rem; color: var(--bs-secondary-color); flex: 1; text-align: left; }

.app-s2-chevron {
    font-size: 1rem;
    color: var(--bs-secondary-color);
    flex-shrink: 0;
    transition: transform 0.2s ease;

    &.app-s2-chevron-up { transform: rotate(-180deg); }
}

// ── Dropdown ──────────────────────────────────────────────────────────────────
.app-s2-dropdown {
    position: absolute;
    z-index: 1060;
    left: 0;
    right: 0;
    top: calc(100% + 4px);
    background-color: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color) !important;
    border-radius: 10px !important;
    overflow: hidden;
    animation: s2FadeIn 0.12s ease;
}

@keyframes s2FadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}

// ── Search ────────────────────────────────────────────────────────────────────
.app-s2-search-wrap { border-bottom: 1px solid var(--bs-border-color); }

.app-s2-search-icon {
    position: absolute;
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    font-size: 0.875rem;
}

.app-s2-search-input {
    padding-left: 1.75rem;

    &::-webkit-search-cancel-button { cursor: pointer; }
}

// ── List ──────────────────────────────────────────────────────────────────────
.app-s2-list {
    list-style: none;
    margin: 0;
    padding: 0.25rem 0;
    max-height: 220px;
    overflow-y: auto;
    overscroll-behavior: contain;
}

.app-s2-empty {
    font-style: italic;
    padding: 0.5rem 0.75rem;
}

.app-s2-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 0.75rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.1s ease;

    &:hover,
    &.app-s2-option-highlighted {
        background-color: var(--bs-primary-bg-subtle);
        color: var(--bs-primary);
    }

    &.app-s2-option-active {
        color: var(--bs-primary);
        font-weight: 500;
    }
}

.app-s2-check {
    font-size: 0.8rem;
    flex-shrink: 0;
}
</style>
