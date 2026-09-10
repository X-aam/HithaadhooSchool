<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AddToCalendarButtons from '@/components/public/AddToCalendarButtons.vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import { Clock, Download, MapPin, Search } from '@/lib/publicIcons';
import { activityEvents } from '@/lib/sampleData';
import type {
    ActivityCategory,
    ActivityEvent,
    Bilingual,
} from '@/lib/sampleData';

const props = defineProps<{ events?: ActivityEvent[] }>();

const { t, pick, date, messages } = useLocale();

const source = computed<ActivityEvent[]>(() => props.events ?? activityEvents);

/** Colour + label mapping used for the card accents and legend. */
const catStyles: Record<
    ActivityCategory,
    { bar: string; dot: string; border: string; label: Bilingual }
> = {
    sports: {
        bar: 'bg-emerald-200 text-emerald-900 ring-emerald-300',
        dot: 'bg-emerald-400',
        border: 'border-s-emerald-500',
        label: { en: 'Sports', dv: 'ކުޅިވަރު' },
    },
    arts: {
        bar: 'bg-violet-200 text-violet-900 ring-violet-300',
        dot: 'bg-violet-400',
        border: 'border-s-violet-500',
        label: { en: 'Arts', dv: 'ފަންނު' },
    },
    clubs: {
        bar: 'bg-sky-200 text-sky-900 ring-sky-300',
        dot: 'bg-sky-400',
        border: 'border-s-sky-500',
        label: { en: 'Clubs', dv: 'ކްލަބް' },
    },
    trips: {
        bar: 'bg-amber-200 text-amber-900 ring-amber-300',
        dot: 'bg-amber-400',
        border: 'border-s-amber-500',
        label: { en: 'Trips', dv: 'ދަތުރު' },
    },
};

const legend = (['sports', 'arts', 'clubs', 'trips'] as const).map((key) => ({
    key,
    ...catStyles[key],
}));

const query = ref('');
const now = new Date();
const selectedYear = ref(now.getFullYear());
const selectedMonth = ref<number | 'all'>('all');
const selectedDay = ref<number | 'all'>('all');

const isSearching = computed(() => query.value.trim().length > 0);

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();

    return [...source.value]
        .filter(
            (e) =>
                !q ||
                e.title.en.toLowerCase().includes(q) ||
                (e.title.dv ?? '').includes(query.value.trim()) ||
                (e.location.en ?? '').toLowerCase().includes(q),
        )
        .sort((a, b) => a.date.localeCompare(b.date));
});

/** When searching, show every match; otherwise apply the year/month/day filters. */
const visible = computed(() => {
    if (isSearching.value) {
        return filtered.value;
    }

    return filtered.value.filter((e) => {
        const [y, m, d] = e.date.split('-').map(Number);

        return (
            y === selectedYear.value &&
            (selectedMonth.value === 'all' || m === selectedMonth.value) &&
            (selectedDay.value === 'all' || d === selectedDay.value)
        );
    });
});

function pad(n: number): string {
    return String(n).padStart(2, '0');
}

const availableYears = computed(() => {
    const years = new Set<number>([now.getFullYear()]);

    for (const e of source.value) {
        years.add(Number(e.date.slice(0, 4)));
    }

    return [...years].sort((a, b) => a - b);
});

const monthOptions = computed(() =>
    Array.from({ length: 12 }, (_, m) => ({
        value: m + 1,
        label: date(`${selectedYear.value}-${pad(m + 1)}-01`, {
            month: 'long',
        }),
    })),
);

const dayOptions = Array.from({ length: 31 }, (_, d) => d + 1);

function range(e: ActivityEvent) {
    return date(e.date, { weekday: 'short', month: 'short', day: 'numeric' });
}

/** Colours and labels for the exported activity calendar. */
const EXPORT_CATEGORIES: Record<
    ActivityCategory,
    {
        bg: [number, number, number];
        fg: [number, number, number];
        label: string;
    }
> = {
    sports: { bg: [167, 243, 208], fg: [6, 95, 70], label: 'Sports' },
    arts: { bg: [221, 214, 254], fg: [91, 33, 182], label: 'Arts' },
    clubs: { bg: [191, 219, 254], fg: [30, 58, 138], label: 'Clubs' },
    trips: { bg: [253, 230, 138], fg: [146, 64, 14], label: 'Trips' },
};

const LEGEND_ORDER = ['sports', 'arts', 'clubs', 'trips'] as const;

const BRAND_NAVY: [number, number, number] = [31, 78, 121];

const exporting = ref(false);

/**
 * Download the selected year's activities as a compact A4 PDF, grouped by
 * month. jsPDF is imported on demand so it stays out of the initial bundle,
 * and the English names are used throughout — its built-in fonts cover
 * Latin-1 only, so Thaana would come out blank.
 */
