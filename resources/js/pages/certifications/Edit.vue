<script setup lang="ts">
import FileUploader from '@/components/FileUploader.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import TechnologyMultiSelect, { type TechnologyOption } from '@/components/ui/technology-multi-select/TechnologyMultiSelect.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Button from '../../components/ui/button/Button.vue';
import DatePicker from '../../components/ui/date-picker/DatePicker.vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Certification Create', href: '/certifications' }];

const isUploading = ref(false);

interface CertificationImage {
    id: number;
    original_name: string;
    filename: string;
    path: string;
    mime_type: string;
    size?: number;
    url: string;
}

interface Certification {
    name: string;
    issuing_organization: string;
    issue_date: string;
    expiration_date: string;
    credential_id: string;
    credential_url: string;
    certification_image?: CertificationImage | null;
}
const props = defineProps<{
    certification: Certification;
    technologiesAll: TechnologyOption[];
    technologySelectedIds?: number[];
}>();

// Transform the certification_image into an array format for FileUploader
const existingImage = computed(() => {
    if (!props.certification.certification_image) {
        return [];
    }

    const img = props.certification.certification_image;

    return [
        {
            id: img.id,
            name: img.original_name,
            original_name: img.original_name,
            url: img.url,
            size: img.size || 0,
            type: img.mime_type,
        },
    ];
});

const form = useForm({
    name: props.certification.name,
    issuing_organization: props.certification.issuing_organization,
    issue_date: props.certification.issue_date,
    expiration_date: props.certification.expiration_date,
    credential_id: props.certification.credential_id,
    credential_url: props.certification.credential_url,
    certificationImage: null as File | null,
    technology_ids: Array.from(props.technologySelectedIds ?? []),
});

function onUploadingChange(busy: boolean) {
    isUploading.value = busy;
}

const onImageClick = (image: any, index: number) => {};

