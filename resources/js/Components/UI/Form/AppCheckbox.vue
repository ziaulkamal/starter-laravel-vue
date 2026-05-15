<template>
    <div class="form-check" v-bind="wrapperAttrs">
        <input
            :id="uid"
            v-bind="inputAttrs"
            type="checkbox"
            :checked="isChecked"
            :value="value"
            :disabled="disabled"
            :class="['form-check-input', { 'is-invalid': !!error }]"
            @change="onChange"
        />
        <label v-if="label" :for="uid" class="form-check-label">
            {{ label }}
        </label>
        <div v-if="hint && !error" class="form-text">{{ hint }}</div>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup lang="ts">
import { computed, useAttrs, useId } from 'vue';

defineOptions({ inheritAttrs: false });

interface Props {
    modelValue?: boolean | (string | number)[];
    value?: string | number;
    label?: string;
    hint?: string;
    error?: string;
    id?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean | (string | number)[]];
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

const isChecked = computed(() => {
    if (Array.isArray(props.modelValue)) {
        return props.value !== undefined && props.modelValue.includes(props.value);
    }
    return !!props.modelValue;
});

function onChange(event: Event) {
    const checked = (event.target as HTMLInputElement).checked;
    if (Array.isArray(props.modelValue)) {
        const next = [...props.modelValue];
        if (checked && props.value !== undefined) {
            next.push(props.value);
        } else if (props.value !== undefined) {
            const idx = next.indexOf(props.value);
            if (idx !== -1) next.splice(idx, 1);
        }
        emit('update:modelValue', next);
    } else {
        emit('update:modelValue', checked);
    }
}
</script>
