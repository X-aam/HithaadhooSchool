<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useLocale } from '@/i18n/useLocale';
import { Download } from '@/lib/publicIcons';
import { classLabel, normalizeTimetable } from '@/lib/timetable';

const props = defineProps<{ timetable?: unknown }>();

const { t, pick, messages } = useLocale();

const timetable = computed(() => normalizeTimetable(props.timetable));

const gradeIndex = ref(0);
const classIndex = ref(0);

const grades = computed(() => timetable.value.grades);

const grade = computed(() => grades.value[gradeIndex.value] ?? grades.value[0]);

const classes = computed(() => grade.value?.classes ?? []);

const activeClass = computed(
    () => classes.value[classIndex.value] ?? classes.value[0],
);

const heading = computed(() =>
    classLabel(grade.value?.name ?? '', activeClass.value?.name ?? ''),
);

// Class lists differ between grades, so start from the first one on a change.
watch(gradeIndex, () => {
    classIndex.value = 0;
});

const days = computed(() => activeClass.value?.days ?? []);

/** The longest day decides how many period rows the desktop grid needs. */
const periodCount = computed(() =>
    days.value.reduce((most, day) => Math.max(most, day.slots.length), 0),
);

/** Times are taken from whichever day actually has a slot in that row. */
const periodTimes = computed(() =>
    Array.from(
        { length: periodCount.value },
        (_, r) =>
            days.value.find((d) => d.slots[r]?.time)?.slots[r]?.time ?? '',
    ),
);

const BRAND_NAVY: [number, number, number] = [31, 78, 121];

const exporting = ref(false);

/**
 * Download the selected class's week as a PDF, laid out like the desktop grid:
 * one column per day, one row per period.
 *
 * The PDF uses the English names throughout. jsPDF's built-in fonts cover
 * Latin-1 only, so Thaana would come out blank without embedding a Dhivehi
 * font in the bundle.
 */
