<template>
    <Head title="Projects" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div v-if="page.props.flash?.message" class="alert">
                <Alert class="">
                    <Rocket class="h-4 w-4" />
                    <AlertTitle>Notification</AlertTitle>
                    <AlertDescription>
                        {{ page.props.flash.message }}
                    </AlertDescription>
                </Alert>
            </div>
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('projects.create')">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add New Project
                        </Button>
                    </Link>
                    <Button v-if="tableFilters.hasActiveFilters.value" variant="outline" size="sm" @click="tableFilters.clearFilters">
                        <X class="mr-1 h-4 w-4" />
                        Clear Filters
                    </Button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Total: <span class="mb-2 font-bold">{{ props.projects.total }}</span> records
                </p>
            </div>
            <Table>
                <TableHeader>
                    <TableRow>
                        <SortableTableHead
                            column="name"
                            label="Name"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.name"
                                    placeholder="Search name..."
                                    @change="(val) => tableFilters.updateFilter('name', val, true)"
                                />
                            </template>
                        </SortableTableHead>
                        <SortableTableHead
                            column="short_description"
                            label="Short Description"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.short_description"
                                    placeholder="Search short description..."
                                    @change="(val) => tableFilters.updateFilter('short_description', val, true)"
                                />
                            </template>
                        </SortableTableHead>
                        <SortableTableHead
                            column="technology"
                            label="Skills"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <column-filter-multi-select
                                    :model-value="filterTechnologies"
                                    :options="technologyOptions"
                                    placeholder="All Skills"
                                    search-placeholder="Search skills..."
                                    @change="(val) => tableFilters.updateFilter('technologies', val)"
                                />
                            </template>
                        </SortableTableHead>
                        <SortableTableHead
                            column="link"
                            label="Website URL"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.link"
                                    placeholder="Search Website URL..."
                                    @change="(val) => tableFilters.updateFilter('link', val, true)"
                                />
                            </template>
                        </SortableTableHead>
                        <SortableTableHead
                            column="github"
                            label="Github URL"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.github"
                                    placeholder="Search Github URL..."
                                    @change="(val) => tableFilters.updateFilter('github', val, true)"
                                />
                            </template>
                        </SortableTableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="project in props.projects.data" :key="project.id">
                        <TableCell>{{ project.name }}</TableCell>
                        <TableCell>{{ project.short_description }}</TableCell>
                        <TableCell class="max-w-[280px]">
                            <div class="flex items-center gap-1">
                                <template v-if="Array.isArray(project.technologies) && project.technologies.length">
                                    <!-- show first 2 badges -->
                                    <span
                                        v-for="(tech, i) in project.technologies.slice(0, VISIBLE_TECH_COUNT)"
                                        :key="getTechKey(tech, i)"
                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-0.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                    >
                                        {{ getTechName(tech) }}
                                    </span>

                                    <Popover
                                        v-if="project.technologies.length > VISIBLE_TECH_COUNT"
                                        :key="`popover-${project.id}`"
                                        :open="popoverOpen[project.id] === true"
                                        @update:open="(val) => (popoverOpen[project.id] = val)"
                                    >
                                        <PopoverTrigger
                                            class="text-xs underline decoration-dotted underline-offset-2"
                                            @focus="openPopover(project.id)"
                                            @mouseenter="openPopover(project.id)"
                                            @mouseleave="scheduleClose(project.id)"
                                        >
                                            +{{ project.technologies.length - VISIBLE_TECH_COUNT }} more
                                        </PopoverTrigger>

                                        <PopoverContent
                                            align="start"
                                            class="w-64"
                                            side="bottom"
                                            @mouseenter="openPopover(project.id)"
                                            @mouseleave="scheduleClose(project.id)"
                                        >
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="(tech, i) in project.technologies.slice(VISIBLE_TECH_COUNT)"
                                                    :key="getTechKey(tech, i + VISIBLE_TECH_COUNT)"
                                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-0.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                                >
                                                    {{ getTechName(tech) }}
                                                </span>
                                            </div>
                                        </PopoverContent>
                                    </Popover>
                                </template>

                                <span v-else class="text-gray-400">—</span>
                            </div>
                        </TableCell>
                        <TableCell>
                            <a v-if="project.link" :href="project.link" target="_blank">Website link</a>
                        </TableCell>
                        <TableCell>
                            <a v-if="project.github" :href="project.github" target="_blank">Github projects' link</a>
                        </TableCell>
                        <TableCell class="space-x-2">
                            <Link :href="route('projects.edit', { id: project.id })">
                                <Button size="sm" variant="outline">
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                            </Link>
                            <Button size="sm" variant="destructive" @click="handleDelete(project.id)">
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
        <div class="mt-4">
            <Pagination
                v-slot="{ page }"
                :items-per-page="props.projects.per_page"
                :total="props.projects.total"
                :default-page="props.projects.current_page"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious
                        :disabled="props.projects.current_page <= 1"
                        @click="tableFilters.goToPage(props.projects.current_page - 1)"
                    />

                    <template v-if="items?.length">
                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :value="item.value"
                                :is-active="item.value === props.projects.current_page"
                                @click="tableFilters.goToPage(item.value)"
                            >
                                {{ item.value }}
                            </PaginationItem>
                        </template>

                        <PaginationEllipsis v-if="items.some((i) => i.type === 'ellipsis')" />
                    </template>

                    <template v-else>
                        <PaginationItem
                            v-for="n in props.projects.last_page"
                            :key="n"
                            :value="n"
                            :is-active="n === props.projects.current_page"
                            @click="tableFilters.goToPage(n)"
                        >
                            {{ n }}
                        </PaginationItem>
                    </template>

                    <PaginationNext
                        :disabled="props.projects.current_page >= props.projects.last_page"
                        @click="tableFilters.goToPage(props.projects.current_page + 1)"
                    />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>

