<template>
    <div v-bind="wrapperAttrs">
        <label v-if="label" :for="uid" class="form-label">
            {{ label }}
            <span v-if="required" class="text-danger ms-1">*</span>
        </label>

        <textarea
            :id="uid"
            v-bind="inputAttrs"
            :value="modelValue ?? ''"
            :disabled="disabled"
            :readonly="readonly"
            :placeholder="placeholder"
            :rows="rows"
            :class="['form-control', { 'is-invalid': !!error, 'resize-none': !resize }]"
            @input="onInput"
        ></textarea>

        <div v-if="hint && !error" class="form-text">{{ hint }}</div>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup lang="ts">
import { computed, useAttrs, useId } from 'vue';

defineOptions({ inheritAttrs: false });

interface Props {
    modelValue?: string | null;
    label?: string;
    placeholder?: string;
    hint?: string;
    error?: string;
    id?: string;
    disabled?: boolean;
    readonly?: boolean;
    required?: boolean;
    rows?: number;
    resize?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    readonly: false,
    required: false,
    rows: 3,
    resize: true,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
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

function onInput(event: Event) {
    emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
}
</script>

<style scoped>
.resize-none {
    resize: none;
}
</style>
