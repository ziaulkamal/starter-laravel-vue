<template>
    <div v-bind="wrapperAttrs">
        <label v-if="label" :for="uid" class="form-label">
            {{ label }}
            <span v-if="required" class="text-danger ms-1">*</span>
        </label>

        <div v-if="hasPrepend || hasAppend" :class="['input-group', groupSizeClass]">
            <slot name="prepend">
                <span v-if="prependIcon" class="input-group-text">
                    <i :class="prependIcon"></i>
                </span>
                <span v-else-if="prependText" class="input-group-text">{{ prependText }}</span>
            </slot>
            <input
                :id="uid"
                v-bind="inputAttrs"
                :value="modelValue ?? ''"
                :type="type"
                :disabled="disabled"
                :readonly="readonly"
                :placeholder="placeholder"
                :class="['form-control', inputSizeClass, { 'is-invalid': !!error }]"
                @input="onInput"
            />
            <slot name="append">
                <span v-if="appendIcon" class="input-group-text">
                    <i :class="appendIcon"></i>
                </span>
                <span v-else-if="appendText" class="input-group-text">{{ appendText }}</span>
            </slot>
            <div v-if="error" class="invalid-feedback">{{ error }}</div>
        </div>

        <input
            v-else
            :id="uid"
            v-bind="inputAttrs"
            :value="modelValue ?? ''"
            :type="type"
            :disabled="disabled"
            :readonly="readonly"
            :placeholder="placeholder"
            :class="['form-control', inputSizeClass, { 'is-invalid': !!error }]"
            @input="onInput"
        />

        <div v-if="hint && !error" class="form-text">{{ hint }}</div>
        <div v-if="error && !hasPrepend && !hasAppend" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup lang="ts">
import { computed, useAttrs, useId, useSlots } from 'vue';

defineOptions({ inheritAttrs: false });

interface Props {
    modelValue?: string | number | null;
    type?: string;
    label?: string;
    placeholder?: string;
    hint?: string;
    error?: string;
    id?: string;
    disabled?: boolean;
    readonly?: boolean;
    required?: boolean;
    size?: 'sm' | 'md' | 'lg';
    prependText?: string;
    prependIcon?: string;
    appendText?: string;
    appendIcon?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    disabled: false,
    readonly: false,
    required: false,
    size: 'md',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const slots = useSlots();
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

const inputSizeClass = computed(() => ({
    'form-control-sm': props.size === 'sm',
    'form-control-lg': props.size === 'lg',
}));

const groupSizeClass = computed(() => ({
    'input-group-sm': props.size === 'sm',
    'input-group-lg': props.size === 'lg',
}));

const hasPrepend = computed(() => !!slots.prepend || !!props.prependText || !!props.prependIcon);
const hasAppend = computed(() => !!slots.append || !!props.appendText || !!props.appendIcon);

function onInput(event: Event) {
    emit('update:modelValue', (event.target as HTMLInputElement).value);
}
</script>