async function exportPdf() {
    if (exporting.value) {
        return;
    }

    exporting.value = true;

    try {
        const [{ jsPDF }, { default: autoTable }] = await Promise.all([
            import('jspdf'),
            import('jspdf-autotable'),
        ]);

        const monthName = (d: string) =>
            new Date(d).toLocaleString('en-GB', {
                month: 'long',
                year: 'numeric',
            });
        const dayShort = (d: string) =>
            `${Number(d.slice(8, 10))} ${new Date(d).toLocaleString('en-GB', { month: 'short' })}`;

        const events = [...source.value]
            .filter((e) => e.date.slice(0, 4) === String(selectedYear.value))
            .sort((a, b) => a.date.localeCompare(b.date));

        const groups = new Map<string, ActivityEvent[]>();

        for (const e of events) {
            const key = e.date.slice(0, 7);

            if (!groups.has(key)) {
                groups.set(key, []);
            }

            groups.get(key)!.push(e);
        }

        // One row per activity; the month cell spans its group with rowSpan.
        const body = [...groups.values()].flatMap((list) =>
            list.map((e, i) => {
                const category = EXPORT_CATEGORIES[e.category];

                return [
                    ...(i === 0
                        ? [
                              {
                                  content: monthName(e.date),
                                  rowSpan: list.length,
                                  styles: {
                                      fillColor: [241, 245, 249] as [
                                          number,
                                          number,
                                          number,
                                      ],
                                      fontStyle: 'bold' as const,
                                      valign: 'top' as const,
                                  },
                              },
                          ]
                        : []),
                    { content: dayShort(e.date) },
                    { content: e.time ?? '' },
                    {
                        content: e.title.en ?? '',
                        styles: {
                            fillColor: category.bg,
                            textColor: category.fg,
                            fontStyle: 'bold' as const,
                        },
                    },
                    { content: e.location.en ?? '' },
                ];
            }),
        );

        // A4 portrait at a tight 18pt (~6mm) margin, so a year fits in as few
        // sheets as possible. MARGIN also places the title bar and legend.
        const MARGIN = 18;
        const LEGEND_HEIGHT = 26;

        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'pt',
            format: 'a4',
        });
        const pageWidth = doc.internal.pageSize.getWidth();

        doc.setFillColor(...BRAND_NAVY);
        doc.rect(MARGIN, MARGIN, pageWidth - MARGIN * 2, 22, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(11);
        doc.text(
            `ACTIVITY CALENDAR ${selectedYear.value}`,
            pageWidth / 2,
            MARGIN + 15,
            { align: 'center' },
        );

        autoTable(doc, {
            startY: MARGIN + 22,
            margin: {
                left: MARGIN,
                right: MARGIN,
                top: MARGIN,
                bottom: MARGIN + LEGEND_HEIGHT,
            },
            head: [['Month', 'Date', 'Time', 'Activity', 'Location']],
            body,
            styles: {
                fontSize: 7.5,
                cellPadding: { top: 2, bottom: 2, left: 4, right: 4 },
                lineColor: [148, 163, 184],
                lineWidth: 0.25,
                overflow: 'linebreak',
                valign: 'middle',
            },
            headStyles: {
                fillColor: BRAND_NAVY,
                textColor: [255, 255, 255],
                fontStyle: 'bold',
                fontSize: 7.5,
                cellPadding: { top: 3, bottom: 3, left: 4, right: 4 },
            },
            columnStyles: {
                0: { cellWidth: 68 },
                1: { cellWidth: 52 },
                2: { cellWidth: 66 },
            },
            // The legend is drawn on every page so a printed sheet stands alone.
            didDrawPage: () => {
                const y = doc.internal.pageSize.getHeight() - MARGIN - 4;
                let x = MARGIN;

                doc.setFontSize(6.5);
                doc.setFont('helvetica', 'bold');

                for (const key of LEGEND_ORDER) {
                    const { bg, fg, label } = EXPORT_CATEGORIES[key];
                    const width = doc.getTextWidth(label) + 8;

                    doc.setFillColor(...bg);
                    doc.rect(x, y - 7, width, 10, 'F');
                    doc.setTextColor(...fg);
                    doc.text(label, x + 4, y);

                    x += width + 4;
                }
            },
        });

        doc.save(`activities-${selectedYear.value}.pdf`);
    } finally {
        exporting.value = false;
    }
}
</script>

