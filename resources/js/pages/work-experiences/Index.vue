<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';

// UI components (adjust paths)
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import ColumnFilterInput from '@/components/ui/table/ColumnFilterInput.vue';
import SortableTableHead from '@/components/ui/table/SortableTableHead.vue';
import { useTableFilters } from '@/composables/useTableFilters';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Pencil, Plus, Trash2, X } from 'lucide-vue-next';

type LinkType = { url: string | null; label: string; active: boolean };

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: route('dashboard') }, { title: 'Work Experience' }];

type Paginator<Item> = {
    data: Item[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: LinkType[];
};

interface Experience {
    job_title: string;
    company_name: string;
    company_website: string;
    start_date: string;
    end_date: string;
}

interface Props {
    experiences: Paginator<Experience>;
    filters?: {
        job_title?: string;
        company_name?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
    flash?: { message?: string };
}
const props = defineProps<Props>();
const page = usePage();

const tableFilters = useTableFilters({
    routeName: 'work-experiences.index',
    initialFilters: {
        job_title: props.filters?.job_title || '',
        company_name: props.filters?.company_name || '',
        sort: 'start_date',
        direction: 'asc',
    },
    debounceMs: 300,
});
const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this work experience?')) {
        router.delete(route('work-experiences.destroy', { id }));
    }
};
</script>

<template>
    <Head title="Work Experience" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div v-if="page.props.flash?.message">
                <Alert>
                    <AlertTitle>Notification</AlertTitle>
                    <AlertDescription>{{ page.props.flash.message }}</AlertDescription>
                </Alert>
            </div>

            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('work-experiences.create')">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add New Work
                        </Button>
                    </Link>
                    <Button v-if="tableFilters.hasActiveFilters.value" variant="outline" size="sm" @click="tableFilters.clearFilters">
                        <X class="mr-1 h-4 w-4" />
                        Clear Filters
                    </Button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Total: <span class="mb-2 font-bold">{{ props.experiences.total }}</span> records
                </p>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <SortableTableHead
                            column="job_title"
                            label="Job Title"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.job_title"
                                    placeholder="Search job title..."
                                    @change="(val) => tableFilters.updateFilter('job_title', val, true)"
                                />
                            </template>
                        </SortableTableHead>
                        <SortableTableHead
                            column="company_name"
                            label="Company"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.company_name"
                                    placeholder="Search company..."
                                    @change="(val) => tableFilters.updateFilter('company_name', val, true)"
                                />
                            </template>
                        </SortableTableHead>
                        <TableHead>Period</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="exp in props.experiences.data" :key="exp.id">
                        <TableCell class="font-medium">{{ exp.job_title }}</TableCell>
                        <TableCell>
                            <template v-if="exp.company_website">
                                <a :href="exp.company_website" target="_blank" rel="noopener" class="underline">{{ exp.company_name }}</a>
                            </template>
                            <template v-else>{{ exp.company_name }}</template>
                        </TableCell>
                        <TableCell> {{ exp.formatted_start_date }} — {{ exp.formatted_end_date }} </TableCell>
                        <TableCell class="space-x-2 text-right">
                            <Link :href="route('work-experiences.edit', { id: exp.id })">
                                <Button size="sm" variant="outline">
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                            </Link>
                            <Button size="sm" variant="destructive" @click="handleDelete(exp.id)">
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div class="mt-4">
                <Pagination
                    v-slot="{ page }"
                    :items-per-page="props.experiences.per_page"
                    :total="props.experiences.total"
                    :default-page="props.experiences.current_page"
                >
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious
                            :disabled="props.experiences.current_page <= 1"
                            @click="tableFilters.goToPage(props.experiences.current_page - 1)"
                        />

                        <template v-if="items?.length">
                            <template v-for="(item, index) in items" :key="index">
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :value="item.value"
                                    :is-active="item.value === props.experiences.current_page"
                                    @click="tableFilters.goToPage(item.value)"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                            </template>

                            <PaginationEllipsis v-if="items.some((i) => i.type === 'ellipsis')" />
                        </template>

                        <template v-else>
                            <PaginationItem
                                v-for="n in props.experiences.last_page"
                                :key="n"
                                :value="n"
                                :is-active="n === props.experiences.current_page"
                                @click="tableFilters.goToPage(n)"
                            >
                                {{ n }}
                            </PaginationItem>
                        </template>

                        <PaginationNext
                            :disabled="props.experiences.current_page >= props.experiences.last_page"
                            @click="tableFilters.goToPage(props.experiences.current_page + 1)"
                        />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>
