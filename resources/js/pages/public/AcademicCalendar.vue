<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AddToCalendarButtons from '@/components/public/AddToCalendarButtons.vue';
import EventDetailCard from '@/components/public/EventDetailCard.vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import {
    ChevronLeft,
    ChevronRight,
    Download,
    RotateCcw,
    Search,
} from '@/lib/publicIcons';
import { academicEvents } from '@/lib/sampleData';
import type { AcademicEvent } from '@/lib/sampleData';

const props = defineProps<{ events?: AcademicEvent[] }>();

const { t, pick, date, isRtl, messages } = useLocale();

const source = computed<AcademicEvent[]>(() => props.events ?? academicEvents);

/** Colour + label mapping used for the month grid bars, side panel and legend. */
const catStyles: Record<
    AcademicEvent['type'],
    { bar: string; dot: string; label: { en: string; dv: string } }
> = {
    term: {
        bar: 'bg-sky-200 text-sky-900 ring-sky-300',
        dot: 'bg-sky-400',
        label: { en: 'School Days', dv: 'ސްކޫލް ދުވަސް' },
    },
    exam: {
        bar: 'bg-fuchsia-200 text-fuchsia-900 ring-fuchsia-300',
        dot: 'bg-fuchsia-400',
        label: { en: 'Exam Days', dv: 'އިމްތިޙާން ދުވަސް' },
    },
    holiday: {
        bar: 'bg-teal-200 text-teal-900 ring-teal-300',
        dot: 'bg-teal-400',
        label: { en: 'Term Holidays', dv: 'ޓާމް ބަންދު' },
    },
    event: {
        bar: 'bg-rose-200 text-rose-900 ring-rose-300',
        dot: 'bg-rose-400',
        label: { en: 'Public Holiday', dv: 'ބަންދު ދުވަސް' },
    },
    meeting: {
        bar: 'bg-slate-200 text-slate-800 ring-slate-300',
        dot: 'bg-slate-400',
        label: { en: 'Other', dv: 'އެހެނިހެން' },
    },
};

const legend = (['term', 'exam', 'holiday', 'event', 'meeting'] as const).map(
    (key) => ({ key, ...catStyles[key] }),
);

const views = [
    { key: 'month' as const, label: messages.calendar.month },
    { key: 'year' as const, label: messages.calendar.year },
    { key: 'list' as const, label: messages.calendar.list },
];

const activeView = ref<'month' | 'year' | 'list'>('month');
const query = ref('');

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();

    return [...source.value]
        .filter(
            (e) =>
                !q ||
                e.title.en.toLowerCase().includes(q) ||
                e.title.dv.includes(query.value.trim()),
        )
        .sort((a, b) => a.date.localeCompare(b.date));
});

const isSearching = computed(() => query.value.trim().length > 0);

function range(e: AcademicEvent) {
    return e.endDate
        ? `${date(e.date, { month: 'short', day: 'numeric' })} – ${date(e.endDate, { month: 'short', day: 'numeric' })}`
        : date(e.date);
}

/** Colours and labels for the exported calendar, matching the printed one. */
const EXPORT_CATEGORIES: Record<
    AcademicEvent['type'],
    {
        bg: [number, number, number];
        fg: [number, number, number];
        label: string;
    }
> = {
    term: { bg: [191, 219, 254], fg: [30, 58, 138], label: 'School Days' },
    exam: { bg: [245, 208, 254], fg: [134, 25, 143], label: 'Exam Days' },
    holiday: { bg: [153, 246, 228], fg: [17, 94, 89], label: 'Term Holidays' },
    event: { bg: [254, 202, 202], fg: [159, 18, 57], label: 'Public Holiday' },
    meeting: { bg: [226, 232, 240], fg: [30, 41, 59], label: 'Other' },
};

const LEGEND_ORDER = ['term', 'exam', 'holiday', 'event', 'meeting'] as const;

const BRAND_NAVY: [number, number, number] = [31, 78, 121];

const exporting = ref(false);

