<script setup lang="ts">
import { ChevronLeft, ChevronRight, Plus, Trash2, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import type { FieldDef } from './schema';
import SchemaField from './SchemaField.vue';

const props = defineProps<{
    events: Record<string, any>[];
}>();

/* ------------------------------------------------------------------ types */

type TypeMeta = {
    value: string;
    label: string;
    dot: string;
    chip: string;
};

const TYPES: TypeMeta[] = [
    { value: 'term', label: 'Term start', dot: 'bg-emerald-500', chip: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30' },
    { value: 'exam', label: 'Exam', dot: 'bg-rose-500', chip: 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-500/30' },
    { value: 'holiday', label: 'Holiday', dot: 'bg-amber-500', chip: 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-500/30' },
    { value: 'meeting', label: 'Meeting', dot: 'bg-sky-500', chip: 'bg-sky-500/15 text-sky-700 dark:text-sky-300 border-sky-500/30' },
    { value: 'event', label: 'Event', dot: 'bg-violet-500', chip: 'bg-violet-500/15 text-violet-700 dark:text-violet-300 border-violet-500/30' },
];

function typeMeta(value: string): TypeMeta {
    return TYPES.find((t) => t.value === value) ?? TYPES[TYPES.length - 1];
}

const editFields: FieldDef[] = [
    { key: 'title', type: 'bilingual', label: 'Title' },
    { key: 'date', type: 'date', label: 'Start date' },
    { key: 'endDate', type: 'date', label: 'End date (optional)' },
    {
        key: 'type',
        type: 'select',
        label: 'Type',
        options: TYPES.map((t) => ({ value: t.value, label: t.label })),
    },
];

/* ----------------------------------------------------------------- helpers */

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function pad(n: number): string {
    return String(n).padStart(2, '0');
}

function iso(y: number, m: number, d: number): string {
    return `${y}-${pad(m + 1)}-${pad(d)}`;
}

const today = new Date();
const todayStr = iso(today.getFullYear(), today.getMonth(), today.getDate());

/** Start on the month of the earliest event, or the current month. */
function initialMonth(): { y: number; m: number } {
    const dates = props.events.map((e) => String(e.date)).filter(Boolean).sort();
    const first = dates[0];

    if (first) {
        const [y, m] = first.split('-').map(Number);

        return { y, m: m - 1 };
    }

    return { y: today.getFullYear(), m: today.getMonth() };
}

const cursor = ref(initialMonth());
const monthsToShow = ref(2);

function weeksOf(y: number, m: number) {
    const startWeekday = new Date(y, m, 1).getDay();
    const cells: { dateStr: string; day: number; inMonth: boolean; isToday: boolean }[] = [];

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

    // Drop trailing weeks that belong entirely to the next month.
    while (rows.length && rows[rows.length - 1].every((c) => !c.inMonth)) {
rows.pop();
}

    return rows;
}

const months = computed(() => {
    const out: { y: number; m: number; label: string; weeks: ReturnType<typeof weeksOf> }[] = [];

    for (let i = 0; i < monthsToShow.value; i++) {
        let m = cursor.value.m + i;
        const y = cursor.value.y + Math.floor(m / 12);
        m = ((m % 12) + 12) % 12;
        out.push({
            y,
            m,
            label: new Date(y, m, 1).toLocaleDateString('en', { month: 'long', year: 'numeric' }),
            weeks: weeksOf(y, m),
        });
    }

    return out;
});

const rangeLabel = computed(() => {
    const list = months.value;

    return list.length === 1 ? list[0].label : `${list[0].label} – ${list[list.length - 1].label}`;
});

function eventsForDay(dateStr: string): Record<string, any>[] {
    return props.events
        .filter((e) => dateStr >= String(e.date) && dateStr <= String(e.endDate || e.date))
        .sort((a, b) => String(a.date).localeCompare(String(b.date)));
}

/* -------------------------------------------------------------- navigation */

function shift(delta: number) {
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

function goToday() {
    cursor.value = { y: today.getFullYear(), m: today.getMonth() };
}

/* ----------------------------------------------------------- add / edit */

const editing = ref<Record<string, any> | null>(null);

function nextId(): number {
    return props.events.reduce((max, e) => Math.max(max, Number(e.id) || 0), 0) + 1;
}

/** Guess the term from a date by finding the most recent "term" event on/before it. */
function guessTerm(dateStr: string): 1 | 2 | 3 {
    const terms = props.events
        .filter((e) => e.type === 'term' && String(e.date) <= dateStr)
        .sort((a, b) => String(b.date).localeCompare(String(a.date)));

    return (terms[0]?.term as 1 | 2 | 3) ?? 1;
}

function addOn(dateStr: string) {
    const item: Record<string, any> = {
        id: nextId(),
        date: dateStr,
        term: guessTerm(dateStr),
        type: 'event',
        title: { en: '', dv: '' },
    };
    props.events.push(item);
    editing.value = item;
}

function addToday() {
    const anchor = cursor.value;
    addOn(iso(anchor.y, anchor.m, 1));
}

function edit(item: Record<string, any>) {
    editing.value = item;
}

function removeEditing() {
    if (!editing.value) {
return;
}

    const i = props.events.indexOf(editing.value);

    if (i !== -1) {
props.events.splice(i, 1);
}

    editing.value = null;
}

function closeEditor() {
    // Drop an event that was added but left completely empty.
    const e = editing.value;

    if (e && !e.title?.en && !e.title?.dv) {
        const i = props.events.indexOf(e);

        if (i !== -1) {
props.events.splice(i, 1);
}
    }

    editing.value = null;
}
</script>

<template>
    <div class="space-y-4">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="rounded-lg border border-border p-2 text-muted-foreground transition hover:bg-muted"
                    title="Previous month"
                    @click="shift(-1)"
                >
                    <ChevronLeft class="size-4" />
                </button>
                <div class="min-w-[13rem] text-center text-sm font-semibold sm:text-base">{{ rangeLabel }}</div>
                <button
                    type="button"
                    class="rounded-lg border border-border p-2 text-muted-foreground transition hover:bg-muted"
                    title="Next month"
                    @click="shift(1)"
                >
                    <ChevronRight class="size-4" />
                </button>
                <button
                    type="button"
                    class="ml-1 rounded-lg border border-border px-3 py-2 text-xs font-semibold text-muted-foreground transition hover:bg-muted"
                    @click="goToday"
                >
                    Today
                </button>

                <div class="ml-2 flex items-center gap-1 rounded-lg border border-border p-0.5" title="Months to show at once">
                    <button
                        v-for="n in [1, 2, 3]"
                        :key="n"
                        type="button"
                        class="rounded-md px-2.5 py-1 text-xs font-semibold transition"
                        :class="monthsToShow === n ? 'bg-brand text-brand-foreground' : 'text-muted-foreground hover:bg-muted'"
                        @click="monthsToShow = n"
                    >
                        {{ n }}
                    </button>
                    <span class="px-1 text-xs text-muted-foreground">mo</span>
                </div>
            </div>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full border border-dashed border-brand/50 px-4 py-2 text-sm font-semibold text-brand transition hover:bg-brand-muted"
                @click="addToday"
            >
                <Plus class="size-4" /> Add event
            </button>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-x-4 gap-y-2">
            <span v-for="t in TYPES" :key="t.value" class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                <span class="size-2.5 rounded-full" :class="t.dot" /> {{ t.label }}
            </span>
        </div>

        <!-- Calendar grid(s) -->
        <div
            class="grid gap-4"
            :class="{
                'lg:grid-cols-2': monthsToShow === 2,
                'lg:grid-cols-2 xl:grid-cols-3': monthsToShow === 3,
            }"
        >
            <div
                v-for="mo in months"
                :key="mo.y + '-' + mo.m"
                class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm"
            >
                <div class="border-b border-border bg-muted/40 px-3 py-2 text-center text-sm font-semibold">
                    {{ mo.label }}
                </div>
                <div class="grid grid-cols-7 border-b border-border bg-muted/20">
                    <div
                        v-for="w in WEEKDAYS"
                        :key="w"
                        class="px-1 py-1.5 text-center text-[11px] font-semibold text-muted-foreground"
                    >
                        {{ w }}
                    </div>
                </div>

                <div v-for="(week, wi) in mo.weeks" :key="wi" class="grid grid-cols-7">
                    <div
                        v-for="cell in week"
                        :key="cell.dateStr"
                        class="group min-h-[5.5rem] border-b border-r border-border p-1 last:border-r-0 transition"
                        :class="cell.inMonth ? 'bg-background' : 'bg-muted/30'"
                    >
                        <div class="mb-1 flex items-center justify-between">
                            <span
                                class="inline-flex size-6 items-center justify-center rounded-full text-xs"
                                :class="[
                                    cell.isToday ? 'bg-brand font-semibold text-brand-foreground' : 'text-muted-foreground',
                                    !cell.inMonth && !cell.isToday ? 'opacity-50' : '',
                                ]"
                            >
                                {{ cell.day }}
                            </span>
                            <button
                                type="button"
                                class="rounded p-0.5 text-muted-foreground opacity-0 transition hover:bg-muted group-hover:opacity-100"
                                title="Add event on this day"
                                @click="addOn(cell.dateStr)"
                            >
                                <Plus class="size-3.5" />
                            </button>
                        </div>

                        <div class="space-y-1">
                            <button
                                v-for="ev in eventsForDay(cell.dateStr)"
                                :key="ev.id"
                                type="button"
                                class="flex w-full items-center gap-1 truncate rounded-md border px-1.5 py-0.5 text-left text-[11px] font-medium transition hover:brightness-95"
                                :class="typeMeta(ev.type).chip"
                                @click="edit(ev)"
                            >
                                <template v-if="cell.dateStr === String(ev.date)">
                                    <span class="truncate">{{ ev.title?.en || ev.title?.dv || 'Untitled' }}</span>
                                </template>
                                <template v-else>
                                    <span class="truncate opacity-70">↳ {{ ev.title?.en || ev.title?.dv || 'Untitled' }}</span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-xs text-muted-foreground">
            Click a day to add an event, or click an existing event to edit it. Multi-day events
            (with an end date) appear on every day they span.
        </p>

        <!-- Editor dialog -->
        <Teleport to="body">
            <div
                v-if="editing"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 backdrop-blur-sm"
                @click.self="closeEditor"
            >
                <div class="mt-16 w-full max-w-lg rounded-2xl border border-border bg-background p-6 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Event details</h3>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted"
                            title="Close"
                            @click="closeEditor"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <div class="grid gap-4">
                        <SchemaField v-for="f in editFields" :key="f.key" :obj="editing" :field="f" />
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:hover:bg-red-900/30"
                            @click="removeEditing"
                        >
                            <Trash2 class="size-4" /> Delete
                        </button>
                        <button
                            type="button"
                            class="rounded-lg bg-brand px-5 py-2 text-sm font-semibold text-brand-foreground transition hover:brightness-95"
                            @click="closeEditor"
                        >
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
