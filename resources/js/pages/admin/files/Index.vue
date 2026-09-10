<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowDownAZ,
    ArrowUpDown,
    Check,
    ChevronRight,
    Copy,
    ExternalLink,
    File,
    FileArchive,
    FileAudio,
    FileCode,
    FileSpreadsheet,
    FileText,
    FileVideo,
    Folder,
    Globe,
    Info,
    LayoutGrid,
    Link2,
    List,
    MonitorCog,
    Presentation,
    RefreshCw,
    Trash2,
    Upload,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Crumb {
    name: string;
    path: string;
}

interface ManagedDirectory {
    path: string;
    name: string;
    label: string;
    count: number;
}

interface ManagedFile {
    path: string;
    name: string;
    extension: string;
    url: string;
    size: number;
    modified: string;
}

/** One place on the site that links to a file. */
interface FileUsage {
    label: string;
    context: string;
    editUrl: string | null;
}

const props = defineProps<{
    path: string;
    breadcrumbs: Crumb[];
    directories: ManagedDirectory[];
    files: ManagedFile[];
}>();

type SortKey = 'name' | 'modified' | 'size' | 'extension';

const SORT_LABELS: Record<SortKey, string> = {
    name: 'Name',
    modified: 'Date modified',
    size: 'Size',
    extension: 'Type',
};

const query = ref('');
const sortKey = ref<SortKey>('name');
const sortAsc = ref(true);
const view = ref<'grid' | 'list'>('grid');
const showInfo = ref(false);
const selected = ref<string | null>(null);
const copied = ref<string | null>(null);
const input = ref<HTMLInputElement | null>(null);

/** Usage lookups, keyed by file path. Fetched once per file, then reused. */
const usages = ref<Record<string, FileUsage[]>>({});
const usageLoading = ref<string | null>(null);
const usageFailed = ref<string | null>(null);

const form = useForm<{ file: File | null; path: string }>({
    file: null,
    path: props.path,
});

/** Drop the selection and retarget uploads whenever we land in a new folder. */
watch(
    () => props.path,
    (path) => {
        selected.value = null;
        form.path = path;
    },
);

const folderName = computed(
    () => props.breadcrumbs[props.breadcrumbs.length - 1]?.name ?? 'Files',
);

const visibleDirectories = computed(() => {
    const q = query.value.trim().toLowerCase();

    return q
        ? props.directories.filter((d) => d.label.toLowerCase().includes(q))
        : props.directories;
});

const visibleFiles = computed(() => {
    const q = query.value.trim().toLowerCase();
    const matched = q
        ? props.files.filter((f) => f.name.toLowerCase().includes(q))
        : [...props.files];
    const direction = sortAsc.value ? 1 : -1;

    return matched.sort((a, b) => {
        const key = sortKey.value;

        if (key === 'size') {
            return (a.size - b.size) * direction;
        }

        if (key === 'modified') {
            return (
                (Date.parse(a.modified) - Date.parse(b.modified)) * direction
            );
        }

        const compared = a[key].localeCompare(b[key], undefined, {
            numeric: true,
            sensitivity: 'base',
        });

        // Files sharing a type still read best in name order.
        return (
            (compared !== 0
                ? compared
                : a.name.localeCompare(b.name, undefined, { numeric: true })) *
            direction
        );
    });
});

const isEmpty = computed(
    () =>
        visibleDirectories.value.length === 0 &&
        visibleFiles.value.length === 0,
);

const selectedFile = computed(
    () => props.files.find((f) => f.path === selected.value) ?? null,
);

const selectedUsage = computed(() =>
    selected.value ? (usages.value[selected.value] ?? null) : null,
);

function open(path: string) {
    router.get('/admin/files', path ? { path } : {});
}

function refresh() {
    router.reload({ only: ['directories', 'files'] });
}

function sortBy(key: SortKey) {
    if (sortKey.value === key) {
        sortAsc.value = !sortAsc.value;

        return;
    }

    sortKey.value = key;
    sortAsc.value = true;
}

function select(file: ManagedFile) {
    selected.value = selected.value === file.path ? null : file.path;
    showInfo.value = selected.value !== null;

    if (selected.value) {
        void loadUsage(file.path);
    }
}

