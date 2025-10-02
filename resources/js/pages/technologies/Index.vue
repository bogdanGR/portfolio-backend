<template>
    <Head title="Skills" />
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
                    <Link :href="route('technologies.create')">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add New Skill
                        </Button>
                    </Link>
                    <Button v-if="tableFilters.hasActiveFilters.value" variant="outline" size="sm" @click="tableFilters.clearFilters">
                        <X class="mr-1 h-4 w-4" />
                        Clear Filters
                    </Button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Total: <span class="mb-2 font-bold">{{ props.technologies.total }}</span> records
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
                            column="slug"
                            label="Slug"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.slug"
                                    placeholder="Search slug..."
                                    @change="(val) => tableFilters.updateFilter('slug', val, true)"
                                />
                            </template>
                        </SortableTableHead>

                        <SortableTableHead
                            column="category"
                            label="Category"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterSelect
                                    :model-value="tableFilters.filters.category"
                                    :options="categoryOptions"
                                    placeholder="All Categories"
                                    @change="(val) => tableFilters.updateFilter('category', val)"
                                />
                            </template>
                        </SortableTableHead>

                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="props.technologies.data.length === 0">
                        <TableCell colspan="4" class="h-24 text-center text-muted-foreground"> No technologies found </TableCell>
                    </TableRow>
                    <TableRow v-for="skill in props.technologies.data" :key="skill.id">
                        <TableCell>{{ skill.name }}</TableCell>
                        <TableCell>{{ skill.slug }}</TableCell>
                        <TableCell>{{ skill.category }}</TableCell>
                        <TableCell class="space-x-2">
                            <Link :href="route('technologies.edit', { id: skill.id })">
                                <Button>Edit</Button>
                            </Link>
                            <Button class="bg-red-600" @click="handleDelete(skill.id)">Delete</Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
        <div class="mt-4">
            <Pagination
                v-slot="{ page }"
                :items-per-page="props.technologies.per_page"
                :total="props.technologies.total"
                :default-page="props.technologies.current_page"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious
                        :disabled="props.technologies.current_page <= 1"
                        @click="tableFilters.goToPage(props.technologies.current_page - 1)"
                    />

                    <template v-if="items?.length">
                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :value="item.value"
                                :is-active="item.value === props.technologies.current_page"
                                @click="tableFilters.goToPage(item.value)"
                            >
                                {{ item.value }}
                            </PaginationItem>
                        </template>

                        <PaginationEllipsis v-if="items.some((i) => i.type === 'ellipsis')" />
                    </template>

                    <template v-else>
                        <PaginationItem
                            v-for="n in props.technologies.last_page"
                            :key="n"
                            :value="n"
                            :is-active="n === props.technologies.current_page"
                            @click="tableFilters.goToPage(n)"
                        >
                            {{ n }}
                        </PaginationItem>
                    </template>

                    <PaginationNext
                        :disabled="props.technologies.current_page >= props.technologies.last_page"
                        @click="tableFilters.goToPage(props.technologies.current_page + 1)"
                    />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import ColumnFilterInput from '@/components/ui/table/ColumnFilterInput.vue';
import ColumnFilterSelect from '@/components/ui/table/ColumnFilterSelect.vue';
import SortableTableHead from '@/components/ui/table/SortableTableHead.vue';
import { useTableFilters } from '@/composables/useTableFilters';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Plus, Rocket, X } from 'lucide-vue-next';
import { computed } from 'vue';

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
    id: number;
    name: string;
    slug: string;
    category: string;
}

interface Props {
    technologies: Paginator<Technology>;
    filters?: {
        name?: string;
        slug?: string;
        category?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
    categories: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Technologies',
        href: '/technologies',
    },
];

const page = usePage();

// Initialize table filters
const tableFilters = useTableFilters({
    routeName: 'technologies.index',
    initialFilters: {
        name: props.filters?.name || '',
        slug: props.filters?.slug || '',
        category: props.filters?.category || '',
        sort: 'name',
        direction: 'asc',
    },
    debounceMs: 300,
});

// Transform categories for select options
const categoryOptions = computed(() =>
    props.categories.map((cat) => ({
        value: cat,
        label: formatCategory(cat),
    })),
);

function formatCategory(category: string): string {
    return category
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this skill?')) {
        router.delete(route('technologies.destroy', { id }), {
            preserveScroll: true,
        });
    }
};
</script>
