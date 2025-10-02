<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue'

export interface TechnologyOption {
    id: number,
    name: string,
    slug?: string,
    category?: string | null,
}

const props = defineProps<{
    modelValue: number[],
    options: TechnologyOption[],
    placeholder?: string,
    disabled?: boolean,
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void
    (e: 'blur'): void
    (e: 'focus'): void
}>()

const open = ref(false)        // dropdown open
const sortOpen = ref(false)    // sort box open
const search = ref('')
const containerRef = ref<HTMLElement | null>(null)
const inputRef = ref<HTMLInputElement | null>(null)
const activeIndex = ref<number>(-1)

const selectedIds = computed<number[]>({
    get: () => Array.isArray(props.modelValue) ? props.modelValue : [],
    set: (val) => emit('update:modelValue', val),
})

const selectedMap = computed<Record<number, boolean>>(() => {
    const map: Record<number, boolean> = {}
    for (const id of selectedIds.value) map[id] = true
    return map
})

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return props.options
    return props.options.filter(opt =>
        opt.name.toLowerCase().includes(q) ||
        opt.slug?.toLowerCase().includes(q) ||
        opt.category?.toLowerCase().includes(q)
    )
})

function toggleOpen() {
    if (props.disabled) return
    open.value = !open.value
    if (open.value) requestAnimationFrame(() => inputRef.value?.focus())
}

function selectOption(opt: TechnologyOption) {
    if (selectedMap.value[opt.id]) {
        selectedIds.value = selectedIds.value.filter(id => id !== opt.id)
    } else {
        selectedIds.value = [...selectedIds.value, opt.id]
    }
}

function removeTag(id: number) {
    selectedIds.value = selectedIds.value.filter(x => x !== id)
}

function clearAll() {
    selectedIds.value = []
}

function moveUp(index: number) {
    if (index <= 0) return;
    const arr = [...selectedIds.value];
    [arr[index - 1], arr[index]] = [arr[index], arr[index - 1]];
    selectedIds.value = arr;
}

function moveDown(index: number) {
    if (index >= selectedIds.value.length - 1) return;
    const arr = [...selectedIds.value];
    [arr[index + 1], arr[index]] = [arr[index], arr[index + 1]]
    selectedIds.value = arr;
}

function onKeydown(e: KeyboardEvent) {
    if (!open.value) {
        if (e.key === 'ArrowDown' || e.key === 'Enter') {
            open.value = true
            requestAnimationFrame(() => inputRef.value?.focus())
            e.preventDefault()
        }
        return
    }
    const list = filtered.value
    if (e.key === 'ArrowDown') {
        activeIndex.value = Math.min(activeIndex.value + 1, list.length - 1); e.preventDefault()
    } else if (e.key === 'ArrowUp') {
        activeIndex.value = Math.max(activeIndex.value - 1, 0); e.preventDefault()
    } else if (e.key === 'Enter') {
        if (list[activeIndex.value]) selectOption(list[activeIndex.value]); e.preventDefault()
    } else if (e.key === 'Escape') {
        open.value = false; e.preventDefault()
    }
}