/** Ask the server which pages, articles and profiles link to this file. */
async function loadUsage(path: string) {
    if (usages.value[path] || usageLoading.value === path) {
        return;
    }

    usageLoading.value = path;
    usageFailed.value = null;

    try {
        const response = await fetch(
            `/admin/files/usage?path=${path.split('/').map(encodeURIComponent).join('/')}`,
            { credentials: 'same-origin', headers: { Accept: 'application/json' } },
        );

        if (!response.ok) {
            throw new Error(`Lookup failed (${response.status})`);
        }

        const data = (await response.json()) as { usages: FileUsage[] };

        usages.value = { ...usages.value, [path]: data.usages };
    } catch {
        usageFailed.value = path;
    } finally {
        if (usageLoading.value === path) {
            usageLoading.value = null;
        }
    }
}

function pickFile() {
    input.value?.click();
}

function onFileChosen(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    if (!file) {
        return;
    }

    form.file = file;
    form.post('/admin/files', {
        preserveScroll: true,
        onFinish: () => {
            form.reset('file');

            if (input.value) {
                input.value.value = '';
            }
        },
    });
}

/**
 * Copy the link exactly as the server gave it — root-relative for files on our
 * own disk. These links get pasted into site content, so an absolute URL would
 * pin that content to whichever host it was authored on.
 */
async function copyUrl(file: ManagedFile) {
    await navigator.clipboard.writeText(file.url);
    copied.value = file.path;
    setTimeout(() => {
        if (copied.value === file.path) {
            copied.value = null;
        }
    }, 2000);
}

async function destroy(file: ManagedFile) {
    // Check what links to the file first, so the warning can name the pages
    // that are about to break rather than warning in the abstract.
    await loadUsage(file.path);

    const linked = usages.value[file.path] ?? [];
    const warning = linked.length
        ? `"${file.name}" is linked from ${linked.length} place${linked.length === 1 ? '' : 's'}:\n\n` +
          linked.map((u) => `• ${u.label} (${u.context})`).join('\n') +
          '\n\nDeleting it will break those links. Continue?'
        : `Delete "${file.name}"? Links to it on the website will stop working.`;

    if (!confirm(warning)) {
        return;
    }

    // Encode each segment so the slashes stay real path separators in the URL.
    const path = file.path.split('/').map(encodeURIComponent).join('/');

    router.delete(`/admin/files/${path}`, {
        preserveScroll: true,
        onSuccess: () => {
            selected.value = null;
        },
    });
}

const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif'];

/**
 * Icon and accent colour per file type, so a folder full of similar names still
 * scans at a glance. Keys are lowercase extensions.
 */
const FILE_TYPES: Record<string, { icon: typeof File; class: string }> = {
    pdf: { icon: FileText, class: 'text-red-600' },
    doc: { icon: FileText, class: 'text-blue-600' },
    docx: { icon: FileText, class: 'text-blue-600' },
    rtf: { icon: FileText, class: 'text-blue-500' },
    txt: { icon: FileText, class: 'text-slate-500' },
    xml: { icon: FileCode, class: 'text-amber-600' },
    json: { icon: FileCode, class: 'text-amber-600' },
    xls: { icon: FileSpreadsheet, class: 'text-emerald-600' },
    xlsx: { icon: FileSpreadsheet, class: 'text-emerald-600' },
    csv: { icon: FileSpreadsheet, class: 'text-emerald-600' },
    ppt: { icon: Presentation, class: 'text-orange-600' },
    pptx: { icon: Presentation, class: 'text-orange-600' },
    zip: { icon: FileArchive, class: 'text-yellow-600' },
    rar: { icon: FileArchive, class: 'text-yellow-600' },
    '7z': { icon: FileArchive, class: 'text-yellow-600' },
    mp4: { icon: FileVideo, class: 'text-sky-600' },
    mov: { icon: FileVideo, class: 'text-sky-600' },
    webm: { icon: FileVideo, class: 'text-sky-600' },
    mp3: { icon: FileAudio, class: 'text-violet-600' },
    wav: { icon: FileAudio, class: 'text-violet-600' },
    html: { icon: Globe, class: 'text-orange-500' },
    htm: { icon: Globe, class: 'text-orange-500' },
    css: { icon: FileCode, class: 'text-blue-500' },
    js: { icon: FileCode, class: 'text-yellow-500' },
    ts: { icon: FileCode, class: 'text-blue-600' },
    php: { icon: FileCode, class: 'text-indigo-500' },
    exe: { icon: MonitorCog, class: 'text-slate-600' },
    msi: { icon: MonitorCog, class: 'text-slate-600' },
};

