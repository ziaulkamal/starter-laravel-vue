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
                    :name="name ?? groupId"
                    type="radio"
                    :value="opt.value"
                    :checked="modelValue === opt.value"
                    :disabled="disabled || opt.disabled"
                    :class="['form-check-input', { 'is-invalid': !!error }]"
                    @change="emit('update:modelValue', opt.value)"
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

type RadioOption = { label: string; value: string | number; disabled?: boolean };

interface Props {
    modelValue?: string | number | null;
    options?: Array<string | RadioOption>;
    label?: string;
    hint?: string;
    error?: string;
    name?: string;
    disabled?: boolean;
    required?: boolean;
    inline?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    options: () => [],
    disabled: false,
    required: false,
    inline: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const rawAttrs = useAttrs();
const groupId = useId();

const wrapperAttrs = computed(() => {
    const attrs: Record<string, unknown> = {};
    if (rawAttrs.class !== undefined) attrs.class = rawAttrs.class;
    if (rawAttrs.style !== undefined) attrs.style = rawAttrs.style;
    return attrs;
});

const normalizedOptions = computed<RadioOption[]>(() =>
    props.options.map(opt =>
        typeof opt === 'string' ? { label: opt, value: opt } : opt
    )
);
</script>
