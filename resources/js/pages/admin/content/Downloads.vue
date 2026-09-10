<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ExternalLink, Plus, Search, Trash2, Upload, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import ContentEditorShell from '@/components/admin/ContentEditorShell.vue';
import { clone } from '@/components/admin/schema';
import { downloads } from '@/lib/sampleData';
import { uploadFile } from '@/lib/uploadImage';

const props = defineProps<{ section: string; label: string; value: unknown }>();

interface DownloadRow {
    id: number;
    category: string;
    fileType: string;
    size: string;
    date: string;
    url?: string;
    title: { en?: string; dv?: string };
}

const form = useForm<{ value: DownloadRow[] }>({
    value:
        (props.value as DownloadRow[] | null) ??
        clone(downloads as unknown as DownloadRow[]),
});

const CATEGORIES = [
    { value: 'forms', label: 'Forms' },
    { value: 'policies', label: 'Policies' },
    { value: 'syllabi', label: 'Syllabi' },
    { value: 'newsletters', label: 'Newsletters' },
];

const FILE_TYPES = [
    { value: 'pdf', label: 'PDF' },
    { value: 'docx', label: 'Word (docx)' },
    { value: 'xlsx', label: 'Excel (xlsx)' },
    { value: 'pptx', label: 'PowerPoint (pptx)' },
    { value: 'zip', label: 'ZIP' },
];

const query = ref('');
const category = ref('');
const fileType = ref('');
const uploadingId = ref<number | null>(null);
const uploadError = ref('');

/**
 * Rows matching the filters. These are the same objects as in the form, so
 * editing a filtered row still writes straight through to what gets saved.
 */
const visible = computed(() => {
    const q = query.value.trim().toLowerCase();

    return form.value.filter((row) => {
        if (category.value && row.category !== category.value) {
            return false;
        }

        if (fileType.value && row.fileType !== fileType.value) {
            return false;
        }

        if (!q) {
            return true;
        }

        return (
            (row.title.en ?? '').toLowerCase().includes(q) ||
            (row.title.dv ?? '').includes(query.value.trim()) ||
            (row.url ?? '').toLowerCase().includes(q)
        );
    });
});

const filtering = computed(
    () =>
        Boolean(query.value.trim()) ||
        Boolean(category.value) ||
        Boolean(fileType.value),
);

/** Per-category totals for the filter chips. */
const counts = computed(() => {
    const tally: Record<string, number> = {};

    for (const row of form.value) {
        tally[row.category] = (tally[row.category] ?? 0) + 1;
    }

    return tally;
});

function nextId() {
    return (
        form.value.reduce((max, e) => Math.max(max, Number(e.id) || 0), 0) + 1
    );
}

function addRow() {
    form.value.unshift({
        id: nextId(),
        category: category.value || 'forms',
        fileType: 'pdf',
        size: '',
        date: new Date().toISOString().slice(0, 10),
        url: '',
        title: { en: '', dv: '' },
    });
}

function removeRow(row: DownloadRow) {
    if (
        !confirm(
            `Remove "${row.title.en || 'this file'}" from the downloads list?`,
        )
    ) {
        return;
    }

    // Splice by identity — a filtered row's position is not its form index.
    const index = form.value.indexOf(row);

    if (index !== -1) {
        form.value.splice(index, 1);
    }
}

function clearFilters() {
    query.value = '';
    category.value = '';
    fileType.value = '';
}

const pickers = ref<Record<number, HTMLInputElement | null>>({});

/**
 * Upload a document straight into the row, filling in the link, type and size.
 * Previously an editor had to upload in the file manager, copy the link and
 * paste it here, then type the size by hand.
 */
async function onFilePicked(row: DownloadRow, event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file) {
        return;
    }

    uploadingId.value = row.id;
    uploadError.value = '';

    try {
        const info = await uploadFile(file);

        row.url = info.url;
        row.size = info.size;

        if (FILE_TYPES.some((t) => t.value === info.extension)) {
            row.fileType = info.extension;
        }

        // Only name the row from the file when the editor hasn't titled it.
        if (!row.title.en?.trim()) {
            row.title.en = info.name
                .replace(/-/g, ' ')
                .replace(/\b\w/g, (c) => c.toUpperCase());
        }
    } catch {
        uploadError.value =
            'Upload failed. Allowed: PDF, Word, Excel, PowerPoint, CSV, TXT, ZIP or an image, up to 20 MB.';
    } finally {
        uploadingId.value = null;
    }
}

function save() {
    form.put(`/admin/content/${props.section}`, { preserveScroll: true });
}

const field =
    'min-w-0 rounded-md border border-border bg-background px-2 py-1.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20';
</script>

