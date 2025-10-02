<template>
    <div class="p-2">
        <Select :model-value="modelValue" @update:model-value="handleChange">
            <SelectTrigger class="h-8 text-sm">
                <SelectValue :placeholder="placeholder" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem v-for="option in options" :key="option.value" :value="option.value">
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>

<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Option {
    value: string;
    label: string;
}

interface Props {
    modelValue: string;
    options: Option[];
    placeholder?: string;
}

withDefaults(defineProps<Props>(), {
    placeholder: 'All',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    'change': [value: string];
}>();

function handleChange(value: string) {
    emit('update:modelValue', value);
    emit('change', value);
}
</script>
