<script lang="ts" setup>
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone, parseDate } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

const df = new DateFormatter('en-GB', {
    dateStyle: 'long',
    timeZone: 'Europe/Athens'
});

interface Props {
    modelValue?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const isOpen = ref(false);

// Computed property to handle the two-way binding
const value = computed({
    get: (): DateValue | undefined => {
        if (!props.modelValue) return undefined;

            return parseDate(props.modelValue);
    },
    set: (newValue: DateValue | undefined) => {
        if (newValue) {
            const date = newValue.toDate(getLocalTimeZone());
            const isoString = date.toISOString().split('T')[0];
            emit('update:modelValue', isoString);
        } else {
            emit('update:modelValue', '');
        }
    }
});

function handleDateSelect(newValue: DateValue | undefined) {
    if (newValue) {
        const date = newValue.toDate(getLocalTimeZone());
        const isoString = date.toISOString().split('T')[0];
        emit('update:modelValue', isoString);
        isOpen.value = false;
    }
}
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button
                :class="cn(
                    'w-[280px] justify-start text-left font-normal',
                    !props.modelValue && 'text-muted-foreground',
                )"
                variant="outline"
            >
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ props.modelValue ? df.format(parseDate(props.modelValue).toDate(getLocalTimeZone())) : 'Pick a date' }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <Calendar
                v-model="value"
                class="
                    [&_[data-selected]]:bg-primary
                    [&_[data-selected]]:text-primary-foreground
                    [&_[data-selected]:hover]:bg-primary
                    [&_[data-selected]:hover]:text-primary-foreground
                    [&_[data-selected]:focus]:bg-primary
                    [&_[data-selected]:focus]:text-primary-foreground
                    [&_[role=gridcell]:not([aria-disabled=true])]:cursor-pointer
                    [&_[role=gridcell]:not([aria-disabled=true])_*]:cursor-pointer
                "
                initial-focus
                @update:model-value="handleDateSelect"
            />
        </PopoverContent>
    </Popover>
</template>

<style scoped>
:deep([role="gridcell"] button) {
    cursor: pointer;
}
</style>
