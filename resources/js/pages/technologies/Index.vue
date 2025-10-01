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

            <p class="mb-2 font-bold">Total records: {{ props.technologies.length }}</p>
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
                    <TableRow v-for="skill in props.technologies" :key="skill.id">
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
    </AppLayout>
</template>
<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Button from '@/components/ui/button/Button.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Rocket } from 'lucide-vue-next';

interface Technology {
    id: number;
    name: string;
    slug: string;
    category: string;
}

interface Props {
    technologies: Technology[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Technologies',
        href: '/technologies',
    },
];
const page = usePage();

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this skill?')) {
        router.delete(route('technologies.destroy', { id }));
    }
};
</script>