/**
 * Download the selected year as a PDF laid out like the printed academic
 * calendar. jsPDF is imported on demand so it stays out of the initial bundle
 * for the many visitors who never export.
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

        const dayShort = (d: string) =>
            `${Number(d.slice(8, 10))} ${new Date(d).toLocaleString('en-GB', { month: 'short' })}`;
        const dateRange = (e: AcademicEvent) =>
            !e.endDate || e.endDate === e.date
                ? dayShort(e.date)
                : `${dayShort(e.date)} - ${dayShort(e.endDate)}`;
        const monthName = (d: string) =>
            new Date(d).toLocaleString('en-GB', {
                month: 'long',
                year: 'numeric',
            });

        const events = [...source.value]
            .filter((e) => e.date.slice(0, 4) === String(cursor.value.y))
            .sort((a, b) => a.date.localeCompare(b.date));

        const groups = new Map<string, AcademicEvent[]>();

        for (const e of events) {
            const key = e.date.slice(0, 7);

            if (!groups.has(key)) {
                groups.set(key, []);
            }

            groups.get(key)!.push(e);
        }

        // One row per event; the month cell spans its group with rowSpan.
        const body = [...groups.values()].flatMap((list) =>
            list.map((e, i) => {
                const category = EXPORT_CATEGORIES[e.type];
                const isPublicHoliday = e.type === 'event';
                const titled = {
                    content: e.title.en,
                    styles: {
                        fillColor: category.bg,
                        textColor: category.fg,
                        fontStyle: 'bold' as const,
                    },
                };
                const blank = { content: '' };

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
                    { content: dateRange(e) },
                    isPublicHoliday ? blank : titled,
                    isPublicHoliday ? titled : blank,
                ];
            }),
        );

        // A4 portrait at a tight 18pt (~6mm) page margin, so the year fits in
        // as few sheets as possible: hairline rules, small type, minimal
        // padding. MARGIN also positions the title bar and the legend.
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
            `ACADEMIC CALENDAR ${cursor.value.y}`,
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
            head: [['Month', 'Date', 'Calendar Dates', 'Public Holidays']],
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
                1: { cellWidth: 62 },
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

        doc.save(`academic-calendar-${cursor.value.y}.pdf`);
    } finally {
        exporting.value = false;
    }
}

/* --------------------------------------------------------- calendar grids */

// 2023-01-01 is a Sunday, so this yields Sun–Sat in the active locale.
const WEEKDAYS = computed(() =>
    Array.from({ length: 7 }, (_, i) =>
        date(`2023-01-0${i + 1}`, { weekday: 'short' }),
    ),
);

function pad(n: number): string {
    return String(n).padStart(2, '0');
}

function iso(y: number, m: number, d: number): string {
    return `${y}-${pad(m + 1)}-${pad(d)}`;
}

const now = new Date();
const todayStr = iso(now.getFullYear(), now.getMonth(), now.getDate());

const cursor = ref({ y: now.getFullYear(), m: now.getMonth() });

function weeksOf(y: number, m: number) {
    const startWeekday = new Date(y, m, 1).getDay();
    const cells: {
        dateStr: string;
        day: number;
        inMonth: boolean;
        isToday: boolean;
    }[] = [];

    for (let i = 0; i < 42; i++) {
        const d = new Date(y, m, 1 - startWeekday + i);
        const dateStr = iso(d.getFullYear(), d.getMonth(), d.getDate());
        cells.push({
            dateStr,
            day: d.getDate(),
            inMonth: d.getMonth() === m,
            isToday: dateStr === todayStr,
        });
    }

    const rows: (typeof cells)[] = [];

    for (let i = 0; i < 42; i += 7) {
        rows.push(cells.slice(i, i + 7));
    }

    while (rows.length > 4 && rows[rows.length - 1].every((c) => !c.inMonth)) {
        rows.pop();
    }

    return rows;
}

const monthWeeks = computed(() => weeksOf(cursor.value.y, cursor.value.m));

/** Split each week into stacked lanes of continuous multi-day event bars. */
type Segment = {
    event: AcademicEvent;
    startCol: number;
    span: number;
    isStart: boolean;
    isEnd: boolean;
};
type WeekRow = { days: ReturnType<typeof weeksOf>[number]; lanes: Segment[][] };

