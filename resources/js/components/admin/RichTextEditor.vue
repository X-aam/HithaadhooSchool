<script setup lang="ts">
import {
    Bold,
    Heading2,
    Heading3,
    ImagePlus,
    Italic,
    Link2,
    List,
    ListOrdered,
    Quote,
    Redo2,
    Undo2,
} from '@lucide/vue';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, ref, watch } from 'vue';
import { uploadImage } from '@/lib/uploadImage';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        dir?: 'ltr' | 'rtl';
        placeholder?: string;
    }>(),
    { dir: 'ltr', placeholder: 'Write here…' },
);

const emit = defineEmits<{ 'update:modelValue': [string] }>();

// Image node extended with a width attribute so inline images can be resized.
const ResizableImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: null,
                parseHTML: (element) => element.style.width || element.getAttribute('width') || null,
                renderHTML: (attributes) => (attributes.width ? { style: `width: ${attributes.width}` } : {}),
            },
        };
    },
});

const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);

const editor = useEditor({
    content: props.modelValue || '',
    extensions: [
        StarterKit.configure({ link: false }),
        Link.configure({ openOnClick: false, autolink: true }),
        ResizableImage,
        Placeholder.configure({ placeholder: props.placeholder }),
    ],
    editorProps: {
        attributes: {
            dir: props.dir,
            class: [
                'prose prose-neutral dark:prose-invert max-w-none min-h-[18rem] px-4 py-3 focus:outline-none',
                props.dir === 'rtl' ? 'font-thaana' : '',
            ].join(' '),
        },
    },
    onUpdate: ({ editor }) => emit('update:modelValue', editor.getHTML()),
});

// Keep the editor in sync when the bound value changes externally (e.g. loading
// a different article, or resetting the form).
watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.getHTML()) {
            editor.value.commands.setContent(value || '', { emitUpdate: false });
        }
    },
);

onBeforeUnmount(() => editor.value?.destroy());

function toggleLink() {
    if (!editor.value) {
return;
}

    const previous = editor.value.getAttributes('link').href as string | undefined;
    const url = window.prompt('Link URL', previous ?? 'https://');

    if (url === null) {
return;
}

    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();

        return;
    }

    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
}

function pickImage() {
    fileInput.value?.click();
}

function setImageWidth(width: string) {
    editor.value?.chain().focus().updateAttributes('image', { width }).run();
}

async function onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file || !editor.value) {
return;
}

    uploading.value = true;

    try {
        const url = await uploadImage(file);
        editor.value.chain().focus().setImage({ src: url }).run();
    } catch (e) {
        window.alert('Image upload failed. Please try a smaller image (max 5 MB).');
    } finally {
        uploading.value = false;
    }
}

const btn = 'inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition hover:bg-muted';
const btnActive = 'bg-brand-muted text-brand';
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-border bg-background">
        <div v-if="editor" class="flex flex-wrap items-center gap-0.5 border-b border-border bg-muted/30 p-1.5">
            <button type="button" :class="[btn, editor.isActive('bold') ? btnActive : '']" title="Bold" @click="editor.chain().focus().toggleBold().run()">
                <Bold class="size-4" />
            </button>
            <button type="button" :class="[btn, editor.isActive('italic') ? btnActive : '']" title="Italic" @click="editor.chain().focus().toggleItalic().run()">
                <Italic class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border" />
            <button type="button" :class="[btn, editor.isActive('heading', { level: 2 }) ? btnActive : '']" title="Heading" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()">
                <Heading2 class="size-4" />
            </button>
            <button type="button" :class="[btn, editor.isActive('heading', { level: 3 }) ? btnActive : '']" title="Subheading" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()">
                <Heading3 class="size-4" />
            </button>
            <button type="button" :class="[btn, editor.isActive('bulletList') ? btnActive : '']" title="Bullet list" @click="editor.chain().focus().toggleBulletList().run()">
                <List class="size-4" />
            </button>
            <button type="button" :class="[btn, editor.isActive('orderedList') ? btnActive : '']" title="Numbered list" @click="editor.chain().focus().toggleOrderedList().run()">
                <ListOrdered class="size-4" />
            </button>
            <button type="button" :class="[btn, editor.isActive('blockquote') ? btnActive : '']" title="Quote" @click="editor.chain().focus().toggleBlockquote().run()">
                <Quote class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border" />
            <button type="button" :class="[btn, editor.isActive('link') ? btnActive : '']" title="Link" @click="toggleLink">
                <Link2 class="size-4" />
            </button>
            <button type="button" :class="btn" title="Insert image" :disabled="uploading" @click="pickImage">
                <ImagePlus class="size-4" :class="uploading ? 'animate-pulse' : ''" />
            </button>

            <template v-if="editor.isActive('image')">
                <span class="mx-1 h-5 w-px bg-border" />
                <span class="px-1 text-xs font-medium text-muted-foreground">Size</span>
                <button type="button" :class="btn" title="Small" @click="setImageWidth('25%')">
                    <span class="text-xs font-semibold">S</span>
                </button>
                <button type="button" :class="btn" title="Medium" @click="setImageWidth('50%')">
                    <span class="text-xs font-semibold">M</span>
                </button>
                <button type="button" :class="btn" title="Large" @click="setImageWidth('75%')">
                    <span class="text-xs font-semibold">L</span>
                </button>
                <button type="button" :class="btn" title="Full width" @click="setImageWidth('100%')">
                    <span class="text-xs font-semibold">Full</span>
                </button>
            </template>

            <span class="mx-1 h-5 w-px bg-border" />
            <button type="button" :class="btn" title="Undo" @click="editor.chain().focus().undo().run()">
                <Undo2 class="size-4" />
            </button>
            <button type="button" :class="btn" title="Redo" @click="editor.chain().focus().redo().run()">
                <Redo2 class="size-4" />
            </button>
        </div>

        <EditorContent :editor="editor" />
        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileSelected" />
    </div>
</template>

<style scoped>
:deep(.ProseMirror p.is-editor-empty:first-child::before) {
    content: attr(data-placeholder);
    color: var(--muted-foreground);
    float: left;
    height: 0;
    pointer-events: none;
}

:deep(.ProseMirror img) {
    border-radius: 0.5rem;
    display: block;
    margin: 1rem auto;
    max-width: 100%;
    height: auto;
}

:deep(.ProseMirror img.ProseMirror-selectednode) {
    outline: 2px solid var(--brand);
    outline-offset: 2px;
}

:deep(.ProseMirror-focused) {
    outline: none;
}
</style>
