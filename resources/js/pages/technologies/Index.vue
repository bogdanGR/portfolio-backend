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
            <Link :href="route('technologies.create')">
                <Button class="mb-4">Add a new Skill</Button>
            </Link>

            <p class="mb-2 font-bold">Total records: {{ props.technologies.total }}</p>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>slug</TableHead>
                        <TableHead>category</TableHead>
                        <TableHead>Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
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
        <Pagination
            v-slot="{ page }"
            :items-per-page="props.technologies.per_page"
            :total="props.technologies.total"
            :default-page="props.technologies.current_page"
        >
            <PaginationContent v-slot="{ items }">
                <PaginationPrevious :disabled="props.technologies.current_page <= 1" @click="goTo(props.technologies.current_page - 1)" />

                <!-- If your Pagination component provides an 'items' model -->
                <template v-if="items?.length">
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem
                            v-if="item.type === 'page'"
                            :value="item.value"
                            :is-active="item.value === props.technologies.current_page"
                            @click="goTo(item.value)"
                        >
                            {{ item.value }}
                        </PaginationItem>
                    </template>

                    <PaginationEllipsis v-if="items.some((i) => i.type === 'ellipsis')" />
                </template>

                <!-- Fallback: render 1..last_page -->
                <template v-else>
                    <PaginationItem
                        v-for="n in props.technologies.last_page"
                        :key="n"
                        :value="n"
                        :is-active="n === props.technologies.current_page"
                        @click="goTo(n)"
                    >
                        {{ n }}
                    </PaginationItem>
                </template>

                <PaginationNext
                    :disabled="props.technologies.current_page >= props.technologies.last_page"
                    @click="goTo(props.technologies.current_page + 1)"
                />
            </PaginationContent>
        </Pagination>
    </AppLayout>
</template>
<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Rocket } from 'lucide-vue-next';

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
    filters?: Record<string, unknown>;
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Technologies',
        href: '/technologies',
    },
];
const page = usePage();

function goTo(page: number) {
    router.get(
        route('technologies.index'),
        { ...(props.filters ?? {}), page }, // keep filters, change page
        { preserveScroll: true, preserveState: true, replace: true },
    );
}

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this skill?')) {
        router.delete(route('technologies.destroy', { id }));
    }
};
</script>
