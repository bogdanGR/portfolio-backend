<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'

type Props = {
    modelValue?: string
    placeholder?: string
    editable?: boolean
    minHeight?: string
}
const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    placeholder: 'Write something…',
    editable: true,
    minHeight: '12rem',
})
const emit = defineEmits<{ (e: 'update:modelValue', v: string): void }>()

const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    extensions: [
        StarterKit.configure({ heading: { levels: [1, 2, 3, 4] } }),
        Placeholder.configure({ placeholder: props.placeholder }),
        Link.configure({
            /* avoid navigating away when clicking links in the editor */
            openOnClick: false,
            autolink: true,
            linkOnPaste: true,
            protocols: ['http', 'https', 'mailto', 'tel'],
        }),
        Image,
    ],
    onUpdate: ({ editor }) => emit('update:modelValue', editor.getHTML()),
})

watch(() => props.modelValue, (val) => {
    const ed = editor.value; if (!ed) return
    const current = ed.getHTML()
    if (val !== current) ed.commands.setContent(val ?? '', false)
})
watch(() => props.editable, (val) => editor.value?.setEditable(!!val))
onBeforeUnmount(() => editor.value?.destroy())

const canUndo = computed(() => !!editor.value?.can().chain().focus().undo().run())
const canRedo = computed(() => !!editor.value?.can().chain().focus().redo().run())

function promptForLink() {
    const ed = editor.value; if (!ed) return
    const previous = ed.getAttributes('link').href as string | undefined
    const url = window.prompt('URL', previous ?? 'https://')
    if (url === null) return
    if (url === '') { ed.chain().focus().unsetLink().run(); return }
    ed.chain().focus().setLink({ href: url }).run()
}
function addImage() {
    const ed = editor.value; if (!ed) return
    const url = window.prompt('Image URL', 'https://')
    if (!url) return
    ed.chain().focus().setImage({ src: url }).run()
}
</script>

<template>
    <div class="rte-card">
        <!-- Toolbar (buttons must NOT submit the form; keep focus on editor) -->
        <div class="rte-toolbar">
            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('bold') }"
                    @click="editor?.chain().focus().toggleBold().run()">
                <span class="font-bold">B</span>
            </button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('italic') }"
                    @click="editor?.chain().focus().toggleItalic().run()">
                <span class="italic">I</span>
            </button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('strike') }"
                    @click="editor?.chain().focus().toggleStrike().run()">
                <span class="line-through">S</span>
            </button>

            <span class="rte-divider"></span>

            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('heading', { level: 2 }) }"
                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()">H2</button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('heading', { level: 3 }) }"
                    @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()">H3</button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('bulletList') }"
                    @click="editor?.chain().focus().toggleBulletList().run()">• List</button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('orderedList') }"
                    @click="editor?.chain().focus().toggleOrderedList().run()">1. List</button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('blockquote') }"
                    @click="editor?.chain().focus().toggleBlockquote().run()">❝ Quote</button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    @click="editor?.chain().focus().setHorizontalRule().run()">— HR</button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    @click="editor?.chain().focus().setHardBreak().run()">↵ BR</button>

            <span class="rte-divider"></span>

            <button type="button" @mousedown.prevent class="rte-btn"
                    :class="{ 'is-active': editor?.isActive('link') }"
                    @click="promptForLink()">Link</button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    @click="addImage()">Image</button>

            <span class="rte-divider"></span>

            <button type="button" @mousedown.prevent class="rte-btn"
                    :disabled="!canUndo" @click="editor?.chain().focus().undo().run()">Undo</button>
            <button type="button" @mousedown.prevent class="rte-btn"
                    :disabled="!canRedo" @click="editor?.chain().focus().redo().run()">Redo</button>
        </div>

        <!-- Whole area clickable/writable -->
        <div class="rte-surface" :style="{ minHeight: props.minHeight }"
             @click="editor?.chain().focus().run()">
            <EditorContent :editor="editor" class="outline-none" />
        </div>
    </div>
</template>



<style>
/* Global styles for TipTap editor content */
.ProseMirror {
    outline: none;
    color: rgb(17 24 39);
}

.dark .ProseMirror {
    color: rgb(243 244 246);
}

.ProseMirror h1 {
    font-size: 1.875rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.ProseMirror h2 {
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.3;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}

.ProseMirror h3 {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.4;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.ProseMirror h4 {
    font-size: 1.125rem;
    font-weight: 600;
    line-height: 1.4;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.ProseMirror p {
    margin-bottom: 0.75rem;
    line-height: 1.6;
}

.ProseMirror ul,
.ProseMirror ol {
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}

.ProseMirror ul {
    list-style-type: disc;
}

.ProseMirror ol {
    list-style-type: decimal;
}

.ProseMirror li {
    margin-bottom: 0.25rem;
    line-height: 1.5;
}

.ProseMirror blockquote {
    border-left: 4px solid rgb(209 213 219);
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: rgb(107 114 128);
}

.dark .ProseMirror blockquote {
    border-left-color: rgb(75 85 99);
    color: rgb(156 163 175);
}

.ProseMirror hr {
    border: none;
    border-top: 2px solid rgb(229 231 235);
    margin: 1.5rem 0;
}

.dark .ProseMirror hr {
    border-top-color: rgb(75 85 99);
}

.ProseMirror a {
    color: rgb(59 130 246);
    text-decoration: underline;
}

.dark .ProseMirror a {
    color: rgb(96 165 250);
}

.ProseMirror a:hover {
    color: rgb(37 99 235);
}

.dark .ProseMirror a:hover {
    color: rgb(59 130 246);
}

.ProseMirror img {
    max-width: 100%;
    height: auto;
    border-radius: 0.375rem;
    margin: 0.5rem 0;
}

.ProseMirror code {
    background-color: rgb(243 244 246);
    color: rgb(220 38 127);
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace;
}

.dark .ProseMirror code {
    background-color: rgb(55 65 81);
    color: rgb(251 113 133);
}

.ProseMirror pre {
    background-color: rgb(17 24 39);
    color: rgb(229 231 235);
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1rem 0;
}

.ProseMirror pre code {
    background: none;
    color: inherit;
    padding: 0;
    border-radius: 0;
    font-size: 0.875rem;
}

/* Placeholder styling */
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: rgb(156 163 175);
    pointer-events: none;
    height: 0;
}

.dark .ProseMirror p.is-editor-empty:first-child::before {
    color: rgb(107 114 128);
}

/* Focus styles */
.ProseMirror:focus {
    outline: none;
}

/* Selection styles */
.ProseMirror ::selection {
    background-color: rgb(219 234 254);
}

.dark .ProseMirror ::selection {
    background-color: rgb(30 58 138);
}
</style>
