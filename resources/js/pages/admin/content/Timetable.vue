<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronUp,
    Copy,
    GraduationCap,
    Plus,
    Trash2,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ContentEditorShell from '@/components/admin/ContentEditorShell.vue';
import type { TimetableSlot } from '@/lib/sampleData';
import type { Timetable } from '@/lib/timetable';
import {
    classLabel,
    emptyDay,
    emptySlot,
    normalizeTimetable,
} from '@/lib/timetable';

const props = defineProps<{ section: string; label: string; value: unknown }>();

const form = useForm<{ value: Timetable }>({
    value: normalizeTimetable(props.value),
});

const gradeIndex = ref(0);
const classIndex = ref(0);

const grades = computed(() => form.value.grades);
const grade = computed(() => grades.value[gradeIndex.value]);
const classes = computed(() => grade.value?.classes ?? []);
const activeClass = computed(() => classes.value[classIndex.value]);

/**
 * Every class in the timetable other than the one being edited, as copy
 * sources. Duplicating a sibling class is the common case: streams A and B
 * usually share most of their week.
 */
const copySources = computed(() =>
    grades.value.flatMap((g, gi) =>
        g.classes
            .map((c, ci) => ({
                gi,
                ci,
                label: classLabel(g.name, c.name),
                days: c.days.length,
            }))
            .filter(
                (entry) =>
                    !(
                        entry.gi === gradeIndex.value &&
                        entry.ci === classIndex.value
                    ) && entry.days > 0,
            ),
    ),
);

const copyFrom = ref('');

// Keep the selection valid as grades and classes are added and removed.
watch(
    grades,
    () => {
        gradeIndex.value = Math.min(
            gradeIndex.value,
            Math.max(grades.value.length - 1, 0),
        );
    },
    { deep: true },
);

watch([gradeIndex, classes], () => {
    classIndex.value = Math.min(
        classIndex.value,
        Math.max(classes.value.length - 1, 0),
    );
});

function addGrade() {
    form.value.grades.push({
        name: `Grade ${form.value.grades.length + 1}`,
        classes: [{ name: 'A', days: [] }],
    });
    gradeIndex.value = form.value.grades.length - 1;
    classIndex.value = 0;
}

function removeGrade(i: number) {
    if (!confirm(`Remove "${grades.value[i]?.name}" and all of its classes?`)) {
        return;
    }

    form.value.grades.splice(i, 1);
}

function moveGrade(i: number, delta: number) {
    const target = i + delta;

    if (target < 0 || target >= form.value.grades.length) {
        return;
    }

    const [moved] = form.value.grades.splice(i, 1);
    form.value.grades.splice(target, 0, moved);
    gradeIndex.value = target;
}

function addClass() {
    if (!grade.value) {
        return;
    }

    // Suggest the next letter: A, B, C…
    const next = String.fromCharCode(65 + grade.value.classes.length);

    grade.value.classes.push({ name: next, days: [] });
    classIndex.value = grade.value.classes.length - 1;
}

function removeClass(i: number) {
    if (!grade.value || grade.value.classes.length === 1) {
        alert('A grade needs at least one class.');

        return;
    }

    if (
        !confirm(
            `Remove class "${grade.value.classes[i]?.name}" and its timetable?`,
        )
    ) {
        return;
    }

    grade.value.classes.splice(i, 1);
}

function addDay() {
    activeClass.value?.days.push(emptyDay());
}

function removeDay(i: number) {
    activeClass.value?.days.splice(i, 1);
}

function movePeriod(slots: TimetableSlot[], i: number, delta: number) {
    const target = i + delta;

    if (target < 0 || target >= slots.length) {
        return;
    }

    const [moved] = slots.splice(i, 1);
    slots.splice(target, 0, moved);
}

/**
 * Copy one day's periods onto every other day of this class. Most timetables
 * repeat the same period times all week, so this saves retyping them per day.
 */