async function exportPdf() {
    if (exporting.value || !days.value.length) {
        return;
    }

    exporting.value = true;

    try {
        const [{ jsPDF }, { default: autoTable }] = await Promise.all([
            import('jspdf'),
            import('jspdf-autotable'),
        ]);

        const title = heading.value;
        const body = periodTimes.value.map((time, r) => [
            time,
            ...days.value.map((day) => {
                const slot = day.slots[r];

                if (!slot) {
                    return '';
                }

                const subject = slot.subject.en ?? '';
                const teacher = slot.teacher.en ?? '';

                return teacher ? `${subject}\n${teacher}` : subject;
            }),
        ]);

        const doc = new jsPDF({
            orientation: 'landscape',
            unit: 'pt',
            format: 'a4',
        });
        const pageWidth = doc.internal.pageSize.getWidth();

        doc.setFillColor(...BRAND_NAVY);
        doc.rect(30, 30, pageWidth - 60, 40, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(14);
        doc.text(messages.site.name.en.toUpperCase(), pageWidth / 2, 47, {
            align: 'center',
        });
        doc.setFontSize(11);
        doc.setFont('helvetica', 'normal');
        doc.text(`Class Timetable — ${title}`, pageWidth / 2, 62, {
            align: 'center',
        });

        autoTable(doc, {
            startY: 82,
            margin: { left: 30, right: 30 },
            head: [['Time', ...days.value.map((d) => d.day.en ?? '')]],
            body,
            styles: {
                fontSize: 9,
                cellPadding: 6,
                lineColor: [203, 213, 225],
                lineWidth: 0.5,
                valign: 'middle',
            },
            headStyles: {
                fillColor: BRAND_NAVY,
                textColor: [255, 255, 255],
                fontStyle: 'bold',
            },
            columnStyles: {
                0: { cellWidth: 60, fontStyle: 'bold', halign: 'center' },
            },
            alternateRowStyles: { fillColor: [248, 250, 252] },
        });

        const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-');

        doc.save(`timetable-${slug}.pdf`);
    } finally {
        exporting.value = false;
    }
}
</script>

<template>
    <Head :title="t(messages.nav.classCalendar)" />

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-center gap-x-3 gap-y-2">
            <label for="grade" class="text-sm font-medium text-foreground">
                {{ t(messages.calendar.selectGrade) }}
            </label>
            <select
                id="grade"
                v-model.number="gradeIndex"
                class="rounded-full border border-border bg-background px-4 py-2 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
            >
                <option v-for="(g, i) in grades" :key="i" :value="i">
                    {{ g.name }}
                </option>
            </select>

            <label
                for="class"
                class="text-sm font-medium text-foreground sm:ms-3"
            >
                {{ t(messages.calendar.selectClass) }}
            </label>
            <select
                id="class"
                v-model.number="classIndex"
                class="rounded-full border border-border bg-background px-4 py-2 text-sm transition outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
            >
                <option v-for="(c, i) in classes" :key="i" :value="i">
                    {{ c.name || t({ en: 'Main', dv: 'މައި' }) }}
                </option>
            </select>

            <button
                v-if="days.length"
                type="button"
                class="ms-auto inline-flex shrink-0 items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-semibold text-foreground/80 transition hover:border-brand/40 hover:text-brand disabled:opacity-60"
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

        <h1 class="mb-4 text-lg font-semibold text-foreground">
            {{ heading }}
        </h1>

        <p
            v-if="!days.length"
            class="rounded-2xl border border-dashed border-border py-16 text-center text-sm text-muted-foreground"
        >
            {{
                t({
                    en: 'No timetable has been published for this class yet.',
                    dv: 'މި ކްލާހަށް ތާވަލެއް އަދި ޝާއިޢުކޮށްފައި ނުވެއެވެ.',
                })
            }}
        </p>

        <template v-else>
            <!-- Desktop grid -->
            <div
                class="hidden overflow-x-auto rounded-2xl border border-border shadow-sm lg:block"
            >
                <table class="w-full min-w-max border-collapse text-sm">
                    <thead>
                        <tr class="bg-brand text-brand-foreground">
                            <th class="p-3 text-start font-semibold">
                                {{ t(messages.calendar.time) }}
                            </th>
                            <th
                                v-for="(d, i) in days"
                                :key="i"
                                class="p-3 text-start font-semibold"
                                dir="auto"
                            >
                                {{ pick(d.day) }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(time, r) in periodTimes"
                            :key="r"
                            class="border-t border-border odd:bg-muted/30"
                        >
                            <td
                                class="p-3 font-medium whitespace-nowrap text-foreground"
                                dir="ltr"
                            >
                                {{ time }}
                            </td>
                            <td v-for="(d, i) in days" :key="i" class="p-3">
                                <template v-if="d.slots[r]">
                                    <div
                                        class="font-medium text-foreground"
                                        dir="auto"
                                    >
                                        {{ pick(d.slots[r].subject) }}
                                    </div>
                                    <div
                                        class="text-xs text-muted-foreground"
                                        dir="auto"
                                    >
                                        {{ pick(d.slots[r].teacher) }}
                                    </div>
                                </template>
                                <span v-else class="text-muted-foreground"
                                    >—</span
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile stacked -->
            <div class="space-y-4 lg:hidden">
                <div
                    v-for="(d, i) in days"
                    :key="i"
                    class="overflow-hidden rounded-2xl border border-border shadow-sm"
                >
                    <div
                        class="bg-brand px-4 py-2.5 font-semibold text-brand-foreground"
                        dir="auto"
                    >
                        {{ pick(d.day) }}
                    </div>
                    <ul class="divide-y divide-border">
                        <li
                            v-for="(slot, s) in d.slots"
                            :key="s"
                            class="flex items-center gap-3 p-3"
                        >
                            <span
                                class="w-14 shrink-0 text-xs font-medium text-muted-foreground"
                                dir="ltr"
                            >
                                {{ slot.time }}
                            </span>
                            <div class="min-w-0">
                                <div
                                    class="font-medium text-foreground"
                                    dir="auto"
                                >
                                    {{ pick(slot.subject) }}
                                </div>
                                <div
                                    class="text-xs text-muted-foreground"
                                    dir="auto"
                                >
                                    {{ pick(slot.teacher) }}
                                </div>
                            </div>
                        </li>
                        <li
                            v-if="!d.slots.length"
                            class="p-3 text-xs text-muted-foreground"
                        >
                            {{ t({ en: 'No periods.', dv: 'ގަޑިއެއް ނެތް.' }) }}
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </div>
</template>