function buildRows(weeks: ReturnType<typeof weeksOf>): WeekRow[] {
    return weeks.map((week) => {
        const weekStart = week[0].dateStr;
        const weekEnd = week[6].dateStr;
        const segments: Segment[] = filtered.value
            .filter(
                (e) => (e.endDate || e.date) >= weekStart && e.date <= weekEnd,
            )
            .map((e) => {
                const end = e.endDate || e.date;
                const from = e.date < weekStart ? weekStart : e.date;
                const to = end > weekEnd ? weekEnd : end;
                const startCol = week.findIndex((c) => c.dateStr === from);
                const endCol = week.findIndex((c) => c.dateStr === to);

                return {
                    event: e,
                    startCol,
                    span: endCol - startCol + 1,
                    isStart: e.date >= weekStart,
                    isEnd: end <= weekEnd,
                };
            })
            .sort((a, b) => a.startCol - b.startCol || b.span - a.span);

        const lanes: Segment[][] = [];

        for (const seg of segments) {
            let lane = lanes.find((l) =>
                l.every(
                    (s) =>
                        seg.startCol >= s.startCol + s.span ||
                        seg.startCol + seg.span <= s.startCol,
                ),
            );

            if (!lane) {
                lane = [];
                lanes.push(lane);
            }

            lane.push(seg);
        }

        return { days: week, lanes };
    });
}

const monthRows = computed(() => buildRows(monthWeeks.value));

function eventsInMonth(y: number, m: number): AcademicEvent[] {
    const monthStart = iso(y, m, 1);
    const monthEnd = iso(y, m, new Date(y, m + 1, 0).getDate());

    return filtered.value.filter(
        (e) => (e.endDate || e.date) >= monthStart && e.date <= monthEnd,
    );
}

/** Events overlapping the visible month, for the side panel. */
const monthEvents = computed(() =>
    eventsInMonth(cursor.value.y, cursor.value.m),
);

/** Events overlapping the selected year, for the list view. */
const listEvents = computed(() => {
    const yearStart = `${cursor.value.y}-01-01`;
    const yearEnd = `${cursor.value.y}-12-31`;

    return filtered.value.filter(
        (e) => (e.endDate || e.date) >= yearStart && e.date <= yearEnd,
    );
});

const yearData = computed(() =>
    Array.from({ length: 12 }, (_, m) => ({
        m,
        label: date(iso(cursor.value.y, m, 1), {
            month: 'long',
            year: 'numeric',
        }),
        rows: buildRows(weeksOf(cursor.value.y, m)),
        events: eventsInMonth(cursor.value.y, m),
    })),
);

/**
 * Years offered by the year pickers: every year the events touch, plus the
 * current year and whichever year is on screen, so arrowing past the end of the
 * events never leaves the picker without a matching option.
 */
const availableYears = computed(() => {
    const years = new Set<number>([now.getFullYear(), cursor.value.y]);

    for (const e of source.value) {
        years.add(Number(e.date.slice(0, 4)));

        if (e.endDate) {
            years.add(Number(e.endDate.slice(0, 4)));
        }
    }

    return [...years].sort((a, b) => a - b);
});

/** Month names in the active locale, for the month picker. */
const monthOptions = computed(() =>
    Array.from({ length: 12 }, (_, m) => ({
        value: m,
        label: date(iso(cursor.value.y, m, 1), { month: 'long' }),
    })),
);

function shiftMonth(delta: number) {
    let { y, m } = cursor.value;
    m += delta;

    if (m < 0) {
        m = 11;
        y -= 1;
    } else if (m > 11) {
        m = 0;
        y += 1;
    }

    cursor.value = { y, m };
}

function shiftYear(delta: number) {
    cursor.value = { y: cursor.value.y + delta, m: cursor.value.m };
}

function goToday() {
    cursor.value = { y: now.getFullYear(), m: now.getMonth() };
}
</script>

