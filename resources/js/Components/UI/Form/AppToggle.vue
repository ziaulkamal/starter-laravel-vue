<template>
    <div class="form-check form-switch" v-bind="wrapperAttrs">
        <input
            :id="uid"
            v-bind="inputAttrs"
            type="checkbox"
            role="switch"
            :checked="modelValue"
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
    modelValue?: boolean;
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
    'update:modelValue': [value: boolean];
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

function onChange(event: Event) {
    emit('update:modelValue', (event.target as HTMLInputElement).checked);
}
</script>
