<script lang="ts" setup>
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { CalendarIcon, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { CalendarRoot, useDateFormatter } from 'reka-ui';
import { createDecade, createYear, toDate } from 'reka-ui/date';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
} from '@/components/ui/calendar';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

const df = new DateFormatter('en-GB', {
    dateStyle: 'long',
    timeZone: 'Europe/Athens'
});

const formatter = useDateFormatter('en');

interface Props {
    modelValue?: string | null;
    placeholder?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Pick a date',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
    (e: 'change', value: string | null): void;
}>();

const isOpen = ref(false);

// Internal placeholder for calendar navigation
const calendarPlaceholder = ref<DateValue>(today(getLocalTimeZone()));

// Computed property to handle the two-way binding
const value = computed({
    get: (): DateValue | undefined => {
        if (!props.modelValue) return undefined;
        try {
            const parsed = parseDate(props.modelValue);
            calendarPlaceholder.value = parsed;
            return parsed;
        } catch (e) {
            return undefined;
        }
    },
    set: (newValue: DateValue | undefined) => {
        if (newValue) {
            const isoString = newValue.toString();
            emit('update:modelValue', isoString);
            emit('change', isoString);
        } else {
            emit('update:modelValue', null);
            emit('change', null);
        }
    },
});

const hasValue = computed(() => !!props.modelValue);

// Display text for the button
const displayText = computed(() => {
    if (props.modelValue) {
        try {
            return df.format(parseDate(props.modelValue).toDate(getLocalTimeZone()));
        } catch (e) {
            return props.placeholder;
        }
    }
    return props.placeholder;
});

function handleDateSelect(newValue: DateValue | undefined) {
    if (newValue) {
        // Use the toString() method which gives YYYY-MM-DD format
        const isoString = newValue.toString();
        emit('update:modelValue', isoString);
        emit('change', isoString);
        isOpen.value = false;
    }
}

const clearSelection = () => {
    emit('update:modelValue', null);
    emit('change', null);
    calendarPlaceholder.value = today(getLocalTimeZone());
};
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <div class="relative w-full">
                <Button
                    type="button"
                    :class="
                    cn(
                        'w-full justify-start text-left font-normal',
                        !hasValue && 'text-muted-foreground',
                    )
                "
                    variant="outline"
                    :disabled="disabled"
                >
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    <span class="flex-1 truncate">{{ displayText }}</span>
                </Button>
                <button
                    v-if="hasValue && !disabled"
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-sm opacity-50 hover:opacity-100 focus:opacity-100 disabled:pointer-events-none"
                    @click.stop="clearSelection"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <CalendarRoot
                v-slot="{ date, grid, weekDays }"
                v-model="value"
                v-model:placeholder="calendarPlaceholder"
                :disabled="disabled"
                :class="
                    cn(
                        'rounded-md border p-3',
                        '[&_[data-selected]]:bg-primary',
                        '[&_[data-selected]]:text-primary-foreground',
                        '[&_[data-selected]:hover]:bg-primary',
                        '[&_[data-selected]:hover]:text-primary-foreground',
                        '[&_[data-selected]:focus]:bg-primary',
                        '[&_[data-selected]:focus]:text-primary-foreground',
                        '[&_[role=gridcell]:not([aria-disabled=true])]:cursor-pointer',
                        '[&_[role=gridcell]:not([aria-disabled=true])_*]:cursor-pointer',
                    )
                "
                @update:model-value="handleDateSelect"
            >
                <CalendarHeader>
                    <CalendarHeading class="flex w-full items-center justify-between gap-2">
                        <!-- Month Select -->
                        <Select
                            :default-value="calendarPlaceholder.month.toString()"
                            @update:model-value="
                                (v) => {
                                    if (!v || !calendarPlaceholder) return;
                                    if (Number(v) === calendarPlaceholder?.month) return;
                                    calendarPlaceholder = calendarPlaceholder.set({
                                        month: Number(v),
                                    });
                                }
                            "
                        >
                            <SelectTrigger aria-label="Select month" class="w-[60%]">
                                <SelectValue placeholder="Select month" />
                            </SelectTrigger>
                            <SelectContent class="max-h-[200px]">
                                <SelectItem v-for="month in createYear({ dateObj: date })" :key="month.toString()" :value="month.month.toString()">
                                    {{ formatter.custom(toDate(month), { month: 'long' }) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Year Select -->
                        <Select
                            :default-value="calendarPlaceholder.year.toString()"
                            @update:model-value="
                                (v) => {
                                    if (!v || !calendarPlaceholder) return;
                                    if (Number(v) === calendarPlaceholder?.year) return;
                                    calendarPlaceholder = calendarPlaceholder.set({
                                        year: Number(v),
                                    });
                                }
                            "
                        >
                            <SelectTrigger aria-label="Select year" class="w-[40%]">
                                <SelectValue placeholder="Select year" />
                            </SelectTrigger>
                            <SelectContent class="max-h-[200px]">
                                <SelectItem
                                    v-for="yearValue in createDecade({ dateObj: date, startIndex: -10, endIndex: 10 })"
                                    :key="yearValue.toString()"
                                    :value="yearValue.year.toString()"
                                >
                                    {{ yearValue.year }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </CalendarHeading>
                </CalendarHeader>

                <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
                    <CalendarGrid v-for="month in grid" :key="month.value.toString()">
                        <CalendarGridHead>
                            <CalendarGridRow>
                                <CalendarHeadCell v-for="day in weekDays" :key="day">
                                    {{ day }}
                                </CalendarHeadCell>
                            </CalendarGridRow>
                        </CalendarGridHead>
                        <CalendarGridBody class="grid">
                            <CalendarGridRow v-for="(weekDates, index) in month.rows" :key="`weekDate-${index}`" class="mt-2 w-full">
                                <CalendarCell v-for="weekDate in weekDates" :key="weekDate.toString()" :date="weekDate">
                                    <CalendarCellTrigger :day="weekDate" :month="month.value" />
                                </CalendarCell>
                            </CalendarGridRow>
                        </CalendarGridBody>
                    </CalendarGrid>
                </div>
            </CalendarRoot>
        </PopoverContent>
    </Popover>
</template>

