<template>
    <Head title="Certificates" />
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
                    <Link :href="route('certifications.create')">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add New Certification
                        </Button>
                    </Link>
                    <Button v-if="tableFilters.hasActiveFilters.value" variant="outline" size="sm" @click="tableFilters.clearFilters">
                        <X class="mr-1 h-4 w-4" />
                        Clear Filters
                    </Button>
                </div>
                <p class="text-sm text-muted-foreground">
                    Total: <span class="mb-2 font-bold">{{ props.certifications?.total }}</span> records
                </p>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <SortableTableHead
                            column="id"
                            label="ID"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.id"
                                    placeholder="Search name..."
                                    @change="(val) => tableFilters.updateFilter('id', val, true)"
                                />
                            </template>
                        </SortableTableHead>
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
                            column="issuing_organization"
                            label="Issuing Organization"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.issuing_organization"
                                    placeholder="Search Issuing Organization..."
                                    @change="(val) => tableFilters.updateFilter('issuing_organization', val, true)"
                                />
                            </template>
                        </SortableTableHead>

                        <SortableTableHead
                            column="issue_date"
                            label="Issue Date"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <date-range-filter
                                    :model-value="{
                                        start: tableFilters.filters.issue_date_start,
                                        end: tableFilters.filters.issue_date_end,
                                    }"
                                    placeholder="Filter by issue date..."
                                    @change="handleIssueDateChange"
                                />
                            </template>
                        </SortableTableHead>

                        <SortableTableHead
                            column="expiration_date"
                            label="Expiration Date"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <DateRangeFilter
                                    :model-value="{
                                        start: tableFilters.filters.expiration_date_start,
                                        end: tableFilters.filters.expiration_date_end,
                                    }"
                                    placeholder="Filter by expiration date..."
                                    @change="handleExpirationDateChange"
                                />
                            </template>
                        </SortableTableHead>
                        <SortableTableHead
                            column="credential_id"
                            label="Credential ID"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.credential_id"
                                    placeholder="Search Credit ID..."
                                    @change="(val) => tableFilters.updateFilter('credential_id', val, true)"
                                />
                            </template>
                        </SortableTableHead>
                        <SortableTableHead
                            column="credential_url"
                            label="Credential URL"
                            :active-sort="tableFilters.filters.sort"
                            :direction="tableFilters.filters.direction"
                            filterable
                            @sort="tableFilters.sortBy"
                        >
                            <template #filter>
                                <ColumnFilterInput
                                    :model-value="tableFilters.filters.credential_url"
                                    placeholder="Search Credit URL..."
                                    @change="(val) => tableFilters.updateFilter('credential_url', val, true)"
                                />
                            </template>
                        </SortableTableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="props.certifications.data.length === 0">
                        <TableCell colspan="4" class="h-24 text-center text-muted-foreground"> No certifications found </TableCell>
                    </TableRow>
                    <TableRow v-for="certificate in props.certifications.data" :key="certificate.id">
                        <TableCell>{{ certificate.id }}</TableCell>
                        <TableCell>{{ certificate.name }}</TableCell>
                        <TableCell>{{ certificate.issuing_organization }}</TableCell>
                        <TableCell>{{ certificate.formatted_issue_date }}</TableCell>
                        <TableCell>{{ certificate.formatted_expiration_date }}</TableCell>
                        <TableCell>{{ certificate.credential_id }}</TableCell>
                        <TableCell>{{ certificate.credential_url }}</TableCell>
                        <TableCell class="space-x-2">
                            <Link :href="route('certifications.edit', { id: certificate.id })">
                                <Button size="sm" variant="outline">
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                            </Link>
                            <Button size="sm" variant="destructive" @click="handleDelete(skill.id)">
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
                :items-per-page="props.certifications.per_page"
                :total="props.certifications.total"
                :default-page="props.certifications.current_page"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious
                        :disabled="props.certifications.current_page <= 1"
                        @click="tableFilters.goToPage(props.certifications.current_page - 1)"
                    />

                    <template v-if="items?.length">
                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :value="item.value"
                                :is-active="item.value === props.certifications.current_page"
                                @click="tableFilters.goToPage(item.value)"
                            >
                                {{ item.value }}
                            </PaginationItem>
                        </template>

                        <PaginationEllipsis v-if="items.some((i) => i.type === 'ellipsis')" />
                    </template>

                    <template v-else>
                        <PaginationItem
                            v-for="n in props.certifications.last_page"
                            :key="n"
                            :value="n"
                            :is-active="n === props.certifications.current_page"
                            @click="tableFilters.goToPage(n)"
                        >
                            {{ n }}
                        </PaginationItem>
                    </template>

                    <PaginationNext
                        :disabled="props.certifications.current_page >= props.certifications.last_page"
                        @click="tableFilters.goToPage(props.certifications.current_page + 1)"
                    />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import DateRangeFilter from '@/components/ui/custom-date-range/DateRangeFilter.vue';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import ColumnFilterInput from '@/components/ui/table/ColumnFilterInput.vue';
import SortableTableHead from '@/components/ui/table/SortableTableHead.vue';
import { useTableFilters } from '@/composables/useTableFilters';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Rocket, Trash2, X } from 'lucide-vue-next';

type LinkType = { url: string | null; label: string; active: boolean };

type Paginator<Item> = {
    data: Item[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: LinkType[];
};

interface Certifications {
    id: number;
    name: string;
    issuing_organization: string;
    issue_date: string;
    formatted_issue_date: string;
    formatted_expiration_date: string;
    expiration_date: string;
    credential_id: string;
    credential_url: string;
}

interface Props {
    certifications: Paginator<Certifications>;
    filters?: {
        name?: string;
        issuing_organization?: string;
        issue_date?: string;
        expiration_date?: string;
        credential_id?: string;
        credential_url?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
}

const props = defineProps<Props>();
console.log(props);
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'certifications',
        href: '/certifications',
    },
];

const page = usePage();

// Initialize table filters
const tableFilters = useTableFilters({
    routeName: 'certifications.index',
    initialFilters: {
        id: props.filters?.id || '',
        name: props.filters?.name || '',
        issuing_organization: props.filters?.issuing_organization || '',
        issue_date: props.filters?.issue_date || '',
        expiration_date: props.filters?.expiration_date || '',
        credential_id: props.filters?.credential_id || '',
        credential_url: props.filters?.credential_url || '',
        sort: 'name',
        direction: 'asc',
    },
    debounceMs: 300,
});

const handleIssueDateChange = (value: { start: string | null; end: string | null }) => {
    tableFilters.updateFilter('issue_date_start', value.start, true);
    tableFilters.updateFilter('issue_date_end', value.end, true);
};

const handleExpirationDateChange = (value: { start: string | null; end: string | null }) => {
    tableFilters.updateFilter('expiration_date_start', value.start, true);
    tableFilters.updateFilter('expiration_date_end', value.end, true);
};

// Transform categories for select options
// const categoryOptions = computed(() =>
//     props.categories.map((cat) => ({
//         value: cat,
//         label: formatCategory(cat),
//     })),
// );
//
// function formatCategory(category: string): string {
//     return category
//         .split('_')
//         .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
//         .join(' ');
// }

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this certificate?')) {
        router.delete(route('certifications.destroy', { id }), {
            preserveScroll: true,
        });
    }
};
</script>
