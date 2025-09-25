<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';

// FilePond imports
import vueFilePond from 'vue-filepond';
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

// Import FilePond plugins
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';

// Create FilePond component
const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
    FilePondPluginImagePreview,
    FilePondPluginImageResize
);

// Types
interface ProcessedFile {
    id?: string;
    name: string;
    preview?: string;
    url?: string;
    file?: File;
    size?: number;
    type?: string;
    serverResponse?: any;
}

interface UploadProgress {
    show: boolean;
    current: number;
    total: number;
}

interface ExistingFile {
    id: number;
    name: string;
    url: string;
    original_name: string;
    size?: number;
    type?: string;
}

// Props
interface Props {
    title?: string;
    description?: string;
    fieldName?: string;
    maxFiles?: number;
    maxFileSize?: string;
    allowMultiple?: boolean;
    allowReorder?: boolean;
    showFeatured?: boolean;
    showFileCount?: boolean;
    acceptedFileTypes?: string;
    fileTypeDescription?: string;
    fileSingular?: string;
    filePlural?: string;
    labelIdle?: string;
    imagePreviewHeight?: number;
    imageResizeWidth?: number;
    imageResizeHeight?: number;
    imageResizeMode?: string;
    existingFiles?: ExistingFile[];
    modelValue?: File[];
}

const props = withDefaults(defineProps<Props>(), {
    title: '',
    description: '',
    fieldName: 'files',
    maxFiles: 10,
    maxFileSize: '5MB',
    allowMultiple: true,
    allowReorder: true,
    showFeatured: false,
    showFileCount: true,
    acceptedFileTypes: '*',
    fileTypeDescription: 'files',
    fileSingular: 'file',
    filePlural: 'files',
    labelIdle: 'Drag & Drop your files or <span class="filepond--label-action">Browse</span>',
    imagePreviewHeight: 150,
    imageResizeWidth: 800,
    imageResizeHeight: 600,
    imageResizeMode: 'contain',
    existingFiles: () => [],
    modelValue: () => []
});

// Emits
const emit = defineEmits<{
    'update:modelValue': [files: File[]];
    'file-click': [file: ProcessedFile, index: number];
    'files-changed': [files: ProcessedFile[]];
    'featured-changed': [index: number];
    'upload-complete': [files: ProcessedFile[]];
    'upload-error': [error: string];
}>();

// Refs
const pond = ref();
const uploadedFiles = ref([]);
const processedFiles = ref<ProcessedFile[]>([]);
const uploadError = ref('');
const uploadProgress = ref<UploadProgress>({
    show: false,
    current: 0,
    total: 0
});
const reorderMode = ref(false);
const draggedIndex = ref<number | null>(null);

// Get CSRF token
const csrfToken = computed(() => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    return token || '';
});

// File upload handler
const handleFileUpload = async (fieldName: string, file: File, metadata: any, load: Function, error: Function, progress: Function, abort: Function) => {
    uploadError.value = '';

    const uploadId = String(Date.now() + Math.random());

    // Create file object
    const newFile: ProcessedFile = {
        id: uploadId,
        name: file.name,
        file: file,
        size: file.size,
        type: file.type,
        preview: undefined
    };

    // Add to processed files
    processedFiles.value.push(newFile);

    // Wait for DOM update, then add preview
    await nextTick();

    // Add preview for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            // Find the file and update its preview
            const targetFile = processedFiles.value.find(f => f.id === uploadId);
            if (targetFile) {
                targetFile.preview = e.target?.result as string;
            }
        };
        reader.readAsDataURL(file);
    }

    // Update model value
    emit('update:modelValue', currentFilesForModel());
    syncModel();

    // Simulate server upload
    setTimeout(() => {
        load(uploadId);
    }, 1000);

    return {
        abort: () => {
            abort();
        }
    };
};

function currentFilesForModel(): File[] {
    return processedFiles.value
        .map(f => f.file)
        .filter((f): f is File => !!f);
}

function filesEqual(a?: File, b?: File) {
    return !!a && !!b &&
        a.name === b.name &&
        a.size === b.size &&
        a.type === b.type &&
        a.lastModified === b.lastModified;
}

