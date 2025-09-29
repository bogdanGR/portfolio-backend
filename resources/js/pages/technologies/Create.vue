<template>
    <Head title="Skill Create" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-4">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid items-start gap-6 md:grid-cols-3">
                    <!-- Name -->
                    <div class="flex flex-col">
                        <Label for="product_name" class="mb-1 text-gray-900 dark:text-gray-100">Name</Label>
                        <Input
                            v-model="form.name"
                            required
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

                    <!-- Slug -->
                    <div class="flex flex-col">
                        <Label for="slug" class="mb-1 text-gray-900 dark:text-gray-100">Slug</Label>
                        <Input
                            v-model="form.slug"
                            required
                            id="slug"
                            type="text"
                            placeholder="Slug"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500
                                focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                                dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.slug" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.slug }}
                        </div>
                    </div>
                    <div class="flex flex-col relative">
                        <Label for="slug" class="mb-1 text-gray-900 dark:text-gray-100">Category</Label>
                        <category-select :categories="props.categories"  v-model="form.category" />
                        <div v-if="form.errors.slug" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.slug }}
                        </div>
                    </div>
                </div>
                <Button type="submit" class="mt-2" :disabled="form.processing">Save</Button>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../components/ui/button/Button.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';
import categorySelect from  '@/components/technologies/categorySelect.vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Skill Create', href: '/technologies' }];

const form = useForm({
    name: '',
    slug: '',
    category: '',
});

const props = defineProps<{
    categories?: string[],
}>();

function submit() {
    form.post(route('technologies.store'), {
        forceFormData: true
    });
}
</script>
