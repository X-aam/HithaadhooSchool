<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import { ArrowRight, Paperclip, Pin, Search } from '@/lib/publicIcons';
import { announcements } from '@/lib/sampleData';
import type { Announcement, AnnouncementCategory } from '@/lib/sampleData';

const props = defineProps<{ items?: Announcement[] }>();

const { t, pick, date, messages, isMissing, isRtl } = useLocale();

const source = computed<Announcement[]>(() => props.items ?? announcements);

const categories: {
    key: AnnouncementCategory | 'all';
    label: { en: string; dv: string };
}[] = [
    // Reads as a dropdown default now, so it names what it clears — like
    // the year and month filters beside it.
    { key: 'all', label: { en: 'All categories', dv: 'ހުރިހާ ބައި' } },
    { key: 'academic', label: { en: 'Academic', dv: 'ކިޔެވުން' } },
    { key: 'events', label: { en: 'Events', dv: 'ހަރަކާތްތައް' } },
    { key: 'emergency', label: { en: 'Emergency', dv: 'ކުއްލި' } },
    { key: 'general', label: { en: 'General', dv: 'އާންމު' } },
];

const active = ref<AnnouncementCategory | 'all'>('all');
const query = ref('');

/* ---- Date filter ---- */
const now = new Date();
const selectedYear = ref<number | 'all'>('all');
const selectedMonth = ref<number | 'all'>('all');

/** Years the announcements actually span, newest first. */
const availableYears = computed(() => {
    const years = new Set<number>([now.getFullYear()]);

    for (const a of source.value) {
        years.add(Number(a.date.slice(0, 4)));
    }

    return [...years].sort((a, b) => b - a);
});

const monthOptions = computed(() =>
    Array.from({ length: 12 }, (_, m) => ({
        value: m + 1,
        label: date(
            `${selectedYear.value === 'all' ? now.getFullYear() : selectedYear.value}-${String(m + 1).padStart(2, '0')}-01`,
            { month: 'long' },
        ),
    })),
);

const dateFiltered = computed(
    () => selectedYear.value !== 'all' || selectedMonth.value !== 'all',
);

function clearDateFilter() {
    selectedYear.value = 'all';
    selectedMonth.value = 'all';
}

function matchesDate(a: Announcement): boolean {
    const [y, m] = a.date.split('-').map(Number);

    return (
        (selectedYear.value === 'all' || y === selectedYear.value) &&
        (selectedMonth.value === 'all' || m === selectedMonth.value)
    );
}

/**
 * Announcement bodies are rich text now, so the listing shows a plain-text
 * summary rather than raw markup.
 */
