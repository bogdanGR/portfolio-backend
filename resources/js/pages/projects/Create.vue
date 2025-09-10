<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, useForm } from '@inertiajs/vue3'
import RichTextEditor from '@/components/RichTextEditor.vue'
import { Textarea } from '@/components/ui/textarea'
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Project Create', href: '/projects' }]

const form = useForm({
    name: '',
    short_description: '',
    long_description: '', // HTML or plain text
    link: '',
    github_link: '',
})

function submit() {
    form.post(route('projects.store'))
}
</script>

<template>
    <Head title="Project Create" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-4">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid items-start gap-6 md:grid-cols-3">
                    <!-- Name -->
                    <div class="flex flex-col">
                        <Label for="product_name" class="mb-1 text-gray-900 dark:text-gray-100">Name</Label>
                        <Input
                            v-model="form.name"
                            id="product_name"
                            type="text"
                            placeholder="Name"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                     dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Short Description -->
                    <div class="flex flex-col">
                        <Label for="short_description" class="mb-1 text-gray-900 dark:text-gray-100">Short Description</Label>
                        <Input
                            v-model="form.short_description"
                            id="short_description"
                            type="text"
                            placeholder="Short Description"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                     dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.short_description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.short_description }}
                        </div>
                    </div>

                    <!-- (Optional) Editor example: swap Textarea with your RichTextEditor and bind v-model -->
                    <div class="flex flex-col md:col-span-3">
                        <Label for="long_description" class="mb-1 text-gray-900 dark:text-gray-100">Long Description</Label>

                        <!-- Use Textarea -->
                        <Textarea
                            v-model="form.long_description"
                            id="long_description"
                            placeholder="Tell about the project…"
                            class="min-h-32 w-full rounded-md border border-gray-300 bg-gray-50 text-gray-900 placeholder-gray-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                     dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />

                        <!-- Or, if using RichTextEditor with HTML output, ensure it supports dark styles internally -->
                        <!--
                        <RichTextEditor
                          v-model="form.long_description"
                          class="rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                        />
                        -->

                        <div v-if="form.errors.long_description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.long_description }}
                        </div>
                    </div>

                    <!-- Site Link -->
                    <div class="flex flex-col">
                        <Label for="link" class="mb-1 text-gray-900 dark:text-gray-100">Site Link</Label>
                        <Input
                            v-model="form.link"
                            id="link"
                            type="url"
                            placeholder="Site link"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                     dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.link" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.link }}
                        </div>
                    </div>

                    <!-- Github Link -->
                    <div class="flex flex-col">
                        <Label for="github_link" class="mb-1 text-gray-900 dark:text-gray-100">Github</Label>
                        <Input
                            v-model="form.github_link"
                            id="github_link"
                            type="url"
                            placeholder="Github link"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                     dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.github_link" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.github_link }}
                        </div>
                    </div>
                </div>
                <Button type="submit" class="mt-2" :disabled="form.processing">Save</Button>
            </form>
        </div>
    </AppLayout>
</template>