function isImage(file: ManagedFile) {
    return IMAGE_EXTENSIONS.includes(file.extension);
}

function typeFor(file: ManagedFile) {
    return (
        FILE_TYPES[file.extension] ?? { icon: File, class: 'text-slate-500' }
    );
}

function formatSize(bytes: number) {
    if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    if (bytes >= 1024) {
        return `${Math.round(bytes / 1024)} KB`;
    }

    return `${bytes} B`;
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Files — CMS" />

    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
    >
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Files</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Every file uploaded to the website, including images added
                through the page editors. Copy a file's link to use it anywhere
                on the site (e.g. the Downloads page).
            </p>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            <input
                ref="input"
                type="file"
                class="hidden"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip,.jpg,.jpeg,.png,.webp,.gif"
                @change="onFileChosen"
            />
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110 disabled:opacity-60"
                :disabled="form.processing"
                @click="pickFile"
            >
                <Upload class="size-4" />
                {{ form.processing ? 'Uploading…' : 'Upload file' }}
            </button>
        </div>
    </div>

    <p
        v-if="form.errors.file"
        class="mb-4 rounded-xl bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive"
    >
        {{ form.errors.file }}
    </p>

    <div
        class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm"
    >
        <div
            class="flex flex-wrap items-center gap-1 border-b border-border bg-muted/40 px-3 py-2"
        >
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-foreground/80 transition hover:bg-muted hover:text-foreground"
                    >
                        <ArrowUpDown class="size-4" />
                        Sort by
                        <span
                            class="hidden text-xs text-muted-foreground sm:inline"
                            >{{ SORT_LABELS[sortKey] }}</span
                        >
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-52">
                    <DropdownMenuLabel>Sort by</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        v-for="(label, key) in SORT_LABELS"
                        :key="key"
                        @click="sortBy(key)"
                    >
                        <Check v-if="sortKey === key" class="size-4" />
                        <span v-else class="size-4" />
                        {{ label }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="sortAsc = !sortAsc">
                        <ArrowDownAZ class="size-4" />
                        {{ sortAsc ? 'Ascending' : 'Descending' }}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-foreground/80 transition hover:bg-muted hover:text-foreground"
                @click="refresh"
            >
                <RefreshCw class="size-4" />
                Refresh
            </button>

            <div class="ml-auto flex items-center gap-1">
                <div class="flex items-center rounded-lg bg-muted p-0.5">
                    <button
                        type="button"
                        class="rounded-md p-1.5 transition"
                        :class="
                            view === 'grid'
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-foreground/60 hover:text-foreground'
                        "
                        title="Grid view"
                        @click="view = 'grid'"
                    >
                        <LayoutGrid class="size-4" />
                    </button>
                    <button
                        type="button"
                        class="rounded-md p-1.5 transition"
                        :class="
                            view === 'list'
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-foreground/60 hover:text-foreground'
                        "
                        title="Details view"
                        @click="view = 'list'"
                    >
                        <List class="size-4" />
                    </button>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-2 transition"
                    :class="
                        showInfo
                            ? 'bg-brand-muted text-brand'
                            : 'text-foreground/60 hover:bg-muted hover:text-foreground'
                    "
                    title="Details pane"
                    @click="showInfo = !showInfo"
                >
                    <Info class="size-4" />
                </button>
            </div>
        </div>

        <div
            class="flex flex-wrap items-center gap-3 border-b border-border px-4 py-2.5"
        >
            <nav
                class="flex min-w-0 items-center gap-1 text-sm"
                aria-label="Breadcrumb"
            >
                <template
                    v-for="(crumb, i) in props.breadcrumbs"
                    :key="crumb.path"
                >
                    <ChevronRight
                        v-if="i > 0"
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                    <button
                        v-if="i < props.breadcrumbs.length - 1"
                        type="button"
                        class="truncate rounded px-1.5 py-0.5 font-medium text-brand transition hover:bg-brand-muted"
                        @click="open(crumb.path)"
                    >
                        {{ crumb.name }}
                    </button>
                    <span v-else class="truncate px-1.5 py-0.5 font-semibold">{{
                        crumb.name
                    }}</span>
                </template>
            </nav>
            <input
                v-model="query"
                type="search"
                :placeholder="`Search ${folderName}`"
                class="ml-auto w-full rounded-full border border-border bg-background px-4 py-1.5 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 sm:w-64"
            />
        </div>

        <div class="flex">
            <div class="min-w-0 flex-1 p-4">
                <p
                    v-if="isEmpty"
                    class="py-16 text-center text-sm text-muted-foreground"
                >
                    {{
                        query
                            ? 'No files match your search.'
                            : 'This folder is empty. Use “Upload file” to add one.'
                    }}
                </p>

                <div
                    v-else-if="view === 'grid'"
                    class="grid grid-cols-[repeat(auto-fill,minmax(104px,1fr))] gap-1"
                >
                    <button
                        v-for="d in visibleDirectories"
                        :key="d.path"
                        type="button"
                        class="group flex flex-col items-center gap-2 rounded-xl border border-transparent px-2 py-3 text-center transition hover:border-brand/30 hover:bg-brand-muted/40"
                        :title="`${d.label} — ${d.count} item${d.count === 1 ? '' : 's'}`"
                        @click="open(d.path)"
                    >
                        <Folder
                            class="size-11 text-slate-400 transition group-hover:text-brand"
                            :stroke-width="1.25"
                        />
                        <span
                            class="line-clamp-2 w-full text-xs font-medium break-words"
                            >{{ d.label }}</span
                        >
                    </button>

                    <button
                        v-for="f in visibleFiles"
                        :key="f.path"
                        type="button"
                        class="group flex flex-col items-center gap-2 rounded-xl border px-2 py-3 text-center transition"
                        :class="
                            selected === f.path
                                ? 'border-brand/40 bg-brand-muted/60'
                                : 'border-transparent hover:border-brand/30 hover:bg-brand-muted/40'
                        "
                        :title="f.name"
                        @click="select(f)"
                    >
                        <img
                            v-if="isImage(f)"
                            :src="f.url"
                            :alt="f.name"
                            class="size-11 rounded-md border border-border object-cover"
                            loading="lazy"
                        />
                        <component
                            v-else
                            :is="typeFor(f).icon"
                            class="size-11"
                            :class="typeFor(f).class"
                            :stroke-width="1.25"
                        />
                        <span class="line-clamp-2 w-full text-xs break-words">{{
                            f.name
                        }}</span>
                    </button>
                </div>

                <div v-else class="grid gap-1.5">
                    <button
                        v-for="d in visibleDirectories"
                        :key="d.path"
                        type="button"
                        class="flex w-full items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-left transition hover:border-brand/30 hover:bg-brand-muted/40"
                        @click="open(d.path)"
                    >
                        <Folder
                            class="size-8 shrink-0 text-slate-400"
                            :stroke-width="1.25"
                        />
                        <span class="min-w-0 flex-1 truncate font-medium">{{
                            d.label
                        }}</span>
                        <span class="shrink-0 text-xs text-muted-foreground"
                            >{{ d.count }} item{{
                                d.count === 1 ? '' : 's'
                            }}</span
                        >
                    </button>

                    <div
                        v-for="f in visibleFiles"
                        :key="f.path"
                        class="flex items-center gap-3 rounded-xl border px-3 py-2.5 transition"
                        :class="
                            selected === f.path
                                ? 'border-brand/40 bg-brand-muted/60'
                                : 'border-transparent hover:bg-muted/60'
                        "
                        @click="select(f)"
                    >
                        <img
                            v-if="isImage(f)"
                            :src="f.url"
                            :alt="f.name"
                            class="size-8 shrink-0 rounded-md border border-border object-cover"
                            loading="lazy"
                        />
                        <component
                            v-else
                            :is="typeFor(f).icon"
                            class="size-8 shrink-0"
                            :class="typeFor(f).class"
                            :stroke-width="1.25"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">{{ f.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ f.extension.toUpperCase() }} ·
                                {{ formatSize(f.size) }} ·
                                {{ formatDate(f.modified) }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                class="rounded-lg p-2 text-foreground/70 transition hover:bg-muted hover:text-foreground"
                                :title="
                                    copied === f.path ? 'Copied!' : 'Copy link'
                                "
                                @click.stop="copyUrl(f)"
                            >
                                <component
                                    :is="copied === f.path ? Check : Copy"
                                    class="size-4"
                                    :class="
                                        copied === f.path
                                            ? 'text-emerald-600'
                                            : ''
                                    "
                                />
                            </button>
                            <a
                                :href="f.url"
                                target="_blank"
                                rel="noopener"
                                class="rounded-lg p-2 text-foreground/70 transition hover:bg-muted hover:text-foreground"
                                title="Open file"
                                @click.stop
                            >
                                <ExternalLink class="size-4" />
                            </a>
                            <button
                                type="button"
                                class="rounded-lg p-2 text-foreground/70 transition hover:bg-destructive/10 hover:text-destructive"
                                title="Delete file"
                                @click.stop="destroy(f)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <aside
                v-if="showInfo"
                class="hidden w-72 shrink-0 border-l border-border bg-muted/20 p-4 lg:block"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold">Details</h2>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-foreground/60 transition hover:bg-muted hover:text-foreground"
                        title="Close details"
                        @click="showInfo = false"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <p v-if="!selectedFile" class="text-sm text-muted-foreground">
                    Select a file to see its details.
                </p>

                <div v-else class="space-y-4">
                    <div
                        class="grid place-items-center rounded-xl border border-border bg-background p-4"
                    >
                        <img
                            v-if="isImage(selectedFile)"
                            :src="selectedFile.url"
                            :alt="selectedFile.name"
                            class="max-h-40 rounded-lg object-contain"
                        />
                        <component
                            v-else
                            :is="typeFor(selectedFile).icon"
                            class="size-16"
                            :class="typeFor(selectedFile).class"
                            :stroke-width="1"
                        />
                    </div>

                    <p class="text-sm font-semibold break-words">
                        {{ selectedFile.name }}
                    </p>

                    <dl class="space-y-1.5 text-xs">
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Type</dt>
                            <dd class="font-medium">
                                {{ selectedFile.extension.toUpperCase() }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Size</dt>
                            <dd class="font-medium">
                                {{ formatSize(selectedFile.size) }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Modified</dt>
                            <dd class="font-medium">
                                {{ formatDate(selectedFile.modified) }}
                            </dd>
                        </div>
                        <div class="pt-1">
                            <dt class="mb-1 text-muted-foreground">Path</dt>
                            <dd
                                class="rounded-lg bg-background px-2 py-1.5 font-mono break-all"
                            >
                                {{ selectedFile.url }}
                            </dd>
                        </div>
                    </dl>

                    <div class="border-t border-border pt-3">
                        <h3 class="mb-2 flex items-center gap-1.5 text-xs font-semibold">
                            <Link2 class="size-3.5 text-muted-foreground" />
                            Linked from
                        </h3>

                        <p
                            v-if="usageLoading === selectedFile.path"
                            class="text-xs text-muted-foreground"
                        >
                            Checking the site…
                        </p>
                        <p
                            v-else-if="usageFailed === selectedFile.path"
                            class="text-xs text-destructive"
                        >
                            Could not check where this file is used.
                            <button
                                type="button"
                                class="font-semibold underline"
                                @click="loadUsage(selectedFile.path)"
                            >
                                Retry
                            </button>
                        </p>
                        <p
                            v-else-if="selectedUsage && selectedUsage.length === 0"
                            class="text-xs text-muted-foreground"
                        >
                            Not linked from anywhere on the site. Safe to delete.
                        </p>
                        <ul v-else-if="selectedUsage" class="space-y-1.5">
                            <li
                                v-for="(usage, i) in selectedUsage"
                                :key="`${usage.label}-${usage.context}-${i}`"
                                class="rounded-lg bg-background px-2 py-1.5 text-xs"
                            >
                                <a
                                    v-if="usage.editUrl"
                                    :href="usage.editUrl"
                                    class="font-semibold text-brand hover:underline"
                                >
                                    {{ usage.label }}
                                </a>
                                <span v-else class="font-semibold">{{ usage.label }}</span>
                                <span class="block text-muted-foreground">{{ usage.context }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs font-medium transition hover:bg-muted"
                            @click="copyUrl(selectedFile)"
                        >
                            <component
                                :is="
                                    copied === selectedFile.path ? Check : Copy
                                "
                                class="size-3.5"
                                :class="
                                    copied === selectedFile.path
                                        ? 'text-emerald-600'
                                        : ''
                                "
                            />
                            {{
                                copied === selectedFile.path
                                    ? 'Copied'
                                    : 'Copy link'
                            }}
                        </button>
                        <a
                            :href="selectedFile.url"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs font-medium transition hover:bg-muted"
                        >
                            <ExternalLink class="size-3.5" />
                            Open
                        </a>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs font-medium text-destructive transition hover:bg-destructive/10"
                            @click="destroy(selectedFile)"
                        >
                            <Trash2 class="size-3.5" />
                            Delete
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
