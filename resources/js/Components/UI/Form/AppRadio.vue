<template>
    <div class="form-check" v-bind="wrapperAttrs">
        <input
            :id="uid"
            v-bind="inputAttrs"
            type="radio"
            :name="name"
            :value="value"
            :checked="modelValue === value"
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
    modelValue?: string | number | null;
    value: string | number;
    label?: string;
    hint?: string;
    error?: string;
    id?: string;
    name?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
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

function onChange() {
    emit('update:modelValue', props.value);
}
</script>