function summary(a: Announcement): string {
    const html = pick(a.body) ?? '';
    const text = html
        .replace(/<[^>]*>/g, ' ')
        .replace(/&nbsp;/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    return text.length > 220 ? `${text.slice(0, 220).trimEnd()}…` : text;
}

function matchesQuery(a: Announcement): boolean {
    const q = query.value.trim().toLowerCase();

    if (!q) {
        return true;
    }

    const dv = query.value.trim();

    return (
        a.title.en.toLowerCase().includes(q) ||
        (a.title.dv ?? '').includes(dv) ||
        a.body.en.toLowerCase().includes(q) ||
        (a.body.dv ?? '').includes(dv)
    );
}

const filtered = computed(() =>
    [...source.value]
        .filter(
            (a) =>
                (active.value === 'all' || a.category === active.value) &&
                matchesDate(a) &&
                matchesQuery(a),
        )
        .sort((a, b) => {
            if (a.pinned !== b.pinned) {
                return a.pinned ? -1 : 1;
            }

            return b.date.localeCompare(a.date);
        }),
);
</script>

<template>
    <Head :title="t(messages.nav.announcements)" />

    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <!--
            Search, categories and the date filter share one row. They wrap on
            narrow screens rather than stacking into three fixed blocks.
        -->
        <div class="mb-8 flex flex-wrap items-center gap-x-2 gap-y-2">
            <div class="relative w-44 shrink-0 sm:w-52">
                <Search
                    class="pointer-events-none absolute inset-y-0 start-3 my-auto size-4 text-muted-foreground"
                />
                <input
                    v-model="query"
                    type="search"
                    :placeholder="t({ en: 'Search…', dv: 'ހޯއްދަވާ…' })"
                    class="w-full rounded-full border border-border bg-background py-1.5 ps-9 pe-3 text-sm text-foreground transition placeholder:text-muted-foreground focus:border-brand focus:outline-none"
                />
            </div>

            <select
                v-model="active"
                :aria-label="t({ en: 'Category', dv: 'ބައި' })"
                class="rounded-full border bg-background px-3 py-1.5 text-sm font-medium transition focus:border-brand focus:outline-none"
                :class="
                    active === 'all'
                        ? 'border-border text-foreground'
                        : 'border-brand text-brand'
                "
            >
                <option v-for="c in categories" :key="c.key" :value="c.key">
                    {{ t(c.label) }}
                </option>
            </select>

            <select
                v-model="selectedYear"
                :aria-label="t(messages.calendar.year)"
                class="rounded-full border border-border bg-background px-3 py-1.5 text-sm transition focus:border-brand focus:outline-none"
            >
                <option value="all">
                    {{ t({ en: 'Any year', dv: 'ހުރިހާ އަހަރު' }) }}
                </option>
                <option v-for="y in availableYears" :key="y" :value="y">
                    {{ y }}
                </option>
            </select>

            <select
                v-model="selectedMonth"
                :aria-label="t(messages.calendar.month)"
                class="rounded-full border border-border bg-background px-3 py-1.5 text-sm transition focus:border-brand focus:outline-none"
            >
                <option value="all">
                    {{ t({ en: 'Any month', dv: 'ހުރިހާ މަސް' }) }}
                </option>
                <option
                    v-for="m in monthOptions"
                    :key="m.value"
                    :value="m.value"
                >
                    {{ m.label }}
                </option>
            </select>

            <button
                v-if="dateFiltered"
                type="button"
                class="rounded-full px-2.5 py-1.5 text-xs font-medium text-muted-foreground transition hover:text-foreground"
                @click="clearDateFilter"
            >
                {{ t({ en: 'Clear', dv: 'ސާފުކުރައްވާ' }) }}
            </button>

            <span class="ms-auto shrink-0 text-xs text-muted-foreground">
                {{ filtered.length }} {{ t({ en: 'shown', dv: 'ދައްކަނީ' }) }}
            </span>
        </div>

        <p
            v-if="!filtered.length"
            class="py-16 text-center text-muted-foreground"
        >
            {{ t(messages.common.noResults) }}
        </p>

        <div class="space-y-4">
            <Reveal v-for="(a, i) in filtered" :key="a.id" :delay="i * 60">
                <article
                    class="rounded-2xl border bg-background p-6 shadow-sm transition hover:shadow-md"
                    :class="
                        a.pinned
                            ? 'border-brand/40 ring-1 ring-brand/20'
                            : 'border-border'
                    "
                >
                    <div class="flex items-center gap-2">
                        <span
                            v-if="a.pinned"
                            class="inline-flex items-center gap-1 rounded-full bg-brand px-2.5 py-0.5 text-xs font-semibold text-brand-foreground"
                        >
                            <Pin class="size-3" />
                            {{ t(messages.common.pinned) }}
                        </span>
                        <span
                            class="rounded-full bg-brand-muted px-2.5 py-0.5 text-xs font-medium text-brand"
                        >
                            {{
                                t(
                                    categories.find(
                                        (c) => c.key === a.category,
                                    )!.label,
                                )
                            }}
                        </span>
                        <span class="ms-auto text-xs text-muted-foreground">{{
                            date(a.date)
                        }}</span>
                    </div>
                    <h2
                        class="mt-3 text-xl font-semibold text-foreground"
                        dir="auto"
                    >
                        <Link
                            :href="`/announcements/${a.slug}`"
                            class="transition hover:text-brand"
                        >
                            {{ pick(a.title) }}
                        </Link>
                    </h2>
                    <p
                        class="mt-2 leading-relaxed text-foreground/80"
                        dir="auto"
                    >
                        {{ summary(a) }}
                    </p>
                    <p
                        v-if="isMissing(a.title)"
                        class="mt-2 text-xs text-muted-foreground italic"
                    >
                        {{ t(messages.common.translationMissing) }}
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <Link
                            :href="`/announcements/${a.slug}`"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand transition hover:opacity-80"
                        >
                            {{ t(messages.common.readMore) }}
                            <ArrowRight
                                class="size-3.5"
                                :class="isRtl ? 'rotate-180' : ''"
                            />
                        </Link>
                        <span
                            v-if="a.attachments?.length"
                            class="inline-flex items-center gap-1 text-xs text-muted-foreground"
                        >
                            <Paperclip class="size-3.5" />
                            {{ a.attachments.length }}
                            {{
                                t({
                                    en:
                                        a.attachments.length === 1
                                            ? 'attachment'
                                            : 'attachments',
                                    dv: 'ފައިލް',
                                })
                            }}
                        </span>
                    </div>
                </article>
            </Reveal>
        </div>
    </div>
</template>