<template>
    <Head :title="t(messages.nav.academicCalendar)" />

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div
            class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
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
                            en: 'Search events…',
                            dv: 'ހަރަކާތްތައް ހޯއްދަވާ…',
                        })
                    "
                    class="w-full rounded-full border border-border bg-background py-1.5 ps-9 pe-3 text-sm text-foreground transition placeholder:text-muted-foreground focus:border-brand focus:outline-none"
                />
            </div>
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

        <!-- View switcher -->
        <div
            v-if="!isSearching"
            class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div
                class="inline-flex rounded-full border border-border bg-background p-1"
            >
                <button
                    v-for="view in views"
                    :key="view.key"
                    type="button"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                    :class="
                        activeView === view.key
                            ? 'bg-brand text-brand-foreground'
                            : 'text-foreground/70 hover:text-brand'
                    "
                    @click="activeView = view.key"
                >
                    {{ t(view.label) }}
                </button>
            </div>

            <!-- Period navigation (month + year views) -->
            <div v-if="activeView !== 'list'" class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-full border border-border p-1.5 text-foreground/70 transition hover:border-brand/40 hover:text-brand"
                    :aria-label="t(messages.calendar.month)"
                    @click="
                        activeView === 'year' ? shiftYear(-1) : shiftMonth(-1)
                    "
                >
                    <component
                        :is="isRtl ? ChevronRight : ChevronLeft"
                        class="size-4"
                    />
                </button>
                <label
                    v-if="activeView === 'year'"
                    class="inline-flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <span class="hidden sm:inline">{{
                        t({ en: 'Filter by year', dv: 'އަހަރު ފިލްޓަރ' })
                    }}</span>
                    <select
                        v-model.number="cursor.y"
                        class="rounded-md border border-border bg-background px-2 py-1 text-sm font-semibold text-foreground focus:border-brand focus:outline-none"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">
                            {{ y }}
                        </option>
                    </select>
                </label>
                <div v-else class="flex items-center gap-1.5">
                    <label class="sr-only" for="calendar-month">{{
                        t(messages.calendar.month)
                    }}</label>
                    <select
                        id="calendar-month"
                        v-model.number="cursor.m"
                        class="rounded-md border border-border bg-background px-2 py-1 text-sm font-semibold text-foreground focus:border-brand focus:outline-none"
                    >
                        <option
                            v-for="month in monthOptions"
                            :key="month.value"
                            :value="month.value"
                        >
                            {{ month.label }}
                        </option>
                    </select>
                    <label class="sr-only" for="calendar-year">{{
                        t(messages.calendar.year)
                    }}</label>
                    <select
                        id="calendar-year"
                        v-model.number="cursor.y"
                        class="rounded-md border border-border bg-background px-2 py-1 text-sm font-semibold text-foreground focus:border-brand focus:outline-none"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">
                            {{ y }}
                        </option>
                    </select>
                </div>
                <button
                    type="button"
                    class="rounded-full border border-border p-1.5 text-foreground/70 transition hover:border-brand/40 hover:text-brand"
                    :aria-label="t(messages.calendar.month)"
                    @click="
                        activeView === 'year' ? shiftYear(1) : shiftMonth(1)
                    "
                >
                    <component
                        :is="isRtl ? ChevronLeft : ChevronRight"
                        class="size-4"
                    />
                </button>
                <button
                    type="button"
                    class="rounded-full border border-border px-3 py-1.5 text-sm font-medium text-foreground/70 transition hover:border-brand/40 hover:text-brand"
                    @click="goToday"
                >
                    {{ t(messages.calendar.today) }}
                </button>
            </div>

            <!-- Year filter (list view) -->
            <label
                v-if="activeView === 'list'"
                class="inline-flex items-center gap-2 text-sm text-muted-foreground"
            >
                <span class="hidden sm:inline">{{
                    t({ en: 'Filter by year', dv: 'އަހަރު ފިލްޓަރ' })
                }}</span>
                <select
                    v-model.number="cursor.y"
                    class="rounded-md border border-border bg-background px-2 py-1 text-sm font-semibold text-foreground focus:border-brand focus:outline-none"
                >
                    <option v-for="y in availableYears" :key="y" :value="y">
                        {{ y }}
                    </option>
                </select>
            </label>
        </div>

        <!-- Legend (list + year views, and search results) -->
        <div
            v-if="isSearching || activeView !== 'month'"
            class="mb-6 flex flex-wrap gap-x-4 gap-y-2"
        >
            <span
                v-for="c in legend"
                :key="c.key"
                class="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <span class="size-2.5 rounded-full" :class="c.dot" />
                {{ t(c.label) }}
            </span>
        </div>

        <!-- SEARCH results (list) -->
        <ol
            v-if="isSearching"
            class="relative space-y-3 border-s border-border ps-6"
        >
            <Reveal
                v-for="(e, i) in filtered"
                :key="e.id"
                as="li"
                :delay="i * 40"
                class="relative"
            >
                <span
                    class="absolute -start-[1.6rem] top-2 size-3 rounded-full ring-4 ring-background"
                    :class="catStyles[e.type].dot"
                />
                <div
                    class="rounded-2xl border border-border bg-background p-4 shadow-sm transition hover:shadow-md"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                            :class="catStyles[e.type].bar"
                            >{{ t(catStyles[e.type].label) }}</span
                        >
                        <span
                            class="ms-auto text-sm font-medium text-foreground"
                            >{{ range(e) }}</span
                        >
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <h3
                            class="min-w-0 font-semibold text-foreground"
                            dir="auto"
                        >
                            {{ pick(e.title) }}
                        </h3>
                        <AddToCalendarButtons :event="e" />
                    </div>
                </div>
            </Reveal>
            <li v-if="!filtered.length" class="text-sm text-muted-foreground">
                {{ t(messages.calendar.noEvents) }}
            </li>
        </ol>

        <!-- LIST view -->
        <ol
            v-else-if="activeView === 'list'"
            class="relative space-y-3 border-s border-border ps-6"
        >
            <Reveal
                v-for="(e, i) in listEvents"
                :key="e.id"
                as="li"
                :delay="i * 50"
                class="relative"
            >
                <span
                    class="absolute -start-[1.6rem] top-2 size-3 rounded-full ring-4 ring-background"
                    :class="catStyles[e.type].dot"
                />
                <div
                    class="rounded-2xl border border-border bg-background p-4 shadow-sm transition hover:shadow-md"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                            :class="catStyles[e.type].bar"
                            >{{ t(catStyles[e.type].label) }}</span
                        >
                        <span
                            class="ms-auto text-sm font-medium text-foreground"
                            >{{ range(e) }}</span
                        >
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <h3
                            class="min-w-0 font-semibold text-foreground"
                            dir="auto"
                        >
                            {{ pick(e.title) }}
                        </h3>
                        <AddToCalendarButtons :event="e" />
                    </div>
                </div>
            </Reveal>
            <li v-if="!listEvents.length" class="text-sm text-muted-foreground">
                {{ t(messages.calendar.noEvents) }}
            </li>
        </ol>

        <!-- MONTH view -->
        <div
            v-else-if="activeView === 'month'"
            class="grid gap-6 lg:grid-cols-3"
        >
            <div class="lg:col-span-2">
                <div
                    class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm"
                >
                    <div
                        class="grid grid-cols-7 border-b border-border bg-muted/40 text-center text-xs font-semibold text-muted-foreground"
                    >
                        <div v-for="wd in WEEKDAYS" :key="wd" class="py-2">
                            {{ wd }}
                        </div>
                    </div>
                    <div
                        v-for="(row, wi) in monthRows"
                        :key="wi"
                        class="border-b border-border last:border-b-0"
                    >
                        <!-- Day numbers -->
                        <div class="grid grid-cols-7">
                            <div
                                v-for="cell in row.days"
                                :key="cell.dateStr"
                                class="border-e border-border px-1.5 pt-1.5 last:border-e-0"
                                :class="cell.inMonth ? '' : 'bg-muted/30'"
                            >
                                <span
                                    class="inline-flex size-6 items-center justify-center rounded-full text-xs font-medium"
                                    :class="
                                        cell.isToday
                                            ? 'bg-brand text-brand-foreground'
                                            : cell.inMonth
                                              ? 'text-foreground/70'
                                              : 'text-muted-foreground/50'
                                    "
                                    >{{ cell.day }}</span
                                >
                            </div>
                        </div>
                        <!-- Event bars -->
                        <div class="space-y-1 px-1 pt-1 pb-1.5">
                            <div
                                v-for="(lane, li) in row.lanes"
                                :key="li"
                                class="grid grid-cols-7 gap-x-1"
                            >
                                <div
                                    v-for="seg in lane"
                                    :key="seg.event.id"
                                    class="min-h-[1.25rem] truncate px-2 py-0.5 text-[0.7rem] leading-tight font-medium ring-1 ring-inset"
                                    :class="[
                                        catStyles[seg.event.type].bar,
                                        seg.isStart
                                            ? 'rounded-s-md ps-2'
                                            : 'ps-1',
                                        seg.isEnd
                                            ? 'rounded-e-md pe-2'
                                            : 'pe-1',
                                    ]"
                                    :style="{
                                        gridColumn: `${seg.startCol + 1} / span ${seg.span}`,
                                    }"
                                    :title="pick(seg.event.title)"
                                    dir="auto"
                                >
                                    {{ pick(seg.event.title) }}
                                </div>
                            </div>
                            <div v-if="!row.lanes.length" class="h-1" />
                        </div>
                    </div>
                </div>

                <!-- Legend + reset -->
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
                    <span
                        v-for="c in legend"
                        :key="c.key"
                        class="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span class="size-3 rounded-full" :class="c.dot" />
                        {{ t(c.label) }}
                    </span>
                    <button
                        type="button"
                        class="ms-auto inline-flex items-center gap-1.5 text-xs font-semibold text-brand transition hover:opacity-80"
                        @click="goToday"
                    >
                        <RotateCcw class="size-3.5" />
                        {{ t(messages.calendar.today) }}
                    </button>
                </div>
            </div>

            <!-- Events for the month -->
            <aside class="lg:col-span-1">
                <h2 class="mb-4 text-lg font-semibold text-foreground">
                    {{
                        t({
                            en: 'Events for the month',
                            dv: 'މި މަހުގެ ހަރަކާތްތައް',
                        })
                    }}
                </h2>
                <ol class="space-y-3">
                    <EventDetailCard
                        v-for="e in monthEvents"
                        :key="e.id"
                        :event="e"
                        :badge-class="catStyles[e.type].bar"
                    />
                    <li
                        v-if="!monthEvents.length"
                        class="rounded-2xl border border-dashed border-border p-4 text-center text-sm text-muted-foreground"
                    >
                        {{ t(messages.calendar.noEvents) }}
                    </li>
                </ol>
            </aside>
        </div>

        <!-- YEAR view -->
        <div v-else class="space-y-5">
            <div
                v-for="mo in yearData"
                :key="mo.m"
                class="grid gap-4 rounded-2xl border border-border bg-background p-4 shadow-sm md:grid-cols-2"
            >
                <!-- Mini month calendar with event bars -->
                <div>
                    <h3
                        class="mb-2 text-center text-sm font-semibold text-foreground"
                    >
                        {{ mo.label }}
                    </h3>
                    <div
                        class="overflow-hidden rounded-xl border border-border"
                    >
                        <div
                            class="grid grid-cols-7 border-b border-border bg-muted/40 text-center text-[0.65rem] font-semibold text-muted-foreground"
                        >
                            <div v-for="wd in WEEKDAYS" :key="wd" class="py-1">
                                {{ wd }}
                            </div>
                        </div>
                        <div
                            v-for="(row, wi) in mo.rows"
                            :key="wi"
                            class="border-b border-border last:border-b-0"
                        >
                            <div class="grid grid-cols-7">
                                <div
                                    v-for="cell in row.days"
                                    :key="cell.dateStr"
                                    class="border-e border-border py-0.5 text-center last:border-e-0"
                                >
                                    <span
                                        class="inline-flex size-5 items-center justify-center rounded-full text-[0.65rem]"
                                        :class="
                                            cell.isToday
                                                ? 'bg-brand text-brand-foreground'
                                                : cell.inMonth
                                                  ? 'text-foreground/80'
                                                  : 'text-muted-foreground/40'
                                        "
                                        >{{ cell.day }}</span
                                    >
                                </div>
                            </div>
                            <div class="space-y-0.5 px-0.5 pt-0.5 pb-1">
                                <div
                                    v-for="(lane, li) in row.lanes"
                                    :key="li"
                                    class="grid grid-cols-7 gap-x-0.5"
                                >
                                    <div
                                        v-for="seg in lane"
                                        :key="seg.event.id"
                                        class="min-h-[1rem] truncate py-0.5 text-[0.6rem] leading-tight font-medium ring-1 ring-inset"
                                        :class="[
                                            catStyles[seg.event.type].bar,
                                            seg.isStart
                                                ? 'rounded-s-md ps-1.5'
                                                : 'ps-0.5',
                                            seg.isEnd
                                                ? 'rounded-e-md pe-1.5'
                                                : 'pe-0.5',
                                        ]"
                                        :style="{
                                            gridColumn: `${seg.startCol + 1} / span ${seg.span}`,
                                        }"
                                        :title="pick(seg.event.title)"
                                        dir="auto"
                                    >
                                        {{ pick(seg.event.title) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Events list for the month -->
                <ol class="space-y-3">
                    <EventDetailCard
                        v-for="e in mo.events"
                        :key="e.id"
                        :event="e"
                        :badge-class="catStyles[e.type].bar"
                    />
                    <li
                        v-if="!mo.events.length"
                        class="rounded-2xl border border-dashed border-border p-4 text-center text-sm text-muted-foreground"
                    >
                        {{ t(messages.calendar.noEvents) }}
                    </li>
                </ol>
            </div>
        </div>
    </div>
</template>
