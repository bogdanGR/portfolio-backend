<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

interface Props {
    categories: string[],
    modelValue?: string;

}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>();
</script>

<template>
    <Select :modelValue="props.modelValue"  @update:modelValue="val => emit('update:modelValue', val)" required>
        <SelectTrigger class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500
                                focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                                dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400">
            <SelectValue placeholder="Select a category" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500
                                focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                                dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400">
                <SelectItem v-for="(category, index) in props.categories" :key="index" :value="category">
                    {{ category }}
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>