<template>
    <ContentEditorShell
        :section="section"
        :label="label"
        :processing="form.processing"
        @save="save"
    >
        <!-- Filters -->
        <div
            class="flex flex-wrap items-center gap-2 rounded-xl border border-border bg-muted/30 p-3"
        >
            <div class="relative">
                <Search
                    class="pointer-events-none absolute start-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <input
                    v-model="query"
                    type="search"
                    placeholder="Search title or link…"
                    :class="[field, 'w-56 ps-8']"
                />
            </div>

            <select v-model="category" :class="[field, 'w-40']">
                <option value="">All categories</option>
                <option v-for="c in CATEGORIES" :key="c.value" :value="c.value">
                    {{ c.label }} ({{ counts[c.value] ?? 0 }})
                </option>
            </select>

            <select v-model="fileType" :class="[field, 'w-36']">
                <option value="">All types</option>
                <option v-for="t in FILE_TYPES" :key="t.value" :value="t.value">
                    {{ t.label }}
                </option>
            </select>

            <button
                v-if="filtering"
                type="button"
                class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-xs font-medium text-muted-foreground transition hover:text-foreground"
                @click="clearFilters"
            >
                <X class="size-3.5" /> Clear
            </button>

            <span class="text-xs text-muted-foreground">
                {{ visible.length }} of {{ form.value.length }}
            </span>

            <button
                type="button"
                class="ms-auto inline-flex items-center gap-1.5 rounded-full bg-brand px-4 py-2 text-sm font-semibold text-brand-foreground transition hover:brightness-110"
                @click="addRow"
            >
                <Plus class="size-4" /> Add file
            </button>
        </div>

        <p
            v-if="uploadError"
            class="rounded-xl bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive"
        >
            {{ uploadError }}
        </p>

        <!-- Rows -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-background"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-max border-collapse text-sm">
                    <thead>
                        <tr
                            class="border-b border-border bg-muted/40 text-xs text-muted-foreground"
                        >
                            <th class="px-2 py-2 text-left font-medium">
                                Title
                            </th>
                            <th class="px-2 py-2 text-right font-medium">
                                ދިވެހި
                            </th>
                            <th class="px-2 py-2 text-left font-medium">
                                Category
                            </th>
                            <th class="px-2 py-2 text-left font-medium">
                                Type
                            </th>
                            <th class="px-2 py-2 text-left font-medium">
                                Size
                            </th>
                            <th class="px-2 py-2 text-left font-medium">
                                Date
                            </th>
                            <th class="px-2 py-2 text-left font-medium">
                                File
                            </th>
                            <th class="w-px px-2 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in visible"
                            :key="row.id"
                            class="border-b border-border last:border-0 hover:bg-muted/30"
                        >
                            <td class="px-2 py-1.5">
                                <input
                                    v-model="row.title.en"
                                    type="text"
                                    placeholder="Admission Form 2026"
                                    aria-label="Title (English)"
                                    :class="[field, 'w-56']"
                                />
                            </td>
                            <td class="px-2 py-1.5">
                                <input
                                    v-model="row.title.dv"
                                    type="text"
                                    dir="rtl"
                                    placeholder="ފޯމު"
                                    aria-label="Title (Dhivehi)"
                                    :class="[field, 'w-44']"
                                />
                            </td>
                            <td class="px-2 py-1.5">
                                <select
                                    v-model="row.category"
                                    aria-label="Category"
                                    :class="[field, 'w-32']"
                                >
                                    <option
                                        v-for="c in CATEGORIES"
                                        :key="c.value"
                                        :value="c.value"
                                    >
                                        {{ c.label }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-2 py-1.5">
                                <select
                                    v-model="row.fileType"
                                    aria-label="File type"
                                    :class="[field, 'w-28']"
                                >
                                    <option
                                        v-for="t in FILE_TYPES"
                                        :key="t.value"
                                        :value="t.value"
                                    >
                                        {{ t.value.toUpperCase() }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-2 py-1.5">
                                <input
                                    v-model="row.size"
                                    type="text"
                                    placeholder="240 KB"
                                    aria-label="File size"
                                    :class="[field, 'w-20']"
                                />
                            </td>
                            <td class="px-2 py-1.5">
                                <input
                                    v-model="row.date"
                                    type="date"
                                    aria-label="Date"
                                    :class="[field, 'w-36']"
                                />
                            </td>
                            <td class="px-2 py-1.5">
                                <div class="flex items-center gap-1.5">
                                    <input
                                        :ref="
                                            (el) => {
                                                pickers[row.id] =
                                                    el as HTMLInputElement | null;
                                            }
                                        "
                                        type="file"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip,.jpg,.jpeg,.png,.webp,.gif"
                                        class="hidden"
                                        @change="onFilePicked(row, $event)"
                                    />
                                    <button
                                        type="button"
                                        class="inline-flex shrink-0 items-center gap-1.5 rounded-md border border-border px-2 py-1.5 text-xs font-medium transition hover:bg-muted disabled:opacity-60"
                                        :disabled="uploadingId === row.id"
                                        @click="pickers[row.id]?.click()"
                                    >
                                        <Upload class="size-3.5" />
                                        {{
                                            uploadingId === row.id
                                                ? 'Uploading…'
                                                : row.url
                                                  ? 'Replace'
                                                  : 'Upload'
                                        }}
                                    </button>
                                    <input
                                        v-model="row.url"
                                        type="text"
                                        placeholder="No file attached"
                                        aria-label="File link"
                                        :class="[
                                            field,
                                            'w-56 font-mono text-xs',
                                        ]"
                                    />
                                    <a
                                        v-if="row.url"
                                        :href="row.url"
                                        target="_blank"
                                        rel="noopener"
                                        class="shrink-0 rounded-md p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                        title="Open file"
                                    >
                                        <ExternalLink class="size-3.5" />
                                    </a>
                                </div>
                            </td>
                            <td class="px-2 py-1.5">
                                <button
                                    type="button"
                                    class="rounded-md p-1.5 text-muted-foreground transition hover:text-red-600"
                                    title="Remove"
                                    @click="removeRow(row)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!visible.length">
                            <td
                                colspan="8"
                                class="px-3 py-10 text-center text-sm text-muted-foreground"
                            >
                                {{
                                    filtering
                                        ? 'No files match these filters.'
                                        : 'No downloads yet. Use “Add file” to create one.'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </ContentEditorShell>
</template>
