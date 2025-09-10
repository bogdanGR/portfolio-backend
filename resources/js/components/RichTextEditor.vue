<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'

type Props = {
    modelValue?: string,        // v-model (HTML)
    placeholder?: string,
    editable?: boolean
}
const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    placeholder: 'Write something…',
    editable: true,
})
const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()

// keep editor in sync with v-model
const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    extensions: [
        StarterKit.configure({
            heading: { levels: [1,2,3,4] },
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
        Link.configure({
            openOnClick: true,
            autolink: true,
            linkOnPaste: true,
            protocols: ['http', 'https', 'mailto', 'tel'],
        }),
        Image,
    ],
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    },
})

watch(() => props.modelValue, (val) => {
    if (!editor) return
    // avoid infinite loop – only set if content actually changed
    const current = editor.getHTML()
    if (val !== current) editor.commands.setContent(val ?? '', false)
})

watch(() => props.editable, (val) => {
    editor?.setEditable(!!val)
})

onBeforeUnmount(() => editor?.destroy())

const canUndo = computed(() => editor?.can().chain().focus().undo().run())
const canRedo = computed(() => editor?.can().chain().focus().redo().run())

function promptForLink() {
    const previous = editor?.getAttributes('link').href as string | undefined
    const url = window.prompt('URL', previous ?? 'https://')
    if (url === null) return
    if (url === '') {
        editor?.chain().focus().unsetLink().run()
        return
    }
    editor?.chain().focus().setLink({ href: url }).run()
}

function addImage() {
    const url = window.prompt('Image URL', 'https://')
    if (!url) return
    editor?.chain().focus().setImage({ src: url }).run()
}
</script>

<template>
    <div class="rounded-xl border bg-white shadow-sm">
        <!-- Toolbar -->
        <div class="flex flex-wrap gap-1 border-b p-2">
            <button class="btn" :class="{ 'is-active': editor?.isActive('bold') }"
                    @click="editor?.chain().focus().toggleBold().run()">B</button>
            <button class="btn" :class="{ 'is-active': editor?.isActive('italic') }"
                    @click="editor?.chain().focus().toggleItalic().run()">I</button>
            <button class="btn" :class="{ 'is-active': editor?.isActive('strike') }"
                    @click="editor?.chain().focus().toggleStrike().run()">S</button>

            <span class="w-px bg-zinc-200 mx-1"></span>

            <button class="btn" :class="{ 'is-active': editor?.isActive('heading', { level: 2 }) }"
                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()">H2</button>
            <button class="btn" :class="{ 'is-active': editor?.isActive('heading', { level: 3 }) }"
                    @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()">H3</button>
            <button class="btn" @click="editor?.chain().focus().toggleBulletList().run()">• List</button>
            <button class="btn" @click="editor?.chain().focus().toggleOrderedList().run()">1. List</button>
            <button class="btn" @click="editor?.chain().focus().toggleBlockquote().run()">❝ Quote</button>
            <button class="btn" @click="editor?.chain().focus().setHorizontalRule().run()">— HR</button>
            <button class="btn" @click="editor?.chain().focus().setHardBreak().run()">↵ BR</button>

            <span class="w-px bg-zinc-200 mx-1"></span>

            <button class="btn" @click="promptForLink()">Link</button>
            <button class="btn" @click="addImage()">Image</button>

            <span class="w-px bg-zinc-200 mx-1"></span>

            <button class="btn" :disabled="!canUndo" @click="editor?.chain().focus().undo().run()">Undo</button>
            <button class="btn" :disabled="!canRedo" @click="editor?.chain().focus().redo().run()">Redo</button>
        </div>

        <!-- Editor -->
        <div class="prose max-w-none prose-zinc p-3 min-h-[12rem]">
            <EditorContent :editor="editor" />
        </div>
    </div>
</template>

<style scoped>
/* Tiptap default cursor/selection look is fine; Tailwind Typography styles content */
.prose :deep(p.is-editor-empty:first-child::before) {
    color: #9ca3af; /* gray-400 */
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}
</style>
