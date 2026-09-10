import type { Bilingual, TimetableSlot } from '@/lib/sampleData';

export interface TimetableDay {
    day: Bilingual;
    slots: TimetableSlot[];
}

/** One class within a grade — "A", "B", "A1". */
export interface TimetableClass {
    name: string;
    days: TimetableDay[];
}

export interface TimetableGrade {
    name: string;
    classes: TimetableClass[];
}

export interface Timetable {
    grades: TimetableGrade[];
}

/**
 * The shape this section used before grades gained per-class timetables: a flat
 * list of grade names plus one set of days shared by all of them.
 */
interface LegacyTimetable {
    grades?: unknown;
    days?: unknown;
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}

function cloneDays(days: TimetableDay[]): TimetableDay[] {
    return JSON.parse(JSON.stringify(days)) as TimetableDay[];
}

/**
 * Coerce a stored value into the current timetable shape.
 *
 * Content saved before per-class timetables existed keeps working: each grade
 * name becomes a grade holding a single unnamed class, carrying the timetable
 * that used to be shared across every grade. Anything unrecognisable falls back
 * to the bundled sample so the page still renders.
 */
export function normalizeTimetable(value: unknown): Timetable {
    if (!isRecord(value) || !Array.isArray(value.grades)) {
        return sampleTimetable();
    }

    const legacy = value as LegacyTimetable;
    const legacyDays = Array.isArray(legacy.days)
        ? (legacy.days as TimetableDay[])
        : [];

    const grades = (value.grades as unknown[]).flatMap<TimetableGrade>(
        (grade) => {
            // Legacy: the grade was just its name, with days held one level up.
            if (typeof grade === 'string') {
                return [
                    {
                        name: grade,
                        classes: [{ name: '', days: cloneDays(legacyDays) }],
                    },
                ];
            }

            if (!isRecord(grade)) {
                return [];
            }

            const name = typeof grade.name === 'string' ? grade.name : '';
            const classes = Array.isArray(grade.classes)
                ? (grade.classes as unknown[]).flatMap<TimetableClass>(
                      (cls) => {
                          if (!isRecord(cls)) {
                              return [];
                          }

                          return [
                              {
                                  name:
                                      typeof cls.name === 'string'
                                          ? cls.name
                                          : '',
                                  days: Array.isArray(cls.days)
                                      ? (cls.days as TimetableDay[])
                                      : [],
                              },
                          ];
                      },
                  )
                : [];

            return [
                {
                    name,
                    // A grade always has at least one class, so the public page
                    // never has to special-case an empty class picker.
                    classes: classes.length
                        ? classes
                        : [{ name: '', days: cloneDays(legacyDays) }],
                },
            ];
        },
    );

    return grades.length ? { grades } : sampleTimetable();
}

/* ------------------------------------------------------- bundled sample data */

const SAMPLE_DAYS: [string, string][] = [
    ['Sunday', 'އާދިއްތަ'],
    ['Monday', 'ހޯމަ'],
    ['Tuesday', 'އަންގާރަ'],
    ['Wednesday', 'ބުދަ'],
    ['Thursday', 'ބުރާސްފަތި'],
];

const SAMPLE_TIMES = ['08:00', '08:45', '09:30', '10:45', '11:30', '12:15'];

const SAMPLE_SUBJECTS: [string, string][] = [
    ['Mathematics', 'ހިސާބު'],
    ['Dhivehi', 'ދިވެހި'],
    ['English', 'އިނގިރޭސި'],
    ['Science', 'ސައިންސް'],
    ['Islam', 'އިސްލާމް'],
    ['Social Studies', 'އިޖުތިމާޢީ'],
    ['ICT', 'އައިސީޓީ'],
    ['Art', 'ކުރެހުން'],
    ['PE', 'ކުޅިވަރު'],
    ['Quran', 'ޤުރުއާން'],
    ['Business Studies', 'ވިޔަފާރި'],
    ['Accounting', 'އެކައުންޓިން'],
];

const SAMPLE_TEACHERS: [string, string][] = [
    ['Mr. Nashid', 'ނާޝިދު'],
    ['Ms. Shifa', 'ޝިފާ'],
    ['Ms. Fathmath', 'ފާތިމަތު'],
    ['Mr. Iyaz', 'އިޔާޒް'],
    ['Mr. Hassan', 'ޙަސަން'],
    ['Mr. Adam', 'އާދަމް'],
    ['Ms. Reena', 'ރީނާ'],
    ['Ms. Leena', 'ލީނާ'],
    ['Mr. Zaid', 'ޒައިދު'],
    ['Ms. Aminath', 'އާމިނަތު'],
    ['Mr. Ibrahim', 'އިބްރާހީމް'],
    ['Ms. Mariyam', 'މަރިޔަމް'],
];

/** Class names per grade, so the sample shows the A / B / A1 style in use. */
function sampleClassNames(gradeNumber: number): string[] {
    if (gradeNumber >= 11) {
        return ['A1', 'B1'];
    }

    if (gradeNumber >= 8) {
        return ['A', 'B', 'A1'];
    }

    return ['A', 'B'];
}

/**
 * Deterministic index into a pool. The sample has to be stable across renders
 * — a real random number would reshuffle the timetable on every keystroke — so
 * this mixes the coordinates into a repeatable value instead.
 */
function pick<T>(pool: T[], ...coords: number[]): T {
    const mixed = coords.reduce((acc, n) => acc * 31 + n + 7, 17);

    return pool[Math.abs(mixed) % pool.length];
}

function sampleDays(gradeNumber: number, classIndex: number): TimetableDay[] {
    return SAMPLE_DAYS.map(([en, dv], dayIndex) => ({
        day: { en, dv },
        slots: SAMPLE_TIMES.map((time, periodIndex) => {
            const [subjectEn, subjectDv] = pick(
                SAMPLE_SUBJECTS,
                gradeNumber,
                classIndex,
                dayIndex,
                periodIndex,
            );
            const [teacherEn, teacherDv] = pick(
                SAMPLE_TEACHERS,
                periodIndex,
                dayIndex,
                classIndex,
                gradeNumber,
            );

            return {
                time,
                subject: { en: subjectEn, dv: subjectDv },
                teacher: { en: teacherEn, dv: teacherDv },
            };
        }),
    }));
}

/**
 * The bundled sample, shown until the section is filled in: grades 1–12, each
 * with two or three classes carrying their own week of periods.
 */
export function sampleTimetable(): Timetable {
    return {
        grades: Array.from({ length: 12 }, (_, i) => {
            const gradeNumber = i + 1;

            return {
                name: `Grade ${gradeNumber}`,
                classes: sampleClassNames(gradeNumber).map((name, ci) => ({
                    name,
                    days: sampleDays(gradeNumber, ci),
                })),
            };
        }),
    };
}

/** Label for a grade + class pair, e.g. "Grade 8 A" or just "Grade 8". */
export function classLabel(grade: string, className: string): string {
    return className.trim() ? `${grade} ${className}`.trim() : grade;
}

export function emptySlot(): TimetableSlot {
    return {
        time: '',
        subject: { en: '', dv: '' },
        teacher: { en: '', dv: '' },
    };
}

export function emptyDay(): TimetableDay {
    return { day: { en: '', dv: '' }, slots: [] };
}