function submit() {
    if (isUploading.value) {
        return;
    }

    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(route('certifications.update', { certification: props.certification.id }), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Certification Create" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-4">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid items-start gap-6 md:grid-cols-3">
                    <div class="flex flex-col">
                        <Label for="name" class="mb-1 text-gray-900 dark:text-gray-100">Name</Label>
                        <Input
                            v-model="form.name"
                            required
                            id="name"
                            type="text"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <Label for="issuing_organization" class="mb-1 text-gray-900 dark:text-gray-100">Issuing Organization</Label>
                        <Input
                            v-model="form.issuing_organization"
                            required
                            id="issuing_organization"
                            type="text"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.issuing_organization" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.issuing_organization }}
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <Label class="mb-1 text-gray-900 dark:text-gray-100">Issue date</Label>
                        <DatePicker v-model="form.issue_date" />
                        <div v-if="form.errors.issue_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.issue_date }}
                        </div>
                    </div>

                    <!-- Expiration date -->
                    <div class="flex flex-col">
                        <Label class="mb-1 text-gray-900 dark:text-gray-100">Expiration date</Label>
                        <DatePicker v-model="form.expiration_date" />
                        <div v-if="form.errors.expiration_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.expiration_date }}
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <Label for="credential_id" class="mb-1 text-gray-900 dark:text-gray-100">Credential ID</Label>
                        <Input
                            v-model="form.credential_id"
                            required
                            id="credential_id"
                            type="text"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.credential_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.credential_id }}
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <Label for="credential_url" class="mb-1 text-gray-900 dark:text-gray-100">Credential URL</Label>
                        <Input
                            v-model="form.credential_url"
                            required
                            id="credential_url"
                            type="url"
                            class="w-full rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400"
                        />
                        <div v-if="form.errors.credential_url" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.credential_url }}
                        </div>
                    </div>
                    <FileUploader
                        v-model="form.certificationImage"
                        :existing-files="existingImage"
                        title="Certification Image"
                        field-name="images"
                        :max-files="1"
                        max-file-size="2MB"
                        :allow-multiple="false"
                        :allow-reorder="false"
                        :show-featured="false"
                        :show-file-count="true"
                        accepted-file-types="image/jpeg, image/png, image/gif, image/webp"
                        file-type-description="images"
                        file-singular="image"
                        file-plural="images"
                        label-idle='Drag & Drop your image or <span class="filepond--label-action">Browse</span>'
                        :image-preview-height="150"
                        :image-resize-width="800"
                        :image-resize-height="600"
                        image-resize-mode="contain"
                        @file-click="onImageClick"
                        @uploading-change="onUploadingChange"
                    >
                        <!-- Custom image grid preview -->
                        <template #file-preview="{ files, reorderMode, removeFile, setFeatured, onDragStart, onDragOver, onDrop, showFeatured }">
                            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6" :class="{ 'cursor-move': reorderMode }">
                                <div
                                    v-for="(image, index) in files"
                                    :key="image.id || index"
                                    class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 transition-transform duration-200 dark:bg-gray-700"
                                    :class="{
                                        'hover:scale-105': !reorderMode,
                                        'cursor-move': reorderMode,
                                        'ring-2 ring-indigo-500': reorderMode,
                                        'ring-2 ring-yellow-400': showFeatured && index === 0,
                                    }"
                                    @click="reorderMode ? null : onImageClick(image, index)"
                                    draggable="true"
                                    @dragstart="onDragStart($event, index)"
                                    @dragover="onDragOver"
                                    @drop="onDrop($event, index)"
                                >
                                    <!-- Image -->
                                    <img :src="image.url" :alt="image.name" class="h-full w-full object-cover" />

                                    <!-- Featured badge -->
                                    <div
                                        v-if="showFeatured && index === 0"
                                        class="absolute top-2 left-2 flex items-center space-x-1 rounded-full bg-yellow-400 px-2 py-1 text-xs font-medium text-yellow-900"
                                    >
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                            />
                                        </svg>
                                        <span>Featured</span>
                                    </div>

                                    <!-- Reorder indicator -->
                                    <div
                                        v-if="reorderMode"
                                        class="absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-500 text-xs font-bold text-white"
                                    >
                                        {{ index + 1 }}
                                    </div>

                                    <!-- Action buttons -->
                                    <div
                                        v-if="!reorderMode"
                                        class="absolute top-2 right-2 flex space-x-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                                    >
                                        <!-- Set featured button -->
                                        <button
                                            v-if="showFeatured && index !== 0"
                                            @click.stop="setFeatured(index)"
                                            class="rounded-full bg-yellow-500 p-1 text-white transition-colors duration-200 hover:bg-yellow-600"
                                            title="Set as featured"
                                        >
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                                />
                                            </svg>
                                        </button>

                                        <!-- Remove button -->
                                        <button
                                            @click.stop="removeFile(index)"
                                            class="rounded-full bg-red-500 p-1 text-white transition-colors duration-200 hover:bg-red-600"
                                            title="Remove image"
                                        >
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- File name -->
                                    <div class="absolute right-0 bottom-0 left-0 truncate bg-black/60 p-2 text-xs text-white">
                                        {{ image.name }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </FileUploader>
                    <div v-if="form.errors.images" class="rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-400">{{ form.errors.images }}</p>
                    </div>

                    <div>
                        <Label class="mb-1 text-gray-900 dark:text-gray-100">Technologies</Label>
                        <TechnologyMultiSelect v-model="form.technology_ids" :options="props.technologiesAll" placeholder="Search technologies…" />
                        <div v-if="form.errors.technology_ids" class="mt-1 text-sm text-red-600">
                            {{ form.errors.technology_ids }}
                        </div>
                    </div>
                </div>
                <Button type="submit" class="mt-2" :disabled="form.processing">Save</Button>
            </form>
        </div>
    </AppLayout>
</template>
