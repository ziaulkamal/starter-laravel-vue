<template>
    <div v-bind="wrapperAttrs">
        <label v-if="label" class="form-label d-block">
            {{ label }}
            <span v-if="required" class="text-danger ms-1">*</span>
        </label>

        <div :class="inline ? 'd-flex flex-wrap gap-3' : 'd-flex flex-column gap-1'">
            <div
                v-for="opt in normalizedOptions"
                :key="opt.value"
                class="form-check"
            >
                <input
                    :id="`${groupId}-${opt.value}`"
                    type="checkbox"
                    :value="opt.value"
                    :checked="modelValue.includes(opt.value)"
                    :disabled="disabled || opt.disabled"
                    :class="['form-check-input', { 'is-invalid': !!error }]"
                    @change="onToggle(opt.value, ($event.target as HTMLInputElement).checked)"
                />
                <label :for="`${groupId}-${opt.value}`" class="form-check-label">
                    {{ opt.label }}
                </label>
            </div>
        </div>

        <div v-if="hint && !error" class="form-text">{{ hint }}</div>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup lang="ts">
import { computed, useAttrs, useId } from 'vue';

defineOptions({ inheritAttrs: false });

type CheckboxOption = { label: string; value: string | number; disabled?: boolean };

interface Props {
    modelValue?: (string | number)[];
    options?: Array<string | CheckboxOption>;
    label?: string;
    hint?: string;
    error?: string;
    disabled?: boolean;
    required?: boolean;
    inline?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    options: () => [],
    disabled: false,
    required: false,
    inline: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: (string | number)[]];
}>();

const rawAttrs = useAttrs();
const groupId = useId();

const wrapperAttrs = computed(() => {
    const attrs: Record<string, unknown> = {};
    if (rawAttrs.class !== undefined) attrs.class = rawAttrs.class;
    if (rawAttrs.style !== undefined) attrs.style = rawAttrs.style;
    return attrs;
});

const normalizedOptions = computed<CheckboxOption[]>(() =>
    props.options.map(opt =>
        typeof opt === 'string' ? { label: opt, value: opt } : opt
    )
);

function onToggle(value: string | number, checked: boolean) {
    const next = [...props.modelValue];
    if (checked) {
        next.push(value);
    } else {
        const idx = next.indexOf(value);
        if (idx !== -1) next.splice(idx, 1);
    }
    emit('update:modelValue', next);
}
</script>
