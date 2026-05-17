<template>
    <div>
        <div class="d-flex align-items-center justify-content-between mb-1">
            <div>
                <label class="form-label mb-0 fw-semibold">{{ label }}</label>
                <div v-if="description" class="text-muted" style="font-size: 0.75rem">{{ description }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span :class="['badge rounded-pill', modelValue.length ? 'text-bg-primary' : 'text-bg-secondary']">
                    {{ modelValue.length }}
                </span>
                <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none" style="font-size:0.75rem" @click="selectAll">Semua</button>
                <span class="text-muted" style="font-size:0.7rem">·</span>
                <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none" style="font-size:0.75rem" @click="deselectAll">Hapus</button>
            </div>
        </div>

        <!-- Search -->
        <div class="position-relative mb-1">
            <i class="ti ti-search position-absolute text-muted"
               style="left:8px;top:50%;transform:translateY(-50%);font-size:0.8rem;pointer-events:none"></i>
            <input
                v-model="search"
                type="search"
                class="form-control form-control-sm"
                style="padding-left:28px"
                placeholder="Cari..."
            />
        </div>

        <!-- Checkbox list -->
        <div class="border rounded check-list-wrap">
            <div v-if="filtered.length === 0" class="text-center text-muted small py-3 px-2">
                {{ emptyMessage || 'Tidak ada data' }}
            </div>
            <label
                v-for="opt in filtered"
                :key="opt.value"
                class="check-list-item d-flex align-items-center gap-2 px-3"
                style="min-height:34px;cursor:pointer"
            >
                <input
                    type="checkbox"
                    class="form-check-input m-0 flex-shrink-0"
                    :value="opt.value"
                    :checked="modelValue.includes(opt.value)"
                    @change="toggle(opt.value)"
                />
                <span class="small text-truncate">{{ opt.label }}</span>
            </label>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface Opt {
    value: number;
    label: string;
}

const props = withDefaults(defineProps<{
    label:         string;
    options:       Opt[];
    modelValue:    number[];
    description?:  string;
    emptyMessage?: string;
}>(), {});

const emit = defineEmits<{ 'update:modelValue': [v: number[]] }>();

const search = ref('');

const filtered = computed(() =>
    search.value
        ? props.options.filter(o => o.label.toLowerCase().includes(search.value.toLowerCase()))
        : props.options
);

function toggle(val: number): void {
    if (props.modelValue.includes(val)) {
        emit('update:modelValue', props.modelValue.filter(v => v !== val));
    } else {
        emit('update:modelValue', [...props.modelValue, val]);
    }
}

function selectAll(): void {
    emit('update:modelValue', props.options.map(o => o.value));
}

function deselectAll(): void {
    emit('update:modelValue', []);
}
</script>

<style scoped>
.check-list-wrap {
    max-height: 180px;
    overflow-y: auto;
    background: var(--bs-body-bg);
}
.check-list-item:hover {
    background: var(--bs-tertiary-bg);
}
</style>
