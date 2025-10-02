<template>
    <div class="p-2">
        <Input
            :model-value="modelValue"
            :type="type"
            :placeholder="placeholder"
            class="h-8 text-sm"
            @update:model-value="handleInput"
        />
    </div>
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input';

interface Props {
    modelValue: string;
    type?: string;
    placeholder?: string;
    debounce?: boolean;
}

withDefaults(defineProps<Props>(), {
    type: 'text',
    placeholder: 'Filter...',
    debounce: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    'change': [value: string];
}>();

function handleInput(value: string | number) {
    const stringValue = String(value);
    emit('update:modelValue', stringValue);
    emit('change', stringValue);
}
</script>
