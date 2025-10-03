<template>
    <div class="p-2">
        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button variant="outline" role="combobox" :aria-expanded="open" class="h-8 w-full justify-between text-sm">
                    <span v-if="selectedCount === 0" class="text-muted-foreground">{{ placeholder }}</span>
                    <span v-else>{{ selectedCount }} selected</span>
                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-[200px] p-0">
                <Command>
                    <CommandInput :placeholder="searchPlaceholder" />
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandList>
                        <CommandGroup>
                            <CommandItem
                                v-for="option in options"
                                :key="option.value"
                                :value="option.value"
                                @select="toggleOption(option.value)"
                            >
                                <Check
                                    :class="[
                                        'mr-2 h-4 w-4',
                                        selectedValues.includes(option.value) ? 'opacity-100' : 'opacity-0',
                                    ]"
                                />
                                {{ option.label }}
                            </CommandItem>
                        </CommandGroup>
                    </CommandList>
                </Command>
                <div v-if="selectedCount > 0" class="border-t p-2">
                    <Button variant="ghost" size="sm" class="h-7 w-full text-xs" @click="clearSelection">
                        Clear
                    </Button>
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>

<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Check, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Option {
    value: string;
    label: string;
}

interface Props {
    modelValue: string[]; // Array of selected values
    options: Option[];
    placeholder?: string;
    searchPlaceholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select...',
    searchPlaceholder: 'Search...',
});

const emit = defineEmits<{
    'update:modelValue': [value: string[]];
    change: [value: string[]];
}>();

const open = ref(false);

const selectedValues = computed(() => props.modelValue || []);
const selectedCount = computed(() => selectedValues.value.length);

function toggleOption(value: string) {
    const newValues = selectedValues.value.includes(value)
        ? selectedValues.value.filter((v) => v !== value)
        : [...selectedValues.value, value];

    emit('update:modelValue', newValues);
    emit('change', newValues);
}

function clearSelection() {
    emit('update:modelValue', []);
    emit('change', []);
    open.value = false;
}
</script>