<template>
    <Head :title="t(messages.nav.activityCalendar)" />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-6 space-y-3">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="relative w-full sm:max-w-xs">
                    <Search
                        class="pointer-events-none absolute inset-y-0 start-3 my-auto size-4 text-muted-foreground"
                    />
                    <input
                        v-model="query"
                        type="search"
                        :placeholder="
                            t({
                                en: 'Search activities…',
                                dv: 'ހަރަކާތްތައް ހޯއްދަވާ…',
                            })
                        "
                        class="w-full rounded-full border border-border bg-background py-1.5 ps-9 pe-3 text-sm text-foreground transition placeholder:text-muted-foreground focus:border-brand focus:outline-none"
                    />
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <!-- Exports whatever year is picked, so name it on the control. -->
                    <label class="sr-only" for="export-year">{{
                        t(messages.calendar.year)
                    }}</label>
                    <select
                        id="export-year"
                        v-model.number="selectedYear"
                        class="rounded-full border border-border bg-background px-3 py-1.5 text-sm font-semibold text-foreground transition focus:border-brand focus:outline-none"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">
                            {{ y }}
                        </option>
                    </select>
                    <button
                        type="button"
                        class="inline-flex shrink-0 items-center gap-2 rounded-full border border-border px-4 py-1.5 text-sm font-semibold text-foreground/80 transition hover:border-brand/40 hover:text-brand disabled:opacity-60"
                        :disabled="exporting"
                        @click="exportPdf"
                    >
                        <Download class="size-4" />
                        {{
                            exporting
                                ? t({
                                      en: 'Preparing PDF…',
                                      dv: 'ޕީޑީއެފް ތައްޔާރުކުރަނީ…',
                                  })
                                : t(messages.calendar.exportPdf)
                        }}
                    </button>
                </div>
            </div>

            <label
                v-if="!isSearching"
                class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
            >
                <span>{{ t({ en: 'Filter', dv: 'ފިލްޓަރ' }) }}</span>
                <select
                    v-model.number="selectedYear"
                    class="rounded-md border border-border bg-background px-2 py-1 text-sm font-semibold text-foreground focus:border-brand focus:outline-none"
                >
                    <option v-for="y in availableYears" :key="y" :value="y">
                        {{ y }}
                    </option>
                </select>
                <select
                    v-model="selectedMonth"
                    class="rounded-md border border-border bg-background px-2 py-1 text-sm font-semibold text-foreground focus:border-brand focus:outline-none"
                >
                    <option value="all">
                        {{ t({ en: 'All months', dv: 'ހުރިހާ މަސް' }) }}
                    </option>
                    <option
                        v-for="mo in monthOptions"
                        :key="mo.value"
                        :value="mo.value"
                    >
                        {{ mo.label }}
                    </option>
                </select>
                <select
                    v-model="selectedDay"
                    class="rounded-md border border-border bg-background px-2 py-1 text-sm font-semibold text-foreground focus:border-brand focus:outline-none"
                >
                    <option value="all">
                        {{ t({ en: 'All days', dv: 'ހުރިހާ ދުވަސް' }) }}
                    </option>
                    <option v-for="d in dayOptions" :key="d" :value="d">
                        {{ d }}
                    </option>
                </select>
            </label>
        </div>

        <!-- Legend -->
        <div class="mb-6 flex flex-wrap gap-x-4 gap-y-2">
            <span
                v-for="c in legend"
                :key="c.key"
                class="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <span class="size-2.5 rounded-full" :class="c.dot" />
                {{ t(c.label) }}
            </span>
        </div>

        <!-- Activity list -->
        <ol class="space-y-3">
            <Reveal
                v-for="(e, i) in visible"
                :key="e.id"
                as="li"
                :delay="i * 40"
            >
                <div
                    class="rounded-2xl border border-s-4 border-border bg-background p-4 shadow-sm transition hover:shadow-md"
                    :class="catStyles[e.category].border"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                            :class="catStyles[e.category].bar"
                            >{{ t(catStyles[e.category].label) }}</span
                        >
                        <span
                            class="ms-auto text-sm font-medium text-foreground"
                            >{{ range(e) }}</span
                        >
                    </div>
                    <div class="mt-2 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3
                                class="font-semibold text-foreground"
                                dir="auto"
                            >
                                {{ pick(e.title) }}
                            </h3>
                            <div
                                class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground"
                            >
                                <span class="inline-flex items-center gap-1.5"
                                    ><Clock class="size-3.5" /><span
                                        dir="auto"
                                        >{{ e.time }}</span
                                    ></span
                                >
                                <span class="inline-flex items-center gap-1.5"
                                    ><MapPin class="size-3.5" /><span
                                        dir="auto"
                                        >{{ pick(e.location) }}</span
                                    ></span
                                >
                            </div>
                        </div>
                        <AddToCalendarButtons :event="e" />
                    </div>
                </div>
            </Reveal>
            <li
                v-if="!visible.length"
                class="rounded-2xl border border-dashed border-border p-6 text-center text-sm text-muted-foreground"
            >
                {{ t(messages.calendar.noEvents) }}
            </li>
        </ol>
    </div>
</template>