function applyDayToWeek(source: number) {
    const days = activeClass.value?.days;
    const template = days?.[source]?.slots;

    if (!days || !template || days.length < 2) {
        return;
    }

    if (
        !confirm(
            'Replace the periods on every other day of this class with these?',
        )
    ) {
        return;
    }

    days.forEach((day, i) => {
        if (i !== source) {
            day.slots = JSON.parse(JSON.stringify(template)) as TimetableSlot[];
        }
    });
}

function moveDay(i: number, delta: number) {
    const days = activeClass.value?.days;
    const target = i + delta;

    if (!days || target < 0 || target >= days.length) {
        return;
    }

    const [moved] = days.splice(i, 1);
    days.splice(target, 0, moved);
}

/** Add a Sunday–Thursday week in one go, so a new class isn't built by hand. */
function addSchoolWeek() {
    const week: [string, string][] = [
        ['Sunday', 'އާދިއްތަ'],
        ['Monday', 'ހޯމަ'],
        ['Tuesday', 'އަންގާރަ'],
        ['Wednesday', 'ބުދަ'],
        ['Thursday', 'ބުރާސްފަތި'],
    ];

    if (!activeClass.value) {
        return;
    }

    for (const [en, dv] of week) {
        activeClass.value.days.push({ day: { en, dv }, slots: [] });
    }
}

function applyCopy() {
    if (!copyFrom.value || !activeClass.value) {
        return;
    }

    const [gi, ci] = copyFrom.value.split(':').map(Number);
    const source = form.value.grades[gi]?.classes[ci];

    if (!source) {
        return;
    }

    if (
        activeClass.value.days.length &&
        !confirm(
            'This replaces the timetable for the class you are editing. Continue?',
        )
    ) {
        return;
    }

    activeClass.value.days = JSON.parse(
        JSON.stringify(source.days),
    ) as typeof source.days;
    copyFrom.value = '';
}

function save() {
    form.put(`/admin/content/${props.section}`, { preserveScroll: true });
}
</script>