function syncModel() {
    emit('update:modelValue', currentFilesForModel());
    emit('files-changed', processedFiles.value);
}

// File remove handler
const handleFileRemove = (source: any, load: Function, error: Function) => {
    const uploadId = String(source);

    const index = processedFiles.value.findIndex(file => String(file.id) === uploadId);
    if (index > -1) {
        const fileToRemove = processedFiles.value[index];

        if (fileToRemove.file) {
            emit('update:modelValue', currentFilesForModel());

        }

        processedFiles.value.splice(index, 1);
        syncModel();
        emit('files-changed', processedFiles.value);
    }

    load();
};

// Load existing file
const loadExistingFile = (source: any, load: Function, error: Function, progress: Function, abort: Function) => {
    load();
};

// Event handlers
const onFileProcessed = (error: any, file: any) => {
    if (error) {
        console.error('File processing error:', error);
        uploadError.value = 'Error processing file: ' + file.filename;
        emit('upload-error', uploadError.value);
    } else {
        emit('upload-complete', processedFiles.value);
    }
};

const onFileRemoved = (error: any, fileItem: any) => {
    if (error) {
        console.error('File removal error:', error);
        return;
    }
    // Try by serverId first (what you passed to load(uploadId))
    const serverId = (fileItem?.serverId ?? fileItem?.id ?? null)?.toString() ?? null;
    let idx = -1;

    if (serverId != null) {
        idx = processedFiles.value.findIndex(f => String(f.id) === serverId);
    }
    // Fallback: match by underlying File (in case serverId wasn't set)
    if (idx === -1 && fileItem?.file) {
        const raw = fileItem.file as File;
        idx = processedFiles.value.findIndex(f => filesEqual(f.file, raw));
    }
    if (idx > -1) {
        // remove without trying to remove from pond again (we're already in its callback)
        const f = processedFiles.value[idx];
        if (f?.preview?.startsWith('blob:')) URL.revokeObjectURL(f.preview);
        processedFiles.value.splice(idx, 1);
        syncModel();
    }
};


const onUploadError = (error: any) => {
    console.error('Upload error:', error);
    uploadError.value = 'Upload failed. Please try again.';
    emit('upload-error', uploadError.value);
};

const onFilePondInit = () => {
    if (props.existingFiles.length > 0) {
        processedFiles.value = props.existingFiles.map(file => ({
            id: file.id,
            name: file.original_name,
            url: file.url,
            size: file.size,
            type: file.type
        }));
    }
};

const removeProcessedFile = (index: number) => {
    const fileToRemove = processedFiles.value[index];

    if (fileToRemove.file) {
        emit('update:modelValue', currentFilesForModel());

    }

    processedFiles.value.splice(index, 1);

    if (pond.value && fileToRemove?.id != null) {
        try {
            pond.value.removeFile(String(fileToRemove.id));
        } catch {}
    }

    emit('files-changed', processedFiles.value);
    syncModel();
};

const setFeaturedFile = (index: number) => {
    const [featuredFile] = processedFiles.value.splice(index, 1);
    processedFiles.value.unshift(featuredFile);

    emit('featured-changed', 0);
    emit('files-changed', processedFiles.value);
};

// Reorder functionality
const toggleReorderMode = () => {
    reorderMode.value = !reorderMode.value;
};

const onDragStart = (event: DragEvent, index: number) => {
    if (!reorderMode.value) return;
    draggedIndex.value = index;
    event.dataTransfer!.effectAllowed = 'move';
};

const onDragOver = (event: DragEvent) => {
    if (!reorderMode.value) return;
    event.preventDefault();
    event.dataTransfer!.dropEffect = 'move';
};

const onDrop = (event: DragEvent, targetIndex: number) => {
    if (!reorderMode.value || draggedIndex.value === null) return;

    event.preventDefault();

    const draggedItem = processedFiles.value[draggedIndex.value];
    processedFiles.value.splice(draggedIndex.value, 1);
    processedFiles.value.splice(targetIndex, 0, draggedItem);

    draggedIndex.value = null;
    syncModel();
};

