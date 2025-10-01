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
            <Link :href="route('projects.create')">
                <Button class="mb-4">Create a new project</Button>
            </Link>
            <p class="mb-1 mb-2 font-bold">Total Projects: {{ props.projects.total }}</p>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>ID</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Short Description</TableHead>
                        <TableHead>Skills</TableHead>
                        <TableHead>Website URL</TableHead>
                        <TableHead>Github URL</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="project in props.projects.data" :key="project.id">
                        <TableCell>{{ project.id }}</TableCell>
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

                                    <!-- overflow in popover (hover to open) -->
                                    <Popover
                                        v-if="project.technologies.length > VISIBLE_TECH_COUNT"
                                        :key="`popover-${project.id}`"
                                        :open="popoverOpen[project.id] === true"
                                        @update:open="(val) => (popoverOpen[project.id] = val)"
                                    >
                                        <!-- Do NOT use as-child to avoid null parentNode glitches -->
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
                                <Button>Edit</Button>
                            </Link>
                            <Button class="bg-red-600" @click="handleDelete(project.id)">Delete</Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
        <Pagination
            v-slot="{ page }"
            :items-per-page="props.projects.per_page"
            :total="props.projects.total"
            :default-page="props.projects.current_page"
        >
            <PaginationContent v-slot="{ items }">
                <PaginationPrevious :disabled="props.projects.current_page <= 1" @click="goTo(props.projects.current_page - 1)" />

                <!-- If your Pagination component provides an 'items' model -->
                <template v-if="items?.length">
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem
                            v-if="item.type === 'page'"
                            :value="item.value"
                            :is-active="item.value === props.projects.current_page"
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
                        v-for="n in props.projects.last_page"
                        :key="n"
                        :value="n"
                        :is-active="n === props.projects.current_page"
                        @click="goTo(n)"
                    >
                        {{ n }}
                    </PaginationItem>
                </template>

                <PaginationNext :disabled="props.projects.current_page >= props.projects.last_page" @click="goTo(props.projects.current_page + 1)" />
            </PaginationContent>
        </Pagination>
    </AppLayout>
</template>

<script lang="ts" setup>
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Rocket } from 'lucide-vue-next';
import { reactive } from 'vue';

// ----- Types -----
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
    filters?: Record<string, unknown>;
}

// ----- Props / page -----
const props = defineProps<Props>();
const page = usePage();

// ----- Breadcrumbs -----
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Projects', href: '/projects' }];

// ----- Pagination navigation -----
function goTo(page: number) {
    router.get(
        route('projects.index'),
        { ...(props.filters ?? {}), page }, // keep filters, change page
        { preserveScroll: true, preserveState: true, replace: true },
    );
}

// ----- Technologies display helpers -----
const VISIBLE_TECH_COUNT = 3;

const getTechName = (t: Technology | string) => (typeof t === 'string' ? t : (t?.name ?? ''));

const getTechKey = (t: Technology | string, idx: number) => (typeof t === 'string' ? `${t}-${idx}` : `${t?.id ?? idx}-${getTechName(t)}`);

// ----- Per-row popover open state + close timers -----
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

// ----- Delete handler -----
const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this project?')) {
        router.delete(route('projects.destroy', { id }));
    }
};
</script>
