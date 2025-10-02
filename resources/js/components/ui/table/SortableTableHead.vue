<template>
    <TableHead class="p-0">
        <div class="flex flex-col">
            <!-- Header with Sort -->
            <button
                class="flex items-center justify-between gap-2 px-4 py-3 text-left font-medium hover:bg-muted/50 transition-colors"
                :class="{ 'text-foreground': isActive }"
                @click="$emit('sort', column)"
            >
                <span>{{ label }}</span>
                <component
                    :is="sortIcon"
                    :class="[
                        'h-4 w-4 flex-shrink-0 transition-colors',
                        isActive ? 'text-foreground' : 'text-muted-foreground',
                    ]"
                />
            </button>

            <!-- Filter Slot -->
            <div v-if="filterable" class="border-t">
                <slot name="filter" />
            </div>
        </div>
    </TableHead>
</template>

<script setup lang="ts">
import { TableHead } from '@/components/ui/table';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    column: string;
    label: string;
    activeSort: string;
    direction: 'asc' | 'desc';
    filterable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    filterable: false,
});

defineEmits<{
    sort: [column: string];
}>();

const isActive = computed(() => props.activeSort === props.column);

const sortIcon = computed(() => {
    if (!isActive.value) return ArrowUpDown;
    return props.direction === 'asc' ? ArrowUp : ArrowDown;
});
</script>
