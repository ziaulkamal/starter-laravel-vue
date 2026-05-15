<template>
    <div v-bind="wrapperAttrs">
        <label v-if="label" :for="uid" class="form-label">
            {{ label }}
            <span v-if="required" class="text-danger ms-1">*</span>
        </label>

        <select
            :id="uid"
            v-bind="inputAttrs"
            :disabled="disabled"
            :class="['form-select', sizeClass, { 'is-invalid': !!error }]"
            @change="onChange"
        >
            <option v-if="placeholder" value="" :selected="modelValue === null || modelValue === undefined || modelValue === ''">
                {{ placeholder }}
            </option>
            <slot>
                <option
                    v-for="opt in normalizedOptions"
                    :key="opt.value"
                    :value="opt.value"
                    :disabled="opt.disabled"
                    :selected="String(opt.value) === String(modelValue)"
                >
                    {{ opt.label }}
                </option>
            </slot>
        </select>

        <div v-if="hint && !error" class="form-text">{{ hint }}</div>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup lang="ts">
import { computed, useAttrs, useId } from 'vue';

defineOptions({ inheritAttrs: false });

type SelectOption = { label: string; value: string | number; disabled?: boolean };

interface Props {
    modelValue?: string | number | null;
    options?: Array<string | SelectOption>;
    label?: string;
    placeholder?: string;
    hint?: string;
    error?: string;
    id?: string;
    disabled?: boolean;
    required?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    options: () => [],
    disabled: false,
    required: false,
    size: 'md',
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const rawAttrs = useAttrs();
const generatedId = useId();

const uid = computed(() => props.id ?? generatedId);

const wrapperAttrs = computed(() => {
    const attrs: Record<string, unknown> = {};
    if (rawAttrs.class !== undefined) attrs.class = rawAttrs.class;
    if (rawAttrs.style !== undefined) attrs.style = rawAttrs.style;
    return attrs;
});

const inputAttrs = computed(() => {
    const { class: _c, style: _s, ...rest } = rawAttrs;
    return rest;
});

const sizeClass = computed(() => ({
    'form-select-sm': props.size === 'sm',
    'form-select-lg': props.size === 'lg',
}));

const normalizedOptions = computed<SelectOption[]>(() =>
    props.options.map(opt =>
        typeof opt === 'string' ? { label: opt, value: opt } : opt
    )
);

function onChange(event: Event) {
    const strVal = (event.target as HTMLSelectElement).value;
    const matched = normalizedOptions.value.find(o => String(o.value) === strVal);
    emit('update:modelValue', matched ? matched.value : strVal);
}
</script>
