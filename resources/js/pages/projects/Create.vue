<script setup lang="ts">
import { computed } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import RichTextEditor from '@/components/RichTextEditor.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import FileUploader from '@/components/FileUploader.vue';

// Component setup
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Project Create', href: '/projects' }];

const form = useForm({
    name: '',
    short_description: '',
    long_description: '',
    link: '',
    github: '',
    images: [] as File[]
});


// Event handlers
const onImagesChanged = (images: any[]) => {
    console.log('Images changed:', images);
};

const onUploadComplete = (files: any[]) => {
    console.log('Upload complete:', files);
};

const onUploadError = (error: string) => {
    console.error('Upload error:', error);
};

const onFeaturedChanged = (index: number) => {
    console.log('Featured image changed to index:', index);
};

const onImageClick = (image: any, index: number) => {
    console.log('Image clicked:', image, index);
};

function submit() {
    form.post(route('projects.store'), {
        forceFormData: true
    });
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
                        <Label for="short_description" class="mb-1 text-gray-900 dark:text-gray-100">Short
                            Description</Label>
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

                    <div class="flex flex-col md:col-span-3">
                        <Label for="long_description" class="mb-1 text-gray-900 dark:text-gray-100">Long
                            Description</Label>
                        <RichTextEditor
                            v-model="form.long_description"
                            class="rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                        />
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
                        <Label for="github" class="mb-1 text-gray-900 dark:text-gray-100">Github</Label>
                        <Input
                            v-model="form.github"
                            id="github"
                            type="url"
                            placeholder="Github link"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500
                     focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500
                     dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.github" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.github }}
                        </div>
                    </div>

                    <FileUploader
                        v-model="form.images"
                        title="Project Images"
                        description="Upload images to showcase your project. The first image will be used as the featured image."
                        field-name="images"
                        :max-files="10"
                        max-file-size="2MB"
                        :allow-multiple="true"
                        :allow-reorder="true"
                        :show-featured="true"
                        :show-file-count="true"
                        accepted-file-types="image/jpeg, image/png, image/gif, image/webp"
                        file-type-description="images"
                        file-singular="image"
                        file-plural="images"
                        label-idle='Drag & Drop your images or <span class="filepond--label-action">Browse</span>'
                        :image-preview-height="150"
                        :image-resize-width="800"
                        :image-resize-height="600"
                        image-resize-mode="contain"
                        @files-changed="onImagesChanged"
                        @upload-complete="onUploadComplete"
                        @upload-error="onUploadError"
                        @featured-changed="onFeaturedChanged"
                        @file-click="onImageClick"
                    >
                        <!-- Custom image grid preview -->
                        <template #file-preview="{ files, reorderMode, removeFile, setFeatured, onDragStart, onDragOver, onDrop, showFeatured }">
                            <div
                                class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4"
                                :class="{ 'cursor-move': reorderMode }"
                            >
                                <div
                                    v-for="(image, index) in files"
                                    :key="image.id || index"
                                    class="relative group aspect-square bg-gray-100 rounded-lg overflow-hidden dark:bg-gray-700 transition-transform duration-200"
                                    :class="{
                                    'hover:scale-105': !reorderMode,
                                    'cursor-move': reorderMode,
                                    'ring-2 ring-indigo-500': reorderMode,
                                    'ring-2 ring-yellow-400': showFeatured && index === 0
                                }"
                                    @click="reorderMode ? null : onImageClick(image, index)"
                                    draggable="true"
                                    @dragstart="onDragStart($event, index)"
                                    @dragover="onDragOver"
                                    @drop="onDrop($event, index)"
                                >
                                    <!-- Image -->
                                    <img
                                        :src="image.preview || image.url"
                                        :alt="image.name"
                                        class="w-full h-full object-cover"
                                    />

                                    <!-- Featured badge -->
                                    <div v-if="showFeatured && index === 0" class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded-full font-medium flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span>Featured</span>
                                    </div>

                                    <!-- Reorder indicator -->
                                    <div v-if="reorderMode" class="absolute top-2 right-2 bg-indigo-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">
                                        {{ index + 1 }}
                                    </div>

                                    <!-- Action buttons -->
                                    <div v-if="!reorderMode" class="absolute top-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <!-- Set featured button -->
                                        <button
                                            v-if="showFeatured && index !== 0"
                                            @click.stop="setFeatured(index)"
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-full p-1 transition-colors duration-200"
                                            title="Set as featured"
                                        >
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>

                                        <!-- Remove button -->
                                        <button
                                            @click.stop="removeFile(index)"
                                            class="bg-red-500 hover:bg-red-600 text-white rounded-full p-1 transition-colors duration-200"
                                            title="Remove image"
                                        >
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- File name -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-xs p-2 truncate">
                                        {{ image.name }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </FileUploader>
                    <div v-if="form.errors.images" class="p-3 bg-red-50 border border-red-200 rounded-md dark:bg-red-900/20 dark:border-red-800">
                        <p class="text-sm text-red-800 dark:text-red-400">{{ form.errors.images }}</p>
                    </div>
                </div>
                <Button type="submit" class="mt-2" :disabled="form.processing">Save</Button>
            </form>
        </div>
    </AppLayout>
</template>
