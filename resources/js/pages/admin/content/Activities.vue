<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus, Search, Trash2, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import ContentEditorShell from '@/components/admin/ContentEditorShell.vue';
import { clone } from '@/components/admin/schema';
import { activityEvents } from '@/lib/sampleData';

const props = defineProps<{ section: string; label: string; value: unknown }>();

interface Bilingual {
    en?: string;
    dv?: string;
}

interface ActivityRow {
    id: number;
    date: string;
    time: string;
    category: string;
    title: Bilingual;
    location: Bilingual;
    description: Bilingual;
}

const form = useForm<{ value: ActivityRow[] }>({
    value:
        (props.value as ActivityRow[] | null) ??
        clone(activityEvents as unknown as ActivityRow[]),
});

const CATEGORIES = [
    { value: 'sports', label: 'Sports' },
    { value: 'arts', label: 'Arts' },
    { value: 'clubs', label: 'Clubs' },
    { value: 'trips', label: 'Trips' },
];

const query = ref('');
const category = ref('');
const upcomingOnly = ref(false);
const expanded = ref<number[]>([]);

const today = new Date().toISOString().slice(0, 10);

const counts = computed(() => {
    const tally: Record<string, number> = {};

    for (const row of form.value) {
        tally[row.category] = (tally[row.category] ?? 0) + 1;
    }

    return tally;
});

/**
 * Rows matching the filters. Same objects as the form holds, so editing a
 * filtered row writes straight through to what gets saved.
 */
const visible = computed(() => {
    const q = query.value.trim().toLowerCase();

    return form.value
        .filter((row) => {
            if (category.value && row.category !== category.value) {
                return false;
            }

            if (upcomingOnly.value && row.date < today) {
                return false;
            }

            if (!q) {
                return true;
            }

            return (
                (row.title.en ?? '').toLowerCase().includes(q) ||
                (row.title.dv ?? '').includes(query.value.trim()) ||
                (row.location.en ?? '').toLowerCase().includes(q)
            );
        })
        .sort((a, b) => b.date.localeCompare(a.date));
});

const filtering = computed(
    () =>
        Boolean(query.value.trim()) ||
        Boolean(category.value) ||
        upcomingOnly.value,
);

function nextId() {
    return (
        form.value.reduce((max, e) => Math.max(max, Number(e.id) || 0), 0) + 1
    );
}

function addRow() {
    const id = nextId();

    form.value.unshift({
        id,
        date: today,
        time: '',
        category: category.value || 'clubs',
        title: { en: '', dv: '' },
        location: { en: '', dv: '' },
        description: { en: '', dv: '' },
    });

    expanded.value = [...expanded.value, id];
}

function removeRow(row: ActivityRow) {
    if (!confirm(`Remove "${row.title.en || 'this activity'}"?`)) {
        return;
    }

    // Splice by identity — a filtered row's position is not its form index.
    const index = form.value.indexOf(row);

    if (index !== -1) {
        form.value.splice(index, 1);
    }
}

function toggle(id: number) {
    expanded.value = expanded.value.includes(id)
        ? expanded.value.filter((e) => e !== id)
        : [...expanded.value, id];
}

function clearFilters() {
    query.value = '';
    category.value = '';
    upcomingOnly.value = false;
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
                    placeholder="Search title or place…"
                    :class="[field, 'w-56 ps-8']"
                />
            </div>

            <select v-model="category" :class="[field, 'w-36']">
                <option value="">All categories</option>
                <option v-for="c in CATEGORIES" :key="c.value" :value="c.value">
                    {{ c.label }} ({{ counts[c.value] ?? 0 }})
                </option>
            </select>

            <label class="inline-flex items-center gap-1.5 text-sm">
                <input
                    v-model="upcomingOnly"
                    type="checkbox"
                    class="size-4 rounded border-border"
                />
                Upcoming only
            </label>

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
                <Plus class="size-4" /> Add activity
            </button>
        </div>

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
                                Date
                            </th>
                            <th class="px-2 py-2 text-left font-medium">
                                Time
                            </th>
                            <th class="px-2 py-2 text-left font-medium">
                                Category
                            </th>
                            <th class="px-2 py-2 text-left font-medium">
                                Location
                            </th>
                            <th class="px-2 py-2 text-right font-medium">
                                ދިވެހި
                            </th>
                            <th class="w-px px-2 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="row in visible" :key="row.id">
                            <tr
                                class="border-b border-border hover:bg-muted/30"
                            >
                                <td class="px-2 py-1.5">
                                    <input
                                        v-model="row.title.en"
                                        type="text"
                                        placeholder="Football practice"
                                        aria-label="Title (English)"
                                        :class="[field, 'w-52']"
                                    />
                                </td>
                                <td class="px-2 py-1.5">
                                    <input
                                        v-model="row.title.dv"
                                        type="text"
                                        dir="rtl"
                                        placeholder="ސުރުޚީ"
                                        aria-label="Title (Dhivehi)"
                                        :class="[field, 'w-40']"
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
                                    <input
                                        v-model="row.time"
                                        type="text"
                                        placeholder="15:30 – 17:00"
                                        aria-label="Time"
                                        :class="[field, 'w-28']"
                                    />
                                </td>
                                <td class="px-2 py-1.5">
                                    <select
                                        v-model="row.category"
                                        aria-label="Category"
                                        :class="[field, 'w-28']"
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
                                    <input
                                        v-model="row.location.en"
                                        type="text"
                                        placeholder="School ground"
                                        aria-label="Location (English)"
                                        :class="[field, 'w-40']"
                                    />
                                </td>
                                <td class="px-2 py-1.5">
                                    <input
                                        v-model="row.location.dv"
                                        type="text"
                                        dir="rtl"
                                        placeholder="ތަން"
                                        aria-label="Location (Dhivehi)"
                                        :class="[field, 'w-32']"
                                    />
                                </td>
                                <td class="px-2 py-1.5">
                                    <div class="flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            class="rounded-md px-2 py-1 text-xs font-medium transition"
                                            :class="
                                                expanded.includes(row.id)
                                                    ? 'bg-brand-muted text-brand'
                                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                            "
                                            title="Description"
                                            @click="toggle(row.id)"
                                        >
                                            Note
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-md p-1.5 text-muted-foreground transition hover:text-red-600"
                                            title="Remove"
                                            @click="removeRow(row)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-if="expanded.includes(row.id)"
                                class="border-b border-border bg-muted/20"
                            >
                                <td colspan="8" class="px-3 py-2.5">
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <textarea
                                            v-model="row.description.en"
                                            rows="2"
                                            placeholder="Description (English)"
                                            aria-label="Description (English)"
                                            :class="[field, 'resize-y']"
                                        />
                                        <textarea
                                            v-model="row.description.dv"
                                            rows="2"
                                            dir="rtl"
                                            placeholder="ތަފްޞީލު"
                                            aria-label="Description (Dhivehi)"
                                            :class="[field, 'resize-y']"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-if="!visible.length">
                            <td
                                colspan="8"
                                class="px-3 py-10 text-center text-sm text-muted-foreground"
                            >
                                {{
                                    filtering
                                        ? 'No activities match these filters.'
                                        : 'No activities yet. Use “Add activity” to create one.'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </ContentEditorShell>
</template>
