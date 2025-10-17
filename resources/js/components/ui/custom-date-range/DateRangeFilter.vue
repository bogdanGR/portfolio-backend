<script setup lang="ts">
import type { DateRange, DateValue } from 'reka-ui';
import { computed, ref, watch } from 'vue';
import {  getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { RangeCalendarRoot, useDateFormatter } from 'reka-ui';
import { createDecade, createYear, toDate } from 'reka-ui/date';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    RangeCalendarCell,
    RangeCalendarCellTrigger,
    RangeCalendarGrid,
    RangeCalendarGridBody,
    RangeCalendarGridHead,
    RangeCalendarGridRow,
    RangeCalendarHeadCell,
    RangeCalendarHeader,
    RangeCalendarHeading
} from '@/components/ui/range-calendar';
import Button from '@/components/ui/button/Button.vue';
import { Calendar, X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

interface Props {
    modelValue?: {
        start?: string | null;
        end?: string | null;
    };
    placeholder?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select date range',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: { start: string | null; end: string | null }): void;
    (e: 'change', value: { start: string | null; end: string | null }): void;
}>();

const open = ref(false);
const formatter = useDateFormatter('en');

// Internal placeholder for calendar navigation
const calendarPlaceholder = ref<DateValue>(today(getLocalTimeZone()));

// Convert string dates to DateValue for the calendar
const calendarValue = ref<DateRange | undefined>(undefined);

watch(() => props.modelValue, (newValue) => {
    if (newValue?.start && newValue?.end) {
        try {
            const start = parseDate(newValue.start);
            const end = parseDate(newValue.end);
            calendarValue.value = { start, end };
            calendarPlaceholder.value = start;
        } catch (e) {
            calendarValue.value = undefined;
        }
    } else {
        calendarValue.value = undefined;
    }
}, { immediate: true });

const formatDate = (dateStr: string | null | undefined): string => {
    if (!dateStr) return '';

    try {
        const date = parseDate(dateStr);
        return new Intl.DateTimeFormat('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        }).format(date.toDate(getLocalTimeZone()));
    } catch (e) {
        return dateStr;
    }
};

// Display text for the button
const displayText = computed(() => {
    if (props.modelValue?.start && props.modelValue?.end) {
        return `${formatDate(props.modelValue.start)} - ${formatDate(props.modelValue.end)}`;
    }
    return props.placeholder;
});

// Check if there's an active selection
const hasValue = computed(() => {
    return !!(props.modelValue?.start && props.modelValue?.end);
});

const handleCalendarChange = (value: DateRange | undefined) => {
    if (value?.start && value?.end) {
        const startStr = value.start.toString();
        const endStr = value.end.toString();

        const newValue = {
            start: startStr,
            end: endStr,
        };

        emit('update:modelValue', newValue);
        emit('change', newValue);

        // Close popover after selection
        setTimeout(() => {
            open.value = false;
        }, 150);
    }
};

const clearSelection = (e: Event) => {
    e.preventDefault();
    e.stopPropagation();
    const emptyValue = { start: null, end: null };
    emit('update:modelValue', emptyValue);
    emit('change', emptyValue);

    calendarValue.value = undefined;
    calendarPlaceholder.value = today(getLocalTimeZone());
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <div class="relative w-full">
                <Button
                    variant="outline"
                    :disabled="disabled"
                    :class="cn(
                        'w-full justify-start text-left font-normal',
                        !hasValue && 'text-muted-foreground'
                    )"
                >
                    <Calendar class="mr-2 h-4 w-4" />
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
        <PopoverContent class="w-auto p-0" align="start">
            <RangeCalendarRoot
                v-slot="{ date, grid, weekDays }"
                v-model="calendarValue"
                v-model:placeholder="calendarPlaceholder"
                :disabled="disabled"
                :class="cn(
                    'rounded-md border p-3',
                    '[&_[data-selected]]:bg-primary',
                    '[&_[data-selected]]:text-primary-foreground',
                    '[&_[data-selected]:hover]:bg-primary',
                    '[&_[data-selected]:hover]:text-primary-foreground',
                    '[&_[data-selected]:focus]:bg-primary',
                    '[&_[data-selected]:focus]:text-primary-foreground',
                    '[&_[role=gridcell]:not([aria-disabled=true])]:cursor-pointer',
                    '[&_[role=gridcell]:not([aria-disabled=true])_*]:cursor-pointer'
                )"
                @update:model-value="handleCalendarChange"
            >
                <RangeCalendarHeader>
                    <RangeCalendarHeading class="flex w-full items-center justify-between gap-2">
                        <!-- Month Select -->
                        <Select
                            :default-value="calendarPlaceholder.month.toString()"
                            @update:model-value="(v) => {
                                if (!v || !calendarPlaceholder) return;
                                if (Number(v) === calendarPlaceholder?.month) return;
                                calendarPlaceholder = calendarPlaceholder.set({
                                    month: Number(v),
                                })
                            }"
                        >
                            <SelectTrigger aria-label="Select month" class="w-[60%]">
                                <SelectValue placeholder="Select month" />
                            </SelectTrigger>
                            <SelectContent class="max-h-[200px]">
                                <SelectItem
                                    v-for="month in createYear({ dateObj: date })"
                                    :key="month.toString()"
                                    :value="month.month.toString()"
                                >
                                    {{ formatter.custom(toDate(month), { month: 'long' }) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Year Select -->
                        <Select
                            :default-value="calendarPlaceholder.year.toString()"
                            @update:model-value="(v) => {
                                if (!v || !calendarPlaceholder) return;
                                if (Number(v) === calendarPlaceholder?.year) return;
                                calendarPlaceholder = calendarPlaceholder.set({
                                    year: Number(v),
                                })
                            }"
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
                    </RangeCalendarHeading>
                </RangeCalendarHeader>

                <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
                    <RangeCalendarGrid v-for="month in grid" :key="month.value.toString()">
                        <RangeCalendarGridHead>
                            <RangeCalendarGridRow>
                                <RangeCalendarHeadCell v-for="day in weekDays" :key="day">
                                    {{ day }}
                                </RangeCalendarHeadCell>
                            </RangeCalendarGridRow>
                        </RangeCalendarGridHead>
                        <RangeCalendarGridBody class="grid">
                            <RangeCalendarGridRow
                                v-for="(weekDates, index) in month.rows"
                                :key="`weekDate-${index}`"
                                class="mt-2 w-full"
                            >
                                <RangeCalendarCell
                                    v-for="weekDate in weekDates"
                                    :key="weekDate.toString()"
                                    :date="weekDate"
                                >
                                    <RangeCalendarCellTrigger
                                        :day="weekDate"
                                        :month="month.value"
                                    />
                                </RangeCalendarCell>
                            </RangeCalendarGridRow>
                        </RangeCalendarGridBody>
                    </RangeCalendarGrid>
                </div>
            </RangeCalendarRoot>
        </PopoverContent>
    </Popover>
</template>
