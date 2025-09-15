<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Rocket } from 'lucide-vue-next';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

interface Project {
    id: number,
    name: string,
    short_description: string,
}

interface Props{
    projects: Project[];
}
//get props from inerτia
const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
];
const page = usePage();

const handleDelete = (id: number) => {
    if(confirm('Are you sure you want to delete this project?')) {
        router.delete(route('projects.destroy', {id}));
    }
}
</script>

<template>
    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div v-if="page.props.flash?.message" class="alert">
                <Alert class="">
                    <Rocket class="h-4 w-4"/>
                    <AlertTitle>Notification</AlertTitle>
                    <AlertDescription>
                        {{ page.props.flash.message }}
                    </AlertDescription>
                </Alert>
            </div>
            <Link :href="route('projects.create')">
                <Button class="mb-4">Create a new project</Button>
            </Link>

            <Table>
                <TableCaption>A list of your recent invoices.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>ID</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Short Description</TableHead>
                        <TableHead>Website URL</TableHead>
                        <TableHead>Github URL</TableHead>
                        <TableHead>Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="project in props.projects" :key="project.id">
                        <TableCell>{{ project.id }}</TableCell>
                        <TableCell>{{ project.name }}</TableCell>
                        <TableCell>{{ project.short_description }}</TableCell>
                        <TableCell>
                            <a v-if="project.link" :href="project.link" target="_blank">Website link</a>
                        </TableCell>
                        <TableCell>
                            <a v-if="project.github" :href="project.github" target="_blank">Github projects' link</a>
                        </TableCell>
                        <TableCell class="text-center space-x-2">
                            <Link :href="route('projects.edit', {id: project.id})">
                                <Button>Edit</Button>
                            </Link>
                            <Button class="bg-red-600" @click="handleDelete(project.id)">Delete</Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>


<style scoped>

</style>