<template>
    <ContentEditorShell
        :section="section"
        :label="label"
        :processing="form.processing"
        @save="save"
    >
        <div class="grid gap-5 lg:grid-cols-[260px_1fr]">
            <!-- Grades and classes -->
            <aside class="space-y-3">
                <section
                    class="rounded-2xl border border-border bg-background p-4 shadow-sm"
                >
                    <h2
                        class="mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        Grades
                    </h2>

                    <ul class="space-y-1">
                        <li
                            v-for="(g, i) in grades"
                            :key="i"
                            class="flex items-center gap-1 rounded-lg pr-1 transition"
                            :class="
                                gradeIndex === i
                                    ? 'bg-brand-muted'
                                    : 'hover:bg-muted'
                            "
                        >
                            <button
                                type="button"
                                class="flex min-w-0 flex-1 items-center gap-2 px-2 py-2 text-left text-sm"
                                @click="((gradeIndex = i), (classIndex = 0))"
                            >
                                <GraduationCap
                                    class="size-4 shrink-0"
                                    :class="
                                        gradeIndex === i
                                            ? 'text-brand'
                                            : 'text-muted-foreground'
                                    "
                                />
                                <span
                                    class="truncate"
                                    :class="
                                        gradeIndex === i
                                            ? 'font-semibold text-brand'
                                            : ''
                                    "
                                >
                                    {{ g.name || 'Untitled grade' }}
                                </span>
                                <span
                                    class="ml-auto shrink-0 text-xs text-muted-foreground"
                                >
                                    {{ g.classes.length }}
                                </span>
                            </button>
                            <button
                                type="button"
                                class="rounded p-1 text-muted-foreground transition hover:text-foreground disabled:opacity-30"
                                :disabled="i === 0"
                                title="Move up"
                                @click="moveGrade(i, -1)"
                            >
                                <ChevronUp class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                class="rounded p-1 text-muted-foreground transition hover:text-foreground disabled:opacity-30"
                                :disabled="i === grades.length - 1"
                                title="Move down"
                                @click="moveGrade(i, 1)"
                            >
                                <ChevronDown class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                class="rounded p-1 text-muted-foreground transition hover:text-red-600"
                                title="Remove grade"
                                @click="removeGrade(i)"
                            >
                                <Trash2 class="size-3.5" />
                            </button>
                        </li>
                    </ul>

                    <button
                        type="button"
                        class="mt-3 inline-flex w-full items-center justify-center gap-1 rounded-lg border border-dashed border-brand/50 px-3 py-2 text-sm font-medium text-brand transition hover:bg-brand-muted"
                        @click="addGrade"
                    >
                        <Plus class="size-3.5" /> Add grade
                    </button>
                </section>

                <section
                    v-if="grade"
                    class="rounded-2xl border border-border bg-background p-4 shadow-sm"
                >
                    <h2
                        class="mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        Grade name
                    </h2>
                    <input
                        v-model="grade.name"
                        type="text"
                        placeholder="Grade 8"
                        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                    />

                    <h2
                        class="mt-4 mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        Classes in this grade
                    </h2>
                    <p class="mb-2 text-xs text-muted-foreground">
                        Each class keeps its own timetable. Name them A, B, A1 —
                        whatever the school uses.
                    </p>

                    <div class="space-y-1">
                        <div
                            v-for="(c, i) in classes"
                            :key="i"
                            class="flex items-center gap-1 rounded-lg border px-1.5 py-1 transition"
                            :class="
                                classIndex === i
                                    ? 'border-brand/40 bg-brand-muted'
                                    : 'border-border hover:bg-muted'
                            "
                        >
                            <button
                                type="button"
                                class="shrink-0 rounded px-1.5 text-xs font-semibold"
                                :class="
                                    classIndex === i
                                        ? 'text-brand'
                                        : 'text-muted-foreground'
                                "
                                title="Edit this class"
                                @click="classIndex = i"
                            >
                                {{ classIndex === i ? 'Editing' : 'Edit' }}
                            </button>
                            <input
                                v-model="c.name"
                                type="text"
                                placeholder="A"
                                class="min-w-0 flex-1 bg-transparent px-1 py-1 text-sm outline-none"
                                @focus="classIndex = i"
                            />
                            <span
                                class="shrink-0 text-xs text-muted-foreground"
                            >
                                {{ c.days.length }}d
                            </span>
                            <button
                                type="button"
                                class="shrink-0 rounded p-1 text-muted-foreground transition hover:text-red-600"
                                title="Remove class"
                                @click="removeClass(i)"
                            >
                                <Trash2 class="size-3.5" />
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="mt-2 inline-flex w-full items-center justify-center gap-1 rounded-lg border border-dashed border-brand/50 px-3 py-1.5 text-sm font-medium text-brand transition hover:bg-brand-muted"
                        @click="addClass"
                    >
                        <Plus class="size-3.5" /> Add class
                    </button>
                </section>
            </aside>

            <!-- Timetable for the selected class -->
            <div class="space-y-4">
                <p
                    v-if="!activeClass"
                    class="rounded-2xl border border-dashed border-border py-16 text-center text-sm text-muted-foreground"
                >
                    Add a grade to start building the timetable.
                </p>

                <template v-else>
                    <div
                        class="flex flex-wrap items-center gap-3 rounded-2xl border border-border bg-muted/30 px-4 py-3"
                    >
                        <h2 class="text-sm font-semibold">
                            Timetable for
                            <span class="text-brand">
                                {{
                                    classLabel(
                                        grade?.name ?? '',
                                        activeClass.name,
                                    )
                                }}
                            </span>
                        </h2>

                        <div
                            v-if="copySources.length"
                            class="ml-auto flex items-center gap-2"
                        >
                            <select
                                v-model="copyFrom"
                                class="rounded-lg border border-border bg-background px-2 py-1.5 text-xs outline-none focus:border-brand"
                            >
                                <option value="">Copy timetable from…</option>
                                <option
                                    v-for="s in copySources"
                                    :key="`${s.gi}:${s.ci}`"
                                    :value="`${s.gi}:${s.ci}`"
                                >
                                    {{ s.label }}
                                </option>
                            </select>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-border bg-background px-2.5 py-1.5 text-xs font-medium transition hover:bg-muted disabled:opacity-40"
                                :disabled="!copyFrom"
                                @click="applyCopy"
                            >
                                <Copy class="size-3.5" /> Copy
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="!activeClass.days.length"
                        class="rounded-2xl border border-dashed border-border py-12 text-center"
                    >
                        <p class="mb-3 text-sm text-muted-foreground">
                            No days yet for this class.
                        </p>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2 text-sm font-semibold text-brand-foreground transition hover:brightness-110"
                            @click="addSchoolWeek"
                        >
                            <Plus class="size-4" /> Add Sunday–Thursday
                        </button>
                    </div>

                    <div
                        v-for="(day, i) in activeClass.days"
                        :key="i"
                        class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm"
                    >
                        <!-- Day header: both language names on one compact row -->
                        <div
                            class="flex flex-wrap items-center gap-2 border-b border-border bg-muted/40 px-3 py-2"
                        >
                            <input
                                v-model="day.day.en"
                                type="text"
                                placeholder="Sunday"
                                aria-label="Day name (English)"
                                class="w-36 rounded-lg border border-border bg-background px-2.5 py-1.5 text-sm font-semibold transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                            />
                            <input
                                v-model="day.day.dv"
                                type="text"
                                dir="rtl"
                                placeholder="އާދިއްތަ"
                                aria-label="Day name (Dhivehi)"
                                class="w-36 rounded-lg border border-border bg-background px-2.5 py-1.5 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                            />
                            <span class="text-xs text-muted-foreground">
                                {{ day.slots.length }}
                                {{
                                    day.slots.length === 1
                                        ? 'period'
                                        : 'periods'
                                }}
                            </span>
                            <div class="ml-auto flex items-center gap-1">
                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted disabled:opacity-30"
                                    :disabled="i === 0"
                                    title="Move day up"
                                    @click="moveDay(i, -1)"
                                >
                                    <ChevronUp class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted disabled:opacity-30"
                                    :disabled="
                                        i === activeClass.days.length - 1
                                    "
                                    title="Move day down"
                                    @click="moveDay(i, 1)"
                                >
                                    <ChevronDown class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30"
                                    title="Remove day"
                                    @click="removeDay(i)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Periods as a grid: one row per period -->
                        <div class="overflow-x-auto">
                            <table
                                class="w-full min-w-max border-collapse text-sm"
                            >
                                <thead>
                                    <tr class="text-xs text-muted-foreground">
                                        <th
                                            class="px-2 py-2 text-left font-medium"
                                        >
                                            Time
                                        </th>
                                        <th
                                            class="px-2 py-2 text-left font-medium"
                                        >
                                            Subject
                                        </th>
                                        <th
                                            class="px-2 py-2 text-right font-medium"
                                        >
                                            ދިވެހި
                                        </th>
                                        <th
                                            class="px-2 py-2 text-left font-medium"
                                        >
                                            Teacher
                                        </th>
                                        <th
                                            class="px-2 py-2 text-right font-medium"
                                        >
                                            ދިވެހި
                                        </th>
                                        <th class="w-px px-2 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(slot, s) in day.slots"
                                        :key="s"
                                        class="border-t border-border"
                                    >
                                        <td class="px-2 py-1.5">
                                            <input
                                                v-model="slot.time"
                                                type="text"
                                                placeholder="08:00"
                                                dir="ltr"
                                                aria-label="Time"
                                                class="w-20 rounded-md border border-border bg-background px-2 py-1.5 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                                            />
                                        </td>
                                        <td class="px-2 py-1.5">
                                            <input
                                                v-model="slot.subject.en"
                                                type="text"
                                                placeholder="Mathematics"
                                                aria-label="Subject (English)"
                                                class="w-44 rounded-md border border-border bg-background px-2 py-1.5 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                                            />
                                        </td>
                                        <td class="px-2 py-1.5">
                                            <input
                                                v-model="slot.subject.dv"
                                                type="text"
                                                dir="rtl"
                                                placeholder="ހިސާބު"
                                                aria-label="Subject (Dhivehi)"
                                                class="w-40 rounded-md border border-border bg-background px-2 py-1.5 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                                            />
                                        </td>
                                        <td class="px-2 py-1.5">
                                            <input
                                                v-model="slot.teacher.en"
                                                type="text"
                                                placeholder="Mr. Nashid"
                                                aria-label="Teacher (English)"
                                                class="w-44 rounded-md border border-border bg-background px-2 py-1.5 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                                            />
                                        </td>
                                        <td class="px-2 py-1.5">
                                            <input
                                                v-model="slot.teacher.dv"
                                                type="text"
                                                dir="rtl"
                                                placeholder="ނާޝިދު"
                                                aria-label="Teacher (Dhivehi)"
                                                class="w-40 rounded-md border border-border bg-background px-2 py-1.5 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
                                            />
                                        </td>
                                        <td class="px-2 py-1.5">
                                            <div
                                                class="flex items-center gap-0.5"
                                            >
                                                <button
                                                    type="button"
                                                    class="rounded p-1 text-muted-foreground transition hover:bg-muted disabled:opacity-30"
                                                    :disabled="s === 0"
                                                    title="Move period up"
                                                    @click="
                                                        movePeriod(
                                                            day.slots,
                                                            s,
                                                            -1,
                                                        )
                                                    "
                                                >
                                                    <ChevronUp
                                                        class="size-3.5"
                                                    />
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded p-1 text-muted-foreground transition hover:bg-muted disabled:opacity-30"
                                                    :disabled="
                                                        s ===
                                                        day.slots.length - 1
                                                    "
                                                    title="Move period down"
                                                    @click="
                                                        movePeriod(
                                                            day.slots,
                                                            s,
                                                            1,
                                                        )
                                                    "
                                                >
                                                    <ChevronDown
                                                        class="size-3.5"
                                                    />
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded p-1 text-muted-foreground transition hover:text-red-600"
                                                    title="Remove period"
                                                    @click="
                                                        day.slots.splice(s, 1)
                                                    "
                                                >
                                                    <Trash2 class="size-3.5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr
                                        v-if="!day.slots.length"
                                        class="border-t border-border"
                                    >
                                        <td
                                            colspan="6"
                                            class="px-3 py-3 text-center text-xs text-muted-foreground"
                                        >
                                            No periods yet.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-border px-3 py-2">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-brand/50 px-3 py-1.5 text-xs font-semibold text-brand transition hover:bg-brand-muted"
                                @click="day.slots.push(emptySlot())"
                            >
                                <Plus class="size-3.5" /> Add period
                            </button>
                            <button
                                v-if="day.slots.length"
                                type="button"
                                class="ml-1.5 inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-foreground/70 transition hover:bg-muted"
                                title="Copy this day's periods to every other day in this class"
                                @click="applyDayToWeek(i)"
                            >
                                <Copy class="size-3.5" /> Apply to all days
                            </button>
                        </div>
                    </div>

                    <button
                        v-if="activeClass.days.length"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-dashed border-brand/50 px-5 py-2.5 text-sm font-semibold text-brand transition hover:bg-brand-muted"
                        @click="addDay"
                    >
                        <Plus class="size-4" /> Add day
                    </button>
                </template>
            </div>
        </div>
    </ContentEditorShell>
</template>