// Utility functions
const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const getFileIcon = (file: ProcessedFile) => {
    const type = file.type || '';

    if (type.startsWith('image/')) {
        return 'svg'; // Replace with actual image icon component
    } else if (type === 'application/pdf') {
        return 'svg'; // Replace with PDF icon component
    } else if (type.includes('document') || type.includes('word')) {
        return 'svg'; // Replace with document icon component
    } else if (type.includes('spreadsheet') || type.includes('excel')) {
        return 'svg'; // Replace with spreadsheet icon component
    } else {
        return 'svg'; // Replace with generic file icon component
    }
};

// Watch for changes in existing files
watch(() => props.existingFiles, (newFiles) => {
    if (newFiles.length > 0) {
        processedFiles.value = newFiles.map(file => ({
            id: file.id,
            name: file.original_name,
            url: file.url,
            size: file.size,
            type: file.type
        }));
    }
}, { immediate: true });

watch(processedFiles, () => {
    syncModel();
}, { deep: true });

// Expose methods
defineExpose({
    clearFiles: () => {
        processedFiles.value = [];
        uploadedFiles.value = [];
        if (pond.value) {
            pond.value.removeFiles();
        }
        syncModel();
    },
    getProcessedFiles: () => processedFiles.value,
    hasFiles: () => processedFiles.value.length > 0
});
</script>