<script lang="ts" setup>
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import ColumnFilterInput from '@/components/ui/table/ColumnFilterInput.vue';
import ColumnFilterMultiSelect from '@/components/ui/table/ColumnFilterMultiSelect.vue';
import SortableTableHead from '@/components/ui/table/SortableTableHead.vue';
import { useTableFilters } from '@/composables/useTableFilters';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Rocket, Trash2, X } from 'lucide-vue-next';
import { computed, reactive } from 'vue';

type LinkType = { url: string | null; label: string; active: boolean };

type Paginator<Item> = {
    data: Item[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: LinkType[];
};

interface Technology {
    id?: number;
    name?: string;
}

interface Project {
    id: number;
    name: string;
    short_description: string;
    link?: string | null;
    github?: string | null;
    technologies?: (Technology | string)[];
}

interface Props {
    projects: Paginator<Project>;
    filters?: {
        name?: string;
        short_description?: string;
        link?: string;
        github?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
    technologies: Technology[];
}

const props = defineProps<Props>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Projects', href: '/projects' }];

const tableFilters = useTableFilters({
    routeName: 'projects.index',
    initialFilters: {
        name: props.filters?.name || '',
        short_description: props.filters?.short_description || '',
        link: props.filters?.link || '',
        github: props.filters?.github || '',
        technologies: props.filters?.technologies || [],
        sort: 'name',
        direction: 'asc',
    },
    debounceMs: 300,
});

const filterTechnologies = computed(() => {
    const val = tableFilters.filters.technologies;
    return Array.isArray(val) ? val : [];
});

const technologyOptions = computed(() =>
    (props.technologies ?? []).map((t) => ({
        value: String(t.id),
        label: t.name ?? '',
    })),
);

const VISIBLE_TECH_COUNT = 3;

const getTechName = (t: Technology | string) => (typeof t === 'string' ? t : (t?.name ?? ''));

const getTechKey = (t: Technology | string, idx: number) => (typeof t === 'string' ? `${t}-${idx}` : `${t?.id ?? idx}-${getTechName(t)}`);

const popoverOpen = reactive<Record<number, boolean>>({});
const closeTimers = reactive<Record<number, number | undefined>>({});

const openPopover = (id: number) => {
    if (closeTimers[id]) {
        clearTimeout(closeTimers[id]);
        closeTimers[id] = undefined;
    }
    popoverOpen[id] = true;
};

const scheduleClose = (id: number, delay = 120) => {
    if (closeTimers[id]) clearTimeout(closeTimers[id]);
    closeTimers[id] = window.setTimeout(() => {
        popoverOpen[id] = false;
        closeTimers[id] = undefined;
    }, delay);
};

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this project?')) {
        router.delete(route('projects.destroy', { id }));
    }
};
</script>
