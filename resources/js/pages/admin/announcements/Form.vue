<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Paperclip, Save, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import RichTextEditor from '@/components/admin/RichTextEditor.vue';
import { uploadFile } from '@/lib/uploadImage';

interface Bilingual {
    en: string;
    dv: string;
}
interface Attachment {
    name: string;
    url: string;
    size: string;
    extension: string;
}
interface Announcement {
    id: number;
    category: string;
    pinned: boolean;
    is_published: boolean;
    published_at: string | null;
    title: Bilingual;
    body: Bilingual;
    attachments: Attachment[];
}

const props = defineProps<{ announcement: Announcement | null }>();

const isEdit = computed(() => props.announcement !== null);

const form = useForm({
    category: props.announcement?.category ?? 'general',
    pinned: props.announcement?.pinned ?? false,
    is_published: props.announcement?.is_published ?? true,
    published_at:
        props.announcement?.published_at ??
        new Date().toISOString().slice(0, 10),
    title: {
        en: props.announcement?.title?.en ?? '',
        dv: props.announcement?.title?.dv ?? '',
    },
    body: {
        en: props.announcement?.body?.en ?? '',
        dv: props.announcement?.body?.dv ?? '',
    },
    attachments: (props.announcement?.attachments ?? []) as Attachment[],
});

const uploading = ref(false);
const uploadError = ref('');
const picker = ref<HTMLInputElement | null>(null);

/** Attach a file. Reuses the same upload endpoint as the Downloads editor. */
async function onFilePicked(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file) {
        return;
    }

    uploading.value = true;
    uploadError.value = '';

    try {
        const info = await uploadFile(file);

        form.attachments = [
            ...form.attachments,
            {
                name: file.name,
                url: info.url,
                size: info.size,
                extension: info.extension,
            },
        ];
    } catch {
        uploadError.value =
            'Upload failed. Allowed: PDF, Word, Excel, PowerPoint, CSV, TXT, ZIP or an image, up to 20 MB.';
    } finally {
        uploading.value = false;
    }
}

function removeAttachment(index: number) {
    form.attachments = form.attachments.filter((_, i) => i !== index);
}

function submit() {
    if (isEdit.value) {
        form.put(`/admin/announcements/${props.announcement!.id}`);
    } else {
        form.post('/admin/announcements');
    }
}
</script>

<template>
    <Head
        :title="isEdit ? 'Edit announcement — CMS' : 'New announcement — CMS'"
    />

    <Link
        href="/admin/announcements"
        class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition hover:text-brand"
    >
        <ArrowLeft class="size-4" /> Back to announcements
    </Link>

    <h1 class="mt-4 mb-6 text-2xl font-bold tracking-tight">
        {{ isEdit ? 'Edit announcement' : 'New announcement' }}
    </h1>

    <form class="space-y-8" @submit.prevent="submit">
        <!-- Meta -->
        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium"
                        >Category</label
                    >
                    <select
                        v-model="form.category"
                        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 focus:outline-none"
                    >
                        <option value="academic">Academic</option>
                        <option value="events">Events</option>
                        <option value="emergency">Emergency</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Date</label>
                    <input
                        v-model="form.published_at"
                        type="date"
                        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 focus:outline-none"
                    />
                    <p
                        v-if="form.errors.published_at"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.published_at }}
                    </p>
                </div>
                <label class="flex items-center gap-2.5">
                    <input
                        v-model="form.pinned"
                        type="checkbox"
                        class="size-4 rounded border-border text-brand focus:ring-brand/30"
                    />
                    <span class="text-sm font-medium">Pin to top</span>
                </label>
                <label class="flex items-center gap-2.5">
                    <input
                        v-model="form.is_published"
                        type="checkbox"
                        class="size-4 rounded border-border text-brand focus:ring-brand/30"
                    />
                    <span class="text-sm font-medium">Published</span>
                </label>
            </div>
        </section>

        <!-- English -->
        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                English
            </h2>
            <div class="space-y-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium"
                        >Title</label
                    >
                    <input
                        v-model="form.title.en"
                        type="text"
                        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 focus:outline-none"
                    />
                    <p
                        v-if="form.errors['title.en']"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors['title.en'] }}
                    </p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Body</label>
                    <RichTextEditor
                        v-model="form.body.en"
                        dir="ltr"
                        placeholder="Write the announcement…"
                    />
                    <p
                        v-if="form.errors['body.en']"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors['body.en'] }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Dhivehi -->
        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                ދިވެހި (Dhivehi)
            </h2>
            <div class="space-y-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium"
                        >Title</label
                    >
                    <input
                        v-model="form.title.dv"
                        type="text"
                        dir="rtl"
                        class="w-full rounded-lg border border-border bg-background px-3 py-2 font-thaana text-sm focus:border-brand focus:ring-2 focus:ring-brand/30 focus:outline-none"
                    />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Body</label>
                    <RichTextEditor
                        v-model="form.body.dv"
                        dir="rtl"
                        placeholder="އިޢުލާން ލިޔުއްވާ…"
                    />
                </div>
            </div>
        </section>

        <!-- Attachments -->
        <section
            class="rounded-2xl border border-border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Attachments
            </h2>
            <p class="mb-4 text-xs text-muted-foreground">
                Circulars, forms or photos that belong with this announcement.
                Readers download them from the announcement page.
            </p>

            <p
                v-if="uploadError"
                class="mb-3 rounded-lg bg-destructive/10 px-3 py-2 text-sm font-medium text-destructive"
            >
                {{ uploadError }}
            </p>

            <ul v-if="form.attachments.length" class="mb-3 space-y-1.5">
                <li
                    v-for="(file, i) in form.attachments"
                    :key="i"
                    class="flex items-center gap-2 rounded-lg border border-border px-3 py-2"
                >
                    <Paperclip class="size-4 shrink-0 text-muted-foreground" />
                    <input
                        v-model="file.name"
                        type="text"
                        aria-label="Attachment name"
                        class="min-w-0 flex-1 rounded-md border border-transparent bg-transparent px-1.5 py-1 text-sm transition outline-none hover:border-border focus:border-brand"
                    />
                    <span class="shrink-0 text-xs text-muted-foreground"
                        >{{ file.extension.toUpperCase() }} ·
                        {{ file.size }}</span
                    >
                    <a
                        :href="file.url"
                        target="_blank"
                        rel="noopener"
                        class="shrink-0 rounded-md p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        title="Open file"
                    >
                        <ExternalLink class="size-3.5" />
                    </a>
                    <button
                        type="button"
                        class="shrink-0 rounded-md p-1.5 text-muted-foreground transition hover:text-red-600"
                        title="Remove attachment"
                        @click="removeAttachment(i)"
                    >
                        <Trash2 class="size-3.5" />
                    </button>
                </li>
            </ul>

            <input
                ref="picker"
                type="file"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip,.jpg,.jpeg,.png,.webp,.gif"
                class="hidden"
                @change="onFilePicked"
            />
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-brand/50 px-3 py-1.5 text-sm font-semibold text-brand transition hover:bg-brand-muted disabled:opacity-60"
                :disabled="uploading"
                @click="picker?.click()"
            >
                <Paperclip class="size-3.5" />
                {{ uploading ? 'Uploading…' : 'Add attachment' }}
            </button>
        </section>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110 disabled:opacity-60"
            >
                <Save class="size-4" />
                {{ isEdit ? 'Save changes' : 'Create announcement' }}
            </button>
            <Link
                href="/admin/announcements"
                class="text-sm font-medium text-muted-foreground hover:text-foreground"
                >Cancel</Link
            >
        </div>
    </form>
</template>