<template>
    <div class="file-uploader">
        <div class="bg-white rounded-lg border border-gray-200 p-6 dark:bg-gray-800 dark:border-gray-700">
            <h2 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ title }}</h2>
            <p v-if="description" class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                {{ description }}
            </p>

            <!-- FilePond Upload Area -->
            <div class="space-y-4">
                <file-pond
                    :name="fieldName"
                    ref="pond"
                    class="filepond-custom"
                    :allow-multiple="allowMultiple"
                    :max-files="maxFiles"
                    :accepted-file-types="acceptedFileTypes"
                    :max-file-size="maxFileSize"
                    :image-preview-height="imagePreviewHeight"
                    :image-resize-target-width="imageResizeWidth"
                    :image-resize-target-height="imageResizeHeight"
                    :image-resize-mode="imageResizeMode"
                    :image-resize-upscale="false"
                    :files="uploadedFiles"
                    :label-idle="labelIdle"
                    :label-file-size-not-available="'File size not available'"
                    :label-file-count-singular="fileSingular + ' in list'"
                    :label-file-count-plural="filePlural + ' in list'"
                    :label-file-loading="'Loading...'"
                    :label-file-processing="'Uploading...'"
                    :label-file-processing-complete="'Upload complete'"
                    :label-file-processing-aborted="'Upload cancelled'"
                    :label-file-processing-reverted="'Upload undone'"
                    :label-file-remove="'Remove'"
                    :label-file-remove-error="'Error during remove'"
                    :label-file-type-not-allowed="'File of invalid type'"
                    :file-validate-type-label-expected-types="'Expects ' + fileTypeDescription"
                    :label-file-size-too-large="'File is too large'"
                    :label-file-size-too-small="'File is too small'"
                    :server="{
                        process: handleFileUpload,
                        revert: handleFileRemove,
                        load: loadExistingFile,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    }"
                    @processfile="onFileProcessed"
                    @removefile="onFileRemoved"
                    @error="onUploadError"
                    @init="onFilePondInit"
                />

                <!-- Error display -->
                <div v-if="uploadError" class="p-3 bg-red-50 border border-red-200 rounded-md dark:bg-red-900/20 dark:border-red-800">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-red-800 dark:text-red-400">{{ uploadError }}</p>
                        </div>
                    </div>
                </div>

                <!-- Upload progress -->
                <div v-if="uploadProgress.show" class="bg-blue-50 border border-blue-200 rounded-md p-4 dark:bg-blue-900/20 dark:border-blue-800">
                    <div class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-blue-800 dark:text-blue-400">
                            Uploading {{ uploadProgress.current }} of {{ uploadProgress.total }} {{ filePlural }}...
                        </span>
                    </div>
                </div>

                <!-- Files preview -->
                <div v-if="processedFiles.length > 0" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            Uploaded {{ filePlural }} ({{ processedFiles.length }})
                        </h3>
                        <button
                            type="button"
                            v-if="allowReorder && processedFiles.length > 1"
                            @click="toggleReorderMode"
                            class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            {{ reorderMode ? 'Done Reordering' : 'Reorder ' + filePlural }}
                        </button>
                    </div>

                    <slot
                        name="file-preview"
                        :files="processedFiles"
                        :reorder-mode="reorderMode"
                        :remove-file="removeProcessedFile"
                        :set-featured="setFeaturedFile"
                        :on-drag-start="onDragStart"
                        :on-drag-over="onDragOver"
                        :on-drop="onDrop"
                        :show-featured="showFeatured"
                    >
                        <!-- Default file list preview -->
                        <div class="space-y-2">
                            <div
                                v-for="(file, index) in processedFiles"
                                :key="file.id || index"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg dark:bg-gray-700 group hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
                                :class="{
                                    'cursor-move ring-2 ring-indigo-500': reorderMode,
                                    'ring-2 ring-yellow-400': showFeatured && index === 0
                                }"
                                @click="reorderMode ? null : $emit('file-click', file, index)"
                                draggable="true"
                                @dragstart="onDragStart($event, index)"
                                @dragover="onDragOver"
                                @drop="onDrop($event, index)"
                            >
                                <div class="flex items-center space-x-3">
                                    <!-- File icon based on type -->
                                    <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 rounded-lg dark:bg-indigo-900">
                                        <component :is="getFileIcon(file)" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ file.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatFileSize(file.size || 0) }}
                                            {{ showFeatured && index === 0 ? ' • Featured' : '' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <!-- Reorder number -->
                                    <span v-if="reorderMode" class="bg-indigo-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">
                                        {{ index + 1 }}
                                    </span>

                                    <!-- Action buttons -->
                                    <div v-if="!reorderMode" class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <!-- Set featured button -->
                                        <button
                                            type="button"
                                            v-if="showFeatured && index !== 0"
                                            @click.stop="setFeaturedFile(index)"
                                            class="p-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded transition-colors duration-200"
                                            title="Set as featured"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>

                                        <!-- Remove button -->
                                        <button
                                            type="button"
                                            @click.stop="removeProcessedFile(index)"
                                            class="p-1 bg-red-500 hover:bg-red-600 text-white rounded transition-colors duration-200"
                                            title="Remove file"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </slot>

                    <!-- File count info -->
                    <div v-if="showFileCount" class="text-sm text-gray-600 dark:text-gray-400">
                        {{ processedFiles.length }} of {{ maxFiles }} {{ filePlural }} uploaded
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<style scoped>
/* Custom FilePond styles */
.filepond-custom {
    font-family: inherit;
}

.filepond--root {
    margin-bottom: 0;
}

.filepond--drop-label {
    color: #374151;
    font-size: 16px;
}

.dark .filepond--drop-label {
    color: #d1d5db;
}

.filepond--label-action {
    color: #6366f1;
    text-decoration: underline;
}

.filepond--panel-root {
    background-color: #f9fafb;
    border: 2px dashed #d1d5db;
    border-radius: 0.5rem;
}

.dark .filepond--panel-root {
    background-color: #1f2937;
    border-color: #4b5563;
}

.filepond--panel-root:hover {
    border-color: #6366f1;
    background-color: #eef2ff;
}

.dark .filepond--panel-root:hover {
    border-color: #6366f1;
    background-color: #312e81;
}

.filepond--item-panel {
    background-color: white;
    border-radius: 0.375rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.dark .filepond--item-panel {
    background-color: #374151;
}

.filepond--file-status {
    color: #6b7280;
}

.dark .filepond--file-status {
    color: #9ca3af;
}

.filepond--file-info {
    color: #111827;
}

.dark .filepond--file-info {
    color: #f9fafb;
}

.filepond--action-remove-item {
    background-color: #ef4444;
}

.filepond--action-remove-item:hover {
    background-color: #dc2626;
}

.filepond--progress-indicator {
    background-color: #6366f1;
}
</style>