// Close dropdown when clicking outside (but do NOT force-close sort box)
function handleClickOutside(e: MouseEvent) {
    if (!containerRef.value) return
    if (!containerRef.value.contains(e.target as Node)) {
        open.value = false
        emit('blur')
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
watch(open, (v) => { if (v) emit('focus') })
</script>

<template>
    <div ref="containerRef" class="w-full space-y-2">
        <!-- Control -->
        <div
            class="relative border rounded-lg px-2 py-1.5 flex flex-wrap items-center gap-1 cursor-text focus-within:ring-2 focus-within:ring-primary/40 bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700"
            :class="disabled ? 'opacity-60 cursor-not-allowed' : ''"
            @click="toggleOpen"
            @keydown="onKeydown"
            tabindex="0"
            role="combobox"
            aria-expanded="open"
            aria-haspopup="listbox"
        >
            <!-- Chips -->
            <template v-if="selectedIds.length">
        <span
            v-for="(id, idx) in selectedIds.slice(0, 3)"
            :key="id"
            class="inline-flex items-center gap-1 text-sm rounded-md px-2 py-0.5 bg-gray-100 dark:bg-gray-800"
            @click.stop
        >
          {{ options.find(o => o.id === id)?.name ?? id }}
          <button
              type="button"
              class="hover:opacity-80"
              @click.prevent.stop="removeTag(id)"
              aria-label="Remove"
          >
            ×
          </button>
        </span>
                <span
                    v-if="selectedIds.length > 3"
                    class="inline-flex items-center text-sm px-2 py-0.5 text-gray-500 dark:text-gray-400"
                >
          +{{ selectedIds.length - 3 }} more...
        </span>
            </template>

            <!-- Input / Placeholder -->
            <input
                ref="inputRef"
                v-model="search"
                :placeholder="selectedIds.length ? '' : (placeholder ?? 'Search…')"
                :disabled="disabled"
                class="flex-1 min-w-0 bg-transparent outline-none text-sm py-0.5"
                style="max-width: 120px;"
            />

            <!-- Actions -->
            <div class="ml-auto flex items-center gap-2" @click.stop>
                <button
                    v-if="selectedIds.length && !disabled"
                    type="button"
                    class="text-sm underline underline-offset-2"
                    @click="clearAll"
                >
                    Clear
                </button>
                <button
                    v-if="selectedIds.length && !disabled"
                    type="button"
                    class="text-sm underline underline-offset-2"
                    @click="sortOpen = !sortOpen"
                    aria-expanded="sortOpen"
                    aria-controls="tech-sort-box"
                >
                    Sort
                </button>
            </div>

            <!-- Chevron -->
            <span class="ml-2">▾</span>
        </div>

        <!-- Dropdown -->
        <div v-if="open" class="relative">
            <ul
                role="listbox"
                class="absolute z-20 mt-1 max-h-64 w-full overflow-auto rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-lg"
            >
                <li
                    v-if="!filtered.length"
                    class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                >
                    No results
                </li>

                <li
                    v-for="(opt, i) in filtered"
                    :key="opt.id"
                    role="option"
                    :aria-selected="!!selectedMap[opt.id]"
                    class="px-3 py-2 text-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
                    :class="i === activeIndex ? 'bg-gray-50 dark:bg-gray-800' : ''"
                    @click.stop.prevent="selectOption(opt)"
                    @mousemove="activeIndex = i"
                >
                    <div class="flex items-center gap-2">
                        <input type="checkbox" :checked="!!selectedMap[opt.id]" @change.stop />
                        <span class="font-medium">{{ opt.name }}</span>
                        <span v-if="opt.category" class="text-xs opacity-70">· {{ opt.category }}</span>
                    </div>
                    <span class="text-xs opacity-60">{{ opt.slug }}</span>
                </li>
            </ul>
        </div>

        <!-- SORT BOX (togglable) -->
        <div
            v-if="sortOpen && selectedIds.length"
            id="tech-sort-box"
            class="rounded-lg border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700"
            @click.stop
        >
            <div class="px-3 py-2 flex items-center justify-between">
                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Order (top = first)
                </div>
                <button
                    type="button"
                    class="px-2 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                    aria-label="Close sort"
                    @click="sortOpen = false"
                >
                    ×
                </button>
            </div>

            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                <li
                    v-for="(id, idx) in selectedIds"
                    :key="'ordered-' + id"
                    class="flex items-center justify-between px-3 py-2"
                >
                    <div class="text-sm">
                        {{ options.find(o => o.id === id)?.name ?? id }}
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="px-2 py-1 text-xs rounded border dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40"
                            :disabled="idx === 0"
                            @click="moveUp(idx)"
                        >
                            ↑ Up
                        </button>
                        <button
                            type="button"
                            class="px-2 py-1 text-xs rounded border dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40"
                            :disabled="idx === selectedIds.length - 1"
                            @click="moveDown(idx)"
                        >
                            ↓ Down
                        </button>
                        <button
                            type="button"
                            class="px-2 py-1 text-xs rounded border dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
                            @click="removeTag(id)"
                            aria-label="Remove"
                        >
                            Remove
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
