import type { Locale } from '@/i18n/messages';

/** A piece of text available in one or both languages. */
export type Bilingual = Partial<Record<Locale, string>>;

/* Placeholder imagery is curated and topically related (island life, island
 * schools, classrooms) so pages look right until real Hithaadhoo photos are
 * added. Every asset can be swapped for the school's real photos later
 * without touching layout code:
 *   - images.unsplash.com → curated campus / classroom / article photos
 *   - pravatar.cc         → diverse staff & author headshots
 */
const unsplash = (id: string, w = 800, h = 500) =>
    `https://images.unsplash.com/${id}?auto=format&fit=crop&w=${w}&h=${h}&q=80`;

/** Curated, verified photo ids that match the site's island-school themes. */
const stockIds = {
    islandAerial: 'photo-1514282401047-d79a71a590e8',
    beachPalm: 'photo-1507525428034-b723cf961d3e',
    sandbank: 'photo-1540202404-a2f29016b523',
    seaplane: 'photo-1512100356356-de1b84283e18',
    classroomKids: 'photo-1509062522246-3755977927d7',
    classroom: 'photo-1580582932707-520aed937b7b',
    loveToLearn: 'photo-1546410531-bb4caa6b424d',
    studentsGroup: 'photo-1522202176988-66273c2fd55f',
    scienceLab: 'photo-1532094349884-543bc11b234d',
    library: 'photo-1521587760476-6c12a4b040da',
    books: 'photo-1497633762265-9d179a990aa6',
    seedling: 'photo-1542601906990-b4d3fb778b09',
    swimmer: 'photo-1530549387789-4c1017266635',
    stage: 'photo-1514320291840-2e0a9bf2a9ae',
    football: 'photo-1579952363873-27f3bade9f55',
    kidsPainting: 'photo-1503454537195-1dcabb73ffb9',
    snorkel: 'photo-1544551763-46a013bb70d5',
} as const;
type StockName = keyof typeof stockIds;

export const img = (name: StockName, w = 800, h = 500) =>
    unsplash(stockIds[name], w, h);
export const avatar = (n: number) => `https://i.pravatar.cc/240?img=${n}`;

/* ---------------------------------------------------------------- School */

export const school = {
    established: 1951,
    students: 240,
    teachers: 48,
    motto: {
        en: 'Heuristic Swot',
        dv: 'ހިއުރިސްޓިކް ސްވޮޓް',
    } satisfies Bilingual,
    welcome: {
        en: 'On behalf of our whole community, welcome to Hithaadhoo School. On 17 April 1951 the people of Hithaadhoo worked and sacrificed to establish this school, and ever since we have grown generations of confident, competent and responsible islanders who uphold Islamic principles and values — many of whom serve the nation today in its highest posts. We warmly invite you to explore our school and become part of our family.',
        dv: 'އަޅުގަނޑުމެންގެ މުޅި މުޖުތަމަޢުގެ ފަރާތުން، ބ. ހިތާދޫ ސްކޫލަށް މަރުޙަބާ. 1951 ވަނަ އަހަރު ހިތާދޫގެ ރައްޔިތުންގެ ބުރަ މަސައްކަތުން މި ސްކޫލް ޤާއިމުކުރެވުނު ފަހުން، އިސްލާމީ ރިވެތި އުސޫލުތަކުގައި ހިފަހައްޓާ، ޤާބިލު، ޒިންމާދާރު ދަރިންތަކެއް މި ސްކޫލުން ދަނީ އުފެދެމުން. އޭގެ ތެރެއިން ގިނަ ދަރިވަރުން މިއަދު ޤައުމުގެ މަތީ މަޤާމުތަކުގައި ޚިދުމަތްކުރައްވާކަމީ އަޅުގަނޑުމެންގެ ފަޚުރެއް. އަޅުގަނޑުމެންގެ ސްކޫލު ބައްލަވާލައި، މި އާއިލާގެ ބައެއްގެ ގޮތުގައި ވެލެއްވުމަށް ދަޢުވަތު އަރުވަން.',
    } satisfies Bilingual,
    mission: {
        en: 'Produce self-confident, competent, productive and responsible citizens who uphold Islamic principles and values in life.',
        dv: 'އިސްލާމީ ރިވެތި އުޞޫލުތަކާއި އަގުތަކުގައި ހިފަހައްޓާ، އަމިއްލަ ނަފްސަށް އިތުބާރުކުރާ، ޤާބިލު، އުފެއްދުންތެރި އަދި ޒިންމާދާރު ރައްޔިތުންތަކެއް ބިނާކުރުން.',
    } satisfies Bilingual,
    vision: {
        en: 'Building a generation for a successful life.',
        dv: 'ކާމިޔާބު ދިރިއުޅުމަކަށް ޖީލެއް ބިނާކުރުން.',
    } satisfies Bilingual,
    principal: {
        name: {
            en: 'Aishath Rifga',
            dv: 'ޢާއިޝަތު ރިފްޤާ',
        } satisfies Bilingual,
        title: { en: 'Principal', dv: 'ޕްރިންސިޕަލް' } satisfies Bilingual,
        photo: avatar(5),
    },
    contact: {
        address: {
            en: 'Hithaadhoo School, B. Hithaadhoo 06100, Maldives',
            dv: 'ބ. ހިތާދޫ ސްކޫލް، ބ. ހިތާދޫ 06100، ދިވެހިރާއްޖެ',
        } satisfies Bilingual,
        phone: '+960 6600314',
        email: 'admin@hithaadhooschool.edu.mv',
        officeHours: {
            en: 'Sunday – Thursday, 7:30 AM – 3:00 PM',
            dv: 'އާދިއްތަ – ބުރާސްފަތި، ހެނދުނު 7:30 – މެންދުރު 3:00',
        } satisfies Bilingual,
        // B. Hithaadhoo, Baa Atoll
        mapLat: 5.1214,
        mapLng: 73.0736,
        social: {
            facebook:
                'https://www.facebook.com/p/B-Hithaadhoo-School-100035972058029/',
            instagram: 'https://instagram.com',
            youtube: 'https://youtube.com',
            x: 'https://x.com/B_hithaadhoo',
        },
    },
};

/**
 * Resolve a school photo. Drop real photos into `public/images/` using the
 * given file name and they appear automatically; until then the page falls
 * back to a stable, license-free placeholder (see the `onImgError` helper
 * used in the templates).
 */
export const photo = (
    file: string,
    fallback: StockName,
    w = 1600,
    h = 900,
) => ({
    image: `/images/${file}`,
    fallback: img(fallback, w, h),
});

export const heroSlides: {
    image: string;
    fallback: string;
    title: Bilingual;
    subtitle: Bilingual;
}[] = [
    {
        ...photo('hero-1.jpg', 'islandAerial', 1600, 900),
        title: {
            en: 'Welcome to Hithaadhoo School',
            dv: 'ބ. ހިތާދޫ ސްކޫލަށް މަރުޙަބާ',
        },
        subtitle: {
            en: 'Building a generation for a successful life since 1951',
            dv: '1951 ން ފެށިގެން ކާމިޔާބު ދިރިއުޅުމަކަށް ޖީލެއް ބިނާކުރަނީ',
        },
    },
    {
        ...photo('hero-2.jpg', 'classroomKids', 1600, 900),
        title: {
            en: 'Learning that inspires',
            dv: 'ހިތްވަރުދޭ ތަޢުލީމު',
        },
        subtitle: {
            en: 'Dedicated teachers, curious young minds',
            dv: 'ފަންވަރު ހުރި މުދައްރިސުން، އުނގެނުމަށް ފޯރި ހުރި ދަރިވަރުން',
        },
    },
    {
        ...photo('hero-3.jpg', 'beachPalm', 1600, 900),
        title: {
            en: 'More than a classroom',
            dv: 'ކްލާސްރޫމަށްވުރެ ފުޅާ',
        },
        subtitle: {
            en: 'Sports, arts, clubs and lifelong friendships',
            dv: 'ކުޅިވަރު، ފަންނު، ކްލަބުތައް އަދި ދާއިމީ އެކުވެރިކަން',
        },
    },
];

/* ---------------------------------------------------------- Announcements */

export type AnnouncementCategory =
    'academic' | 'events' | 'emergency' | 'general';

export interface Announcement {
    id: number;
    category: AnnouncementCategory;
    pinned: boolean;
    date: string;
    title: Bilingual;
    body: Bilingual;
}

export const announcements: Announcement[] = [
    {
        id: 1,
        category: 'academic',
        pinned: true,
        date: '2026-06-28',
        title: {
            en: 'Term 3 Exam Timetable Released',
            dv: 'ތިންވަނަ ޓާމުގެ އިމްތިޙާން ތާވަލު އާންމުކޮށްފި',
        },
        body: {
            en: 'The final examination timetable for Term 3 is now available on the Downloads page. Exams begin on 20 July. Please review the schedule with your child.',
            dv: 'ތިންވަނަ ޓާމުގެ ފައިނަލް އިމްތިޙާން ތާވަލު މިހާރު ޑައުންލޯޑް ޞަފްޙާއިން ލިބިވަޑައިގަންނަވާނެ. އިމްތިޙާން ފެށޭނީ ޖުލައި 20 ގައި.',
        },
    },
    {
        id: 2,
        category: 'general',
        pinned: false,
        date: '2026-06-22',
        title: {
            en: 'School Uniform Policy Update',
            dv: 'ސްކޫލް ޔުނިފޯމް ސިޔާސަތު އަޕްޑޭޓް',
        },
        body: {
            en: 'From the new term, house-coloured PE shirts will be worn on activity days. Details are in the updated policy document.',
            dv: 'އައު ޓާމުން ފެށިގެން، ހަރަކާތުގެ ދުވަސްތަކުގައި ހައުސް ކުލައިގެ ޕީއީ ގަމީސް ބޭނުންކުރައްވަން ޖެހޭނެ.',
        },
    },
    {
        id: 3,
        category: 'events',
        pinned: false,
        date: '2026-06-15',
        title: {
            en: 'Annual Sports Day — Save the Date',
            dv: 'އަހަރީ ކުޅިވަރު ދުވަސް — ތާރީޚު ފާހަގަކުރައްވާ',
        },
        body: {
            en: 'Our Annual Sports Day will be held on 8 August at the island football ground. Families are warmly invited to cheer on the houses.',
            dv: 'އަހަރީ ކުޅިވަރު ދުވަސް އޮގަސްޓް 8 ގައި ރަށު ފުޓްބޯޅަ ދަނޡުގައި ބޭއްވޭނެ. އާއިލާތަކަށް ދަޢުވަތު އަރުވަން.',
        },
    },
    {
        id: 4,
        category: 'academic',
        pinned: false,
        date: '2026-06-01',
        title: {
            en: 'School Reopening Date Confirmed',
            dv: 'ސްކޫލް އަލުން ހުޅުވޭ ތާރީޚު ކަށަވަރުވެއްޖެ',
        },
        body: {
            en: 'School reopens for all grades on Sunday, 3 August 2026. The office will be open from 28 July for enquiries.',
            dv: 'ހުރިހާ ގްރޭޑްތަކަށް ސްކޫލް އަލުން ހުޅުވޭނީ 2026 އޮގަސްޓް 3، އާދިއްތަ ދުވަހު.',
        },
    },
    {
        id: 5,
        category: 'emergency',
        pinned: false,
        date: '2026-05-24',
        title: {
            en: 'Early Closure Due to Weather',
            dv: 'މޫސުމް ގޯސްވުމުން އަވަހަށް ބަންދުކުރުން',
        },
        body: {
            en: 'Owing to the weather advisory, school closed at 12:00 PM today. All students were dismissed safely. Normal hours resume tomorrow.',
            dv: 'މޫސުމް ގޯސްވުމުގެ ސަބަބުން މިއަދު 12:00 ގައި ސްކޫލް ބަންދުކުރެވިއްޖެ. މާދަމާ އާދައިގެ ގަޑިތަކުގައި ކިޔެވުން އޮންނާނެ.',
        },
    },
];

/* ------------------------------------------------------- Academic events */

export interface AcademicEvent {
    id: number;
    date: string;
    endDate?: string;
    term: 1 | 2 | 3;
    type: 'term' | 'exam' | 'holiday' | 'meeting' | 'event';
    title: Bilingual;
}

export const academicEvents: AcademicEvent[] = [
    {
        id: 1,
        date: '2026-01-01',
        term: 1,
        type: 'event',
        title: { en: 'New Year 2026', dv: 'އައު އަހަރު 2026' },
    },
    {
        id: 2,
        date: '2026-01-25',
        term: 1,
        type: 'meeting',
        title: {
            en: "Teachers' Reporting Day 2026",
            dv: 'މުދައްރިސުން ހާޒިރުވާ ދުވަސް 2026',
        },
    },
    {
        id: 3,
        date: '2026-01-27',
        term: 1,
        type: 'term',
        title: {
            en: 'Beginning of Academic Year 2026',
            dv: 'ދިރާސީ އަހަރު ފެށުން 2026',
        },
    },
    {
        id: 4,
        date: '2026-02-05',
        term: 1,
        type: 'event',
        title: {
            en: "Opening of the People's Majlis",
            dv: 'ރައްޔިތުންގެ މަޖިލިސް ހުޅުވުން',
        },
    },
    {
        id: 5,
        date: '2026-02-18',
        term: 1,
        type: 'event',
        title: {
            en: 'First of Ramadan',
            dv: 'ރަމަޟާން މަހުގެ ފުރަތަމަ ދުވަސް',
        },
    },
    {
        id: 6,
        date: '2026-03-01',
        endDate: '2026-03-08',
        term: 1,
        type: 'meeting',
        title: {
            en: 'Professional Development Days',
            dv: 'ޕްރޮފެޝަނަލް ޑިވެލޮޕްމަންޓް ދުވަސްތައް',
        },
    },
    {
        id: 7,
        date: '2026-03-09',
        endDate: '2026-03-19',
        term: 1,
        type: 'holiday',
        title: {
            en: 'Last 10 days of Ramadan',
            dv: 'ރަމަޟާން މަހުގެ ފަހު ދިހަ',
        },
    },
    {
        id: 8,
        date: '2026-03-20',
        term: 1,
        type: 'event',
        title: { en: 'Eid-al-Fitr', dv: 'ފިޠުރު ޢީދު' },
    },
    {
        id: 9,
        date: '2026-03-21',
        endDate: '2026-03-22',
        term: 1,
        type: 'event',
        title: {
            en: 'On the occasion of Eid-al-Fitr',
            dv: 'ފިޠުރު ޢީދާ ގުޅިގެން',
        },
    },
    {
        id: 10,
        date: '2026-04-05',
        endDate: '2026-04-14',
        term: 1,
        type: 'exam',
        title: {
            en: 'Grade 11 and 12 First Term Exam',
            dv: 'ގްރޭޑް 11 އަދި 12 ފުރަތަމަ ޓާމް އިމްތިޙާން',
        },
    },
    {
        id: 11,
        date: '2026-05-01',
        term: 1,
        type: 'event',
        title: { en: 'Labour Day', dv: 'މަސައްކަތްތެރިންގެ ދުވަސް' },
    },
    {
        id: 12,
        date: '2026-05-10',
        term: 1,
        type: 'event',
        title: { en: "Children's Day", dv: 'ކުޑަކުދިންގެ ދުވަސް' },
    },
    {
        id: 13,
        date: '2026-05-17',
        endDate: '2026-06-15',
        term: 1,
        type: 'meeting',
        title: {
            en: 'School Transfer Period 1',
            dv: 'ސްކޫލް ބަދަލުކުރުމުގެ މުއްދަތު 1',
        },
    },
    {
        id: 14,
        date: '2026-05-24',
        endDate: '2026-05-30',
        term: 1,
        type: 'holiday',
        title: { en: 'First Term Mid-Break', dv: 'ފުރަތަމަ ޓާމް މެދު ބަންދު' },
    },
    {
        id: 15,
        date: '2026-05-26',
        term: 1,
        type: 'event',
        title: { en: 'Hajj Day', dv: 'ޙައްޖު ދުވަސް' },
    },
    {
        id: 16,
        date: '2026-05-27',
        term: 1,
        type: 'event',
        title: { en: 'Eid-al-Adha', dv: 'އަޟްޙާ ޢީދު' },
    },
    {
        id: 17,
        date: '2026-05-28',
        endDate: '2026-05-30',
        term: 1,
        type: 'event',
        title: {
            en: 'On the occasion of Eid-al-Adha',
            dv: 'އަޟްޙާ ޢީދާ ގުޅިގެން',
        },
    },
    {
        id: 18,
        date: '2026-06-07',
        term: 1,
        type: 'term',
        title: {
            en: 'Beginning of AL Batch 2026',
            dv: 'އޭ.އެލް ބެޗް 2026 ފެށުން',
        },
    },
    {
        id: 19,
        date: '2026-06-16',
        term: 1,
        type: 'event',
        title: { en: 'Islamic New Year 1448', dv: 'އިސްލާމީ އައު އަހަރު 1448' },
    },
    {
        id: 20,
        date: '2026-06-30',
        endDate: '2026-07-09',
        term: 1,
        type: 'exam',
        title: { en: 'First Term Exam', dv: 'ފުރަތަމަ ޓާމް އިމްތިޙާން' },
    },
    {
        id: 21,
        date: '2026-07-09',
        endDate: '2026-08-31',
        term: 1,
        type: 'meeting',
        title: {
            en: 'New Admission - LKG & Gr. 1',
            dv: 'އައު އެޑްމިޝަން - އެލްކޭޖީ އަދި ގްރޭޑް 1',
        },
    },
    {
        id: 22,
        date: '2026-07-16',
        term: 1,
        type: 'term',
        title: {
            en: 'End of First Term 2026',
            dv: 'ފުރަތަމަ ޓާމް ނިމުން 2026',
        },
    },
    {
        id: 23,
        date: '2026-07-17',
        endDate: '2026-08-01',
        term: 1,
        type: 'holiday',
        title: { en: 'First Term Holidays', dv: 'ފުރަތަމަ ޓާމް ބަންދު' },
    },
    {
        id: 24,
        date: '2026-07-26',
        term: 1,
        type: 'event',
        title: { en: 'Independence Day', dv: 'މިނިވަން ދުވަސް' },
    },
    {
        id: 25,
        date: '2026-07-27',
        term: 1,
        type: 'event',
        title: {
            en: 'On the occasion of Independence Day',
            dv: 'މިނިވަން ދުވަހާ ގުޅިގެން',
        },
    },
    {
        id: 26,
        date: '2026-08-02',
        term: 2,
        type: 'term',
        title: {
            en: 'Beginning of Second Term 2026',
            dv: 'ދެވަނަ ޓާމް ފެށުން 2026',
        },
    },
    {
        id: 27,
        date: '2026-08-14',
        term: 2,
        type: 'event',
        title: { en: 'National Day', dv: 'ޤައުމީ ދުވަސް' },
    },
    {
        id: 28,
        date: '2026-08-22',
        endDate: '2026-08-25',
        term: 2,
        type: 'meeting',
        title: {
            en: 'Camps and Activities (KS1 - KS4)',
            dv: 'ކޭމްޕް އަދި ހަރަކާތްތައް (KS1 - KS4)',
        },
    },
    {
        id: 29,
        date: '2026-08-23',
        endDate: '2026-09-10',
        term: 2,
        type: 'exam',
        title: {
            en: 'Grade 11 and 12 Final Exam',
            dv: 'ގްރޭޑް 11 އަދި 12 ފައިނަލް އިމްތިޙާން',
        },
    },
    {
        id: 30,
        date: '2026-08-25',
        term: 2,
        type: 'event',
        title: {
            en: "Prophet Muhammad's (ﷺ) Birthday",
            dv: 'ރަސޫލާ ﷺ ގެ އީދު މީލާދު',
        },
    },
    {
        id: 31,
        date: '2026-09-01',
        endDate: '2026-09-10',
        term: 2,
        type: 'exam',
        title: { en: 'Grade 10 Mock Exam', dv: 'ގްރޭޑް 10 މޮކް އިމްތިޙާން' },
    },
    {
        id: 32,
        date: '2026-09-13',
        term: 2,
        type: 'event',
        title: {
            en: 'The Day Maldives Embraced Islam',
            dv: 'ދިވެހިރާއްޖެ އިސްލާމްވި ދުވަސް',
        },
    },
    {
        id: 33,
        date: '2026-09-13',
        endDate: '2026-09-19',
        term: 2,
        type: 'holiday',
        title: { en: 'Second Term Mid-Break', dv: 'ދެވަނަ ޓާމް މެދު ބަންދު' },
    },
    {
        id: 34,
        date: '2026-10-05',
        term: 2,
        type: 'event',
        title: { en: "Teachers' Day", dv: 'މުދައްރިސުންގެ ދުވަސް' },
    },
    {
        id: 35,
        date: '2026-10-18',
        endDate: '2026-11-17',
        term: 2,
        type: 'meeting',
        title: {
            en: 'School Transfer Period 2',
            dv: 'ސްކޫލް ބަދަލުކުރުމުގެ މުއްދަތު 2',
        },
    },
    {
        id: 36,
        date: '2026-11-03',
        term: 2,
        type: 'event',
        title: { en: 'Victory Day', dv: 'ނަޞްރުގެ ދުވަސް' },
    },
    {
        id: 37,
        date: '2026-11-11',
        term: 2,
        type: 'event',
        title: { en: 'Republic Day', dv: 'ޖުމްހޫރީ ދުވަސް' },
    },
    {
        id: 38,
        date: '2026-11-12',
        term: 2,
        type: 'meeting',
        title: {
            en: 'Professional Development Day',
            dv: 'ޕްރޮފެޝަނަލް ޑިވެލޮޕްމަންޓް ދުވަސް',
        },
    },
    {
        id: 39,
        date: '2026-12-01',
        endDate: '2026-12-10',
        term: 2,
        type: 'exam',
        title: { en: 'Second Term Exam', dv: 'ދެވަނަ ޓާމް އިމްތިޙާން' },
    },
    {
        id: 40,
        date: '2026-12-01',
        endDate: '2026-12-10',
        term: 2,
        type: 'exam',
        title: {
            en: 'First Term Exam (Gr. 11, 2026 Batch)',
            dv: 'ފުރަތަމަ ޓާމް އިމްތިޙާން (ގްރޭޑް 11، 2026 ބެޗް)',
        },
    },
    {
        id: 41,
        date: '2026-12-17',
        term: 2,
        type: 'term',
        title: { en: 'End of Second Term 2026', dv: 'ދެވަނަ ޓާމް ނިމުން 2026' },
    },
    {
        id: 42,
        date: '2026-12-18',
        endDate: '2027-01-12',
        term: 2,
        type: 'holiday',
        title: { en: 'Second Term Holidays', dv: 'ދެވަނަ ޓާމް ބަންދު' },
    },
];

/* ------------------------------------------------------- Activity events */

export type ActivityCategory = 'sports' | 'arts' | 'clubs' | 'trips';

export interface ActivityEvent {
    id: number;
    date: string;
    time: string;
    category: ActivityCategory;
    title: Bilingual;
    location: Bilingual;
    description: Bilingual;
}

export const activityEvents: ActivityEvent[] = [
    {
        id: 1,
        date: '2026-08-05',
        time: '15:30 – 17:00',
        category: 'sports',
        title: {
            en: 'Football Practice — U15',
            dv: 'ފުޓްބޯޅަ ފަރިތަކުރުން — U15',
        },
        location: { en: 'School Ground', dv: 'ސްކޫލް ދަނޑު' },
        description: {
            en: 'Weekly training session for the under-15 squad. Bring water and boots.',
            dv: 'U15 ޓީމުގެ ހަފްތާގެ ފަރިތަކުރުން. ފެނާއި ބޫޓު ގެންނަވާ.',
        },
    },
    {
        id: 2,
        date: '2026-08-07',
        time: '14:00 – 15:30',
        category: 'clubs',
        title: { en: 'Debate Club', dv: 'ބަހުސް ކްލަބް' },
        location: { en: 'Library Hall', dv: 'ލައިބްރަރީ ހޯލް' },
        description: {
            en: 'This week: “Technology in classrooms does more good than harm.”',
            dv: 'މި ހަފްތާ: "ކްލާސްރޫމުގައި ޓެކްނޮލޮޖީ ބޭނުންކުރުމުން ފައިދާ ބޮޑު."',
        },
    },
    {
        id: 3,
        date: '2026-08-12',
        time: '09:00 – 12:00',
        category: 'arts',
        title: { en: 'Art Exhibition', dv: 'އަތްތެރި މަސައްކަތުގެ މައުރަޒު' },
        location: { en: 'Main Hall', dv: 'މައި ހޯލް' },
        description: {
            en: 'Student artwork on display. Parents and visitors welcome all morning.',
            dv: 'ދަރިވަރުންގެ ކުރެހުންތައް ދައްކާލުން. ބެލެނިވެރިންނަށް މަރުޙަބާ.',
        },
    },
    {
        id: 4,
        date: '2026-08-19',
        time: '08:00 – 16:00',
        category: 'trips',
        title: {
            en: 'Grade 9 Field Trip — Hanifaru Bay',
            dv: 'ގްރޭޑް 9 ދަތުރު — ހަނިފަރު ބޭ',
        },
        location: {
            en: 'Hanifaru Bay, Baa Atoll',
            dv: 'ހަނިފަރު ބޭ، ބ. އަތޮޅު',
        },
        description: {
            en: 'A guided visit to the UNESCO Biosphere Reserve to learn about manta rays and reef ecology. Packed lunch required.',
            dv: 'ޔޫނެސްކޯ ބައޯސްފިއރ ރިޒާވްގެ ސަރަޙައްދަށް ކުރާ ދަތުރެއް. އެންމަޑިއާއި ފަރުގެ ދިރުންތައް ދެނެގަތުމަށް. ކެއުން ގެންނަވަން ޖެހޭ.',
        },
    },
    {
        id: 5,
        date: '2026-08-22',
        time: '15:00 – 16:30',
        category: 'clubs',
        title: {
            en: 'Science & Robotics Club',
            dv: 'ސައިންސް އަދި ރޮބޮޓިކްސް ކްލަބް',
        },
        location: { en: 'STEM Lab', dv: 'ސްޓެމް ލެބް' },
        description: {
            en: 'Building line-following robots ahead of the regional challenge.',
            dv: 'ސަރަޙައްދީ މުބާރާތަށް ތައްޔާރުވުމަށް ރޮބޮޓް ބިނާކުރުން.',
        },
    },
    {
        id: 6,
        date: '2026-08-26',
        time: '16:00 – 17:30',
        category: 'sports',
        title: {
            en: 'Inter-House Swimming Gala',
            dv: 'ހައުސްތަކުގެ ފެތުމުގެ މުބާރާތް',
        },
        location: {
            en: 'Island Swimming Area (Moodhu)',
            dv: 'ރަށު މޫދު ފެތޭ ސަރަޙައްދު',
        },
        description: {
            en: 'Heats across all age groups. Come support your house!',
            dv: 'ހުރިހާ އުމުރުފުރައެއްގެ ފެތުން. ތިޔަ ހައުސްއަށް ހިތްވަރުދެއްވާ!',
        },
    },
    {
        id: 101,
        date: '2025-01-15',
        time: '14:00 – 15:30',
        category: 'clubs',
        title: { en: 'Debate Club', dv: 'ބަހުސް ކްލަބް' },
        location: { en: 'Library Hall', dv: 'ލައިބްރަރީ ހޯލް' },
        description: {
            en: 'Season opener with a friendly inter-class debate.',
            dv: 'ސީޒަން ފެށުމުގެ ގޮތުން ކްލާސްތަކުގެ މެދުގައި ބަހުސް.',
        },
    },
    {
        id: 102,
        date: '2025-02-20',
        time: '15:00 – 17:00',
        category: 'sports',
        title: {
            en: 'Inter-House Basketball',
            dv: 'ހައުސްތަކުގެ ބާސްކެޓްބޯޅަ',
        },
        location: { en: 'School Court', dv: 'ސްކޫލް ކޯޓު' },
        description: {
            en: 'Group-stage matches across all four houses.',
            dv: 'ހަތަރު ހައުސްގެ މެދުގައި ގްރޫޕް ސްޓޭޖް މެޗުތައް.',
        },
    },
    {
        id: 103,
        date: '2025-03-12',
        time: '19:30 – 21:00',
        category: 'arts',
        title: { en: 'Drama Night', dv: 'ޑްރާމާ ރޭ' },
        location: { en: 'Main Hall', dv: 'މައި ހޯލް' },
        description: {
            en: 'Student-led plays celebrating local folklore.',
            dv: 'ދަރިވަރުން ހުށަހަޅާ، ދިވެހި ފޯކްލޯރ ފާހަގަކުރާ ޑްރާމާތައް.',
        },
    },
    {
        id: 104,
        date: '2025-04-24',
        time: '08:00 – 14:00',
        category: 'trips',
        title: { en: 'Field Trip — Eydhafushi', dv: 'ދަތުރު — އޭދަފުށި' },
        location: { en: 'Eydhafushi, Baa Atoll', dv: 'އޭދަފުށި، ބ. އަތޮޅު' },
        description: {
            en: 'A day of cultural and heritage learning. Packed lunch required.',
            dv: 'ސަޤާފަތާއި ތަރިކަ ދަސްކުރުމުގެ ދުވަހެއް. ކެއުން ގެންނަވަން ޖެހޭ.',
        },
    },
    {
        id: 105,
        date: '2025-05-16',
        time: '15:30 – 17:30',
        category: 'sports',
        title: { en: 'Annual Athletics Meet', dv: 'އަހަރީ އެތްލެޓިކްސް މީޓް' },
        location: { en: 'School Ground', dv: 'ސްކޫލް ދަނޑު' },
        description: {
            en: 'Track and field finals for all age groups.',
            dv: 'ހުރިހާ އުމުރުފުރައެއްގެ ޓްރެކް އެންޑް ފީލްޑް ފައިނަލްތައް.',
        },
    },
    {
        id: 106,
        date: '2025-08-14',
        time: '14:00 – 16:00',
        category: 'clubs',
        title: {
            en: 'Science & Robotics Club',
            dv: 'ސައިންސް އަދި ރޮބޮޓިކްސް ކްލަބް',
        },
        location: { en: 'STEM Lab', dv: 'ސްޓެމް ލެބް' },
        description: {
            en: 'Hands-on electronics and coding session.',
            dv: 'އިލެކްޓްރޯނިކްސް އަދި ކޯޑިން ފަރިތަކުރުން.',
        },
    },
    {
        id: 107,
        date: '2025-09-27',
        time: '09:00 – 12:00',
        category: 'arts',
        title: { en: 'Cultural Day', dv: 'ސަޤާފީ ދުވަސް' },
        location: { en: 'Main Hall', dv: 'މައި ހޯލް' },
        description: {
            en: 'Boduberu, traditional food and craft stalls.',
            dv: 'ބޮޑުބެރު، ދިވެހި ކެއުން އަދި އަތްތެރި މަސައްކަތުގެ ސްޓޯލްތައް.',
        },
    },
    {
        id: 108,
        date: '2025-11-08',
        time: '07:30 – 10:00',
        category: 'trips',
        title: { en: 'Beach Clean-up', dv: 'ގޮނޑުދޮށް ސާފުކުރުން' },
        location: { en: 'Island Beach', dv: 'ރަށު ގޮނޑުދޮށް' },
        description: {
            en: 'Environment club leads a community clean-up drive.',
            dv: 'ތިމާވެށި ކްލަބުން ކުރިއަށްގެންދާ ސާފުކުރުމުގެ ހަރަކާތެއް.',
        },
    },
];

/* -------------------------------------------------- Class timetable (G8) */

export interface TimetableSlot {
    time: string;
    subject: Bilingual;
    teacher: Bilingual;
}

export const classGrades = [
    'Grade 6',
    'Grade 7',
    'Grade 8',
    'Grade 9',
    'Grade 10',
];

export const timetableDays: { day: Bilingual; slots: TimetableSlot[] }[] = [
    {
        day: { en: 'Sunday', dv: 'އާދިއްތަ' },
        slots: [
            {
                time: '08:00',
                subject: { en: 'Mathematics', dv: 'ހިސާބު' },
                teacher: { en: 'Mr. Nashid', dv: 'ނާޝިދު' },
            },
            {
                time: '09:00',
                subject: { en: 'Dhivehi', dv: 'ދިވެހި' },
                teacher: { en: 'Ms. Shifa', dv: 'ޝިފާ' },
            },
            {
                time: '10:30',
                subject: { en: 'English', dv: 'އިނގިރޭސި' },
                teacher: { en: 'Ms. Fathmath', dv: 'ފާތިމަތު' },
            },
            {
                time: '11:30',
                subject: { en: 'Science', dv: 'ސައިންސް' },
                teacher: { en: 'Mr. Iyaz', dv: 'އިޔާޒް' },
            },
        ],
    },
    {
        day: { en: 'Monday', dv: 'ހޯމަ' },
        slots: [
            {
                time: '08:00',
                subject: { en: 'Islam', dv: 'އިސްލާމް' },
                teacher: { en: 'Mr. Hassan', dv: 'ޙަސަން' },
            },
            {
                time: '09:00',
                subject: { en: 'Science', dv: 'ސައިންސް' },
                teacher: { en: 'Mr. Iyaz', dv: 'އިޔާޒް' },
            },
            {
                time: '10:30',
                subject: { en: 'Mathematics', dv: 'ހިސާބު' },
                teacher: { en: 'Mr. Nashid', dv: 'ނާޝިދު' },
            },
            {
                time: '11:30',
                subject: { en: 'Art', dv: 'ކުރެހުން' },
                teacher: { en: 'Ms. Leena', dv: 'ލީނާ' },
            },
        ],
    },
    {
        day: { en: 'Tuesday', dv: 'އަންގާރަ' },
        slots: [
            {
                time: '08:00',
                subject: { en: 'English', dv: 'އިނގިރޭސި' },
                teacher: { en: 'Ms. Fathmath', dv: 'ފާތިމަތު' },
            },
            {
                time: '09:00',
                subject: { en: 'Social Studies', dv: 'އިޖުތިމާޢީ' },
                teacher: { en: 'Mr. Adam', dv: 'އާދަމް' },
            },
            {
                time: '10:30',
                subject: { en: 'Dhivehi', dv: 'ދިވެހި' },
                teacher: { en: 'Ms. Shifa', dv: 'ޝިފާ' },
            },
            {
                time: '11:30',
                subject: { en: 'PE', dv: 'ކުޅިވަރު' },
                teacher: { en: 'Mr. Zaid', dv: 'ޒައިދު' },
            },
        ],
    },
    {
        day: { en: 'Wednesday', dv: 'ބުދަ' },
        slots: [
            {
                time: '08:00',
                subject: { en: 'Mathematics', dv: 'ހިސާބު' },
                teacher: { en: 'Mr. Nashid', dv: 'ނާޝިދު' },
            },
            {
                time: '09:00',
                subject: { en: 'Science', dv: 'ސައިންސް' },
                teacher: { en: 'Mr. Iyaz', dv: 'އިޔާޒް' },
            },
            {
                time: '10:30',
                subject: { en: 'Islam', dv: 'އިސްލާމް' },
                teacher: { en: 'Mr. Hassan', dv: 'ޙަސަން' },
            },
            {
                time: '11:30',
                subject: { en: 'English', dv: 'އިނގިރޭސި' },
                teacher: { en: 'Ms. Fathmath', dv: 'ފާތިމަތު' },
            },
        ],
    },
    {
        day: { en: 'Thursday', dv: 'ބުރާސްފަތި' },
        slots: [
            {
                time: '08:00',
                subject: { en: 'Dhivehi', dv: 'ދިވެހި' },
                teacher: { en: 'Ms. Shifa', dv: 'ޝިފާ' },
            },
            {
                time: '09:00',
                subject: { en: 'ICT', dv: 'އައިސީޓީ' },
                teacher: { en: 'Ms. Reena', dv: 'ރީނާ' },
            },
            {
                time: '10:30',
                subject: { en: 'Social Studies', dv: 'އިޖުތިމާޢީ' },
                teacher: { en: 'Mr. Adam', dv: 'އާދަމް' },
            },
            {
                time: '11:30',
                subject: { en: 'Assembly', dv: 'އެސެމްބްލީ' },
                teacher: { en: 'Class Teacher', dv: 'ކްލާސް ޓީޗަރ' },
            },
        ],
    },
];

/* ----------------------------------------------------------- Org chart */

export interface StaffNode {
    id: number;
    name: Bilingual;
    title: Bilingual;
    photo: string;
    bio: Bilingual;
    children?: StaffNode[];
}

export const orgChart: StaffNode = {
    id: 1,
    name: school.principal.name,
    title: school.principal.title,
    photo: avatar(5),
    bio: {
        en: 'Leading Hithaadhoo School since 2018 with a focus on inclusive, student-centred learning.',
        dv: '2018 ން ފެށިގެން ބ. ހިތާދޫ ސްކޫލް ހިންގަވަނީ ދަރިވަރުންނަށް އަމާޒުކޮށްގެން.',
    },
    children: [
        {
            id: 2,
            name: { en: 'Ibrahim Waheed', dv: 'އިބްރާހީމް ވަޙީދު' },
            title: {
                en: 'Vice Principal (Academics)',
                dv: 'ވައިސް ޕްރިންސިޕަލް (ކިޔެވުން)',
            },
            photo: avatar(12),
            bio: {
                en: 'Oversees curriculum, assessment and teacher development across all grades.',
                dv: 'ހުރިހާ ގްރޭޑްތަކުގެ މަންހަޖާއި، އިމްތިޙާނާއި، މުދައްރިސުން ތަރައްޤީކުރުން ބަލަހައްޓަވަނީ.',
            },
            children: [
                {
                    id: 5,
                    name: { en: 'Mariyam Zoona', dv: 'މަރިޔަމް ޒޫނާ' },
                    title: {
                        en: 'Head of Sciences',
                        dv: 'ސައިންސް ދާއިރާގެ ވެރިޔާ',
                    },
                    photo: avatar(9),
                    bio: {
                        en: 'Physics teacher and coordinator of the STEM programme.',
                        dv: 'ފިޒިކްސް މުދައްރިސެއް އަދި ސްޓެމް ޕްރޮގްރާމްގެ ކޯޑިނޭޓަރ.',
                    },
                },
                {
                    id: 6,
                    name: { en: 'Ali Shameem', dv: 'ޢަލީ ޝަމީމް' },
                    title: {
                        en: 'Head of Languages',
                        dv: 'ބަހުގެ ދާއިރާގެ ވެރިޔާ',
                    },
                    photo: avatar(15),
                    bio: {
                        en: 'Leads the Dhivehi and English departments.',
                        dv: 'ދިވެހި އަދި އިނގިރޭސި ދާއިރާ ލީޑްކުރައްވަނީ.',
                    },
                },
            ],
        },
        {
            id: 3,
            name: { en: 'Aminath Nasra', dv: 'އާމިނަތު ނަޞްރާ' },
            title: {
                en: 'Vice Principal (Student Affairs)',
                dv: 'ވައިސް ޕްރިންސިޕަލް (ދަރިވަރުންގެ ކަންކަން)',
            },
            photo: avatar(20),
            bio: {
                en: 'Responsible for wellbeing, discipline and extracurricular activities.',
                dv: 'ދަރިވަރުންގެ ދުޅަހެޔޮކަމާއި، އަޚްލާޤާއި، ހަރަކާތްތައް ބަލަހައްޓަވަނީ.',
            },
            children: [
                {
                    id: 7,
                    name: { en: 'Hawwa Leena', dv: 'ޙައްވާ ލީނާ' },
                    title: {
                        en: 'Head of Arts',
                        dv: 'ފަންނުވެރިކަމުގެ ވެރިޔާ',
                    },
                    photo: avatar(24),
                    bio: {
                        en: 'Art and music teacher, coordinator of school events.',
                        dv: 'ކުރެހުމާއި މިއުޒިކް މުދައްރިސެއް، ސްކޫލް ހަރަކާތްތަކުގެ ކޯޑިނޭޓަރ.',
                    },
                },
                {
                    id: 8,
                    name: { en: 'Zaid Mohamed', dv: 'ޒައިދު މުޙައްމަދު' },
                    title: {
                        en: 'Head of Sports',
                        dv: 'ކުޅިވަރު ދާއިރާގެ ވެރިޔާ',
                    },
                    photo: avatar(33),
                    bio: {
                        en: 'PE teacher and coach of the school football team.',
                        dv: 'ކުޅިވަރު މުދައްރިސެއް އަދި ސްކޫލް ފުޓްބޯޅަ ޓީމުގެ ކޯޗު.',
                    },
                },
            ],
        },
        {
            id: 4,
            name: { en: 'Fathimath Rasheedha', dv: 'ފާތިމަތު ރަޝީދާ' },
            title: { en: 'Administration Manager', dv: 'އިދާރީ މެނޭޖަރ' },
            photo: avatar(41),
            bio: {
                en: 'Manages admissions, finance and school operations.',
                dv: 'އެޑްމިޝަން، މާލީ ކަންކަން އަދި ސްކޫލް ހިންގުން ބަލަހައްޓަވަނީ.',
            },
        },
    ],
};

/* ------------------------------------------------------------ Downloads */

export type DownloadCategory = 'forms' | 'policies' | 'syllabi' | 'newsletters';

export interface DownloadItem {
    id: number;
    category: DownloadCategory;
    fileType: 'pdf' | 'docx' | 'xlsx';
    size: string;
    date: string;
    title: Bilingual;
    url?: string;
}

export const downloads: DownloadItem[] = [
    {
        id: 1,
        category: 'forms',
        fileType: 'pdf',
        size: '240 KB',
        date: '2026-06-10',
        title: {
            en: 'Admission Application Form 2026',
            dv: 'އެޑްމިޝަން ފޯމު 2026',
        },
    },
    {
        id: 2,
        category: 'forms',
        fileType: 'docx',
        size: '88 KB',
        date: '2026-05-02',
        title: { en: 'Leave Request Form', dv: 'ޗުއްޓީ އެދޭ ފޯމު' },
    },
    {
        id: 3,
        category: 'policies',
        fileType: 'pdf',
        size: '1.2 MB',
        date: '2026-04-18',
        title: {
            en: 'Child Protection Policy',
            dv: 'ކުޑަކުދިން ރައްކާތެރިކުރުމުގެ ސިޔާސަތު',
        },
    },
    {
        id: 4,
        category: 'policies',
        fileType: 'pdf',
        size: '640 KB',
        date: '2026-04-01',
        title: { en: 'School Uniform Policy', dv: 'ޔުނިފޯމް ސިޔާސަތު' },
    },
    {
        id: 5,
        category: 'syllabi',
        fileType: 'pdf',
        size: '820 KB',
        date: '2026-01-08',
        title: {
            en: 'Grade 10 Syllabus Overview',
            dv: 'ގްރޭޑް 10 މަންހަޖުގެ ޚުލާޞާ',
        },
    },
    {
        id: 6,
        category: 'syllabi',
        fileType: 'xlsx',
        size: '54 KB',
        date: '2026-01-08',
        title: {
            en: 'Term 3 Assessment Weightings',
            dv: 'ތިންވަނަ ޓާމް އިމްތިޙާން ބަރުދަން',
        },
    },
    {
        id: 7,
        category: 'newsletters',
        fileType: 'pdf',
        size: '3.1 MB',
        date: '2026-06-27',
        title: {
            en: 'Hithaadhoo School Newsletter — Term 2',
            dv: 'ހިތާދޫ ސްކޫލް ނިއުސްލެޓަރ — ދެވަނަ ޓާމް',
        },
    },
    {
        id: 8,
        category: 'forms',
        fileType: 'pdf',
        size: '180 KB',
        date: '2026-03-15',
        title: { en: 'Fee Structure 2026', dv: 'ފީ ސްޓްރަކްޗަރ 2026' },
    },
];

/* ------------------------------------------------------------- News/Blog */

export type NewsCategory = 'schoolNews' | 'achievements' | 'events';

export interface NewsArticle {
    id: number;
    slug: string;
    category: NewsCategory;
    date: string;
    image: string;
    author: Bilingual;
    title: Bilingual;
    excerpt: Bilingual;
    body: Bilingual;
}

export const news: NewsArticle[] = [
    {
        id: 1,
        slug: 'regional-science-fair',
        category: 'achievements',
        date: '2026-06-20',
        image: img('scienceLab', 1200, 700),
        author: { en: 'Communications Office', dv: 'ކޮމިއުނިކޭޝަން އޮފީސް' },
        title: {
            en: 'Students Win Baa Atoll Science Fair',
            dv: 'ދަރިވަރުން ބ. އަތޮޅު ސައިންސް ފެއަރ ކާމިޔާބުކޮށްފި',
        },
        excerpt: {
            en: 'Our Grade 9 team took first place with a solar-powered water purifier.',
            dv: 'ގްރޭޑް 9 ޓީމު އިރުގެ ހަކަތައިން ފެން ސާފުކުރާ އާލާތަކުން ފުރަތަމަ ވަނަ ހޯދައިފި.',
        },
        body: {
            en: 'A team of four Grade 9 students earned first place at this year’s Baa Atoll Science Fair in Eydhafushi, impressing judges with a low-cost, solar-powered water purifier designed for island communities. The project began as a class experiment and grew into a working prototype over the term. Congratulations to the team and their mentors!',
            dv: 'ގްރޭޑް 9 ގެ ހަތަރު ދަރިވަރުންގެ ޓީމަކުން މިއަހަރު އޭދަފުށީގައި ބޭއްވުނު ބ. އަތޮޅު ސައިންސް ފެއަރގައި ފުރަތަމަ ވަނަ ހޯދައިފި. ރަށްފުށުގެ މުޖްތަމަޢުތަކަށް އަމާޒުކޮށް، ޚަރަދު ކުޑަ، އިރުގެ ހަކަތައިން ފެން ސާފުކުރާ އާލާތެއް ފަރުމާކޮށްގެން. ޓީމަށާއި މުދައްރިސުންނަށް މަރުޙަބާ!',
        },
    },
    {
        id: 2,
        slug: 'new-library-opens',
        category: 'schoolNews',
        date: '2026-05-30',
        image: img('library', 1200, 700),
        author: { en: 'Aishath Rifga', dv: 'ޢާއިޝަތު ރިފްޤާ' },
        title: {
            en: 'New Library and Reading Garden Opens',
            dv: 'އައު ލައިބްރަރީ އަދި ކިޔެވުމުގެ ބަގީޗާ ހުޅުވައިފި',
        },
        excerpt: {
            en: 'A bright new space with 2,000 books and a shaded outdoor reading garden.',
            dv: '2,000 ފޮތާއެކު އަލިކަން ގަދަ އައު ޖާގައެއް އަދި ބޭރުގައި ކިޔެވުމުގެ ބަގީޗާއެއް.',
        },
        body: {
            en: 'We are delighted to open our newly renovated library, featuring 2,000 titles in Dhivehi and English, a digital catalogue, and a shaded outdoor reading garden. The space was made possible through the generosity of the Hithaadhoo community and parent volunteers.',
            dv: 'ދިވެހި އަދި އިނގިރޭސި ބަހުން 2,000 ފޮތާއެކު، ޑިޖިޓަލް ކެޓަލޮގަކާއި، ބޭރުގައި ކިޔެވުމުގެ ބަގީޗާއަކާއެކު އައު ލައިބްރަރީ ހުޅުވުމަކީ އުފަލެއް. މިއީ ހިތާދޫގެ މުޖްތަމަޢުގެ ދީލަތި އެހީތެރިކަމާއެކު ޙާޞިލުވި ކަމެއް.',
        },
    },
    {
        id: 3,
        slug: 'green-club-tree-planting',
        category: 'events',
        date: '2026-05-12',
        image: img('seedling', 1200, 700),
        author: { en: 'Environment Club', dv: 'ތިމާވެށި ކްލަބް' },
        title: {
            en: 'Environment Club Plants 200 Trees',
            dv: 'ތިމާވެށި ކްލަބުން 200 ގަސް އިންދައިފި',
        },
        excerpt: {
            en: 'Students marked Environment Day by greening the school boundary.',
            dv: 'ދަރިވަރުން ތިމާވެށީގެ ދުވަސް ފާހަގަކުރީ ސްކޫލްގެ ވަށައިގެން ގަސް އިންދައިގެން.',
        },
        body: {
            en: 'To mark World Environment Day, the Environment Club and volunteers planted 200 native trees and shrubs — dhiggaa, midhili and kaani — along the school boundary and the island’s coastal ridge, creating shade and protecting the shoreline. It’s part of our ongoing pledge to care for our island.',
            dv: 'ދުނިޔޭގެ ތިމާވެށީގެ ދުވަސް ފާހަގަކުރުމަށް، ތިމާވެށި ކްލަބުން 200 ގަހާއި ގަސްގަހާގެހި ސްކޫލްގެ ވަށައިގެންނާއި ރަށުގެ ގޮންޑުދޮށުގައި އިންދައިފި. މިއީ ރަށަށް އަޅާލުމުގެ ވަޢުދުގެ ބައެއް.',
        },
    },
    {
        id: 4,
        slug: 'annual-concert',
        category: 'events',
        date: '2026-04-28',
        image: img('stage', 1200, 700),
        author: { en: 'Arts Department', dv: 'ފަންނުވެރިކަމުގެ ދާއިރާ' },
        title: {
            en: 'Annual Cultural Concert Dazzles Families',
            dv: 'އަހަރީ ސަޤާފީ ޝޯ އާއިލާތައް ހައިރާންކޮށްލައިފި',
        },
        excerpt: {
            en: 'An evening of Boduberu, drama and song celebrating island culture.',
            dv: 'ބޮޑުބެރާއި، ޑްރާމާއާއި، ލަވައިން ފުރިގެންވި ސަޤާފީ ރެއެއް.',
        },
        body: {
            en: 'Over 300 students took to the stage for our Annual Cultural Concert, performing Boduberu, traditional drama and choral pieces to a full house. Thank you to every family who joined us to celebrate our island heritage.',
            dv: '300 އަށްވުރެ ގިނަ ދަރިވަރުން އަހަރީ ސަޤާފީ ޝޯގައި ބައިވެރިވެ، ބޮޑުބެރާއި، ސަޤާފީ ޑްރާމާ ހުށަހަޅައިދިނެވެ. ބައިވެރިވެވަޑައިގެންނެވި ހުރިހާ އާއިލާތަކަށް ޝުކުރިއްޔާ.',
        },
    },
];

/* --------------------------------------------- Student & Teacher pieces */

export type PublishingCategory = 'creative' | 'opinion' | 'research' | 'poetry';

export interface PublishedPiece {
    id: number;
    slug: string;
    category: PublishingCategory;
    date: string;
    author: Bilingual;
    role: Bilingual;
    photo: string;
    title: Bilingual;
    excerpt: Bilingual;
    body: Bilingual;
}

export const publishedPieces: PublishedPiece[] = [
    {
        id: 1,
        slug: 'the-tide-remembers',
        category: 'poetry',
        date: '2026-06-18',
        author: { en: 'Yoosuf Ali', dv: 'ޔޫސުފް ޢަލީ' },
        role: { en: 'Student — Grade 10', dv: 'ދަރިވަރު — ގްރޭޑް 10' },
        photo: avatar(52),
        title: { en: 'The Tide Remembers', dv: 'ދިޔަވަރު ހަނދާން ހުރޭ' },
        excerpt: {
            en: 'A poem about growing up beside the reef.',
            dv: 'ފަރުގެ ކައިރީގައި ބޮޑުވުމާ ބެހޭ ޅެމެއް.',
        },
        body: {
            en: 'The tide remembers every footprint / that the morning washed away, / and still it comes back, patient, / to teach the sand to stay…',
            dv: 'ދިޔަވަރު ހަނދާން ހުރޭ ކޮންމެ ފިޔަވަޅެއް / ހެނދުނު ދޮވެލި ކޮންމެ ނިޝާނެއް، / އަދިވެސް އެ އަންނަނީ، ކެތްތެރިކަމާއެކު…',
        },
    },
    {
        id: 2,
        slug: 'why-we-read',
        category: 'opinion',
        date: '2026-06-05',
        author: { en: 'Ms. Fathmath Ibrahim', dv: 'ފާތިމަތު އިބްރާހީމް' },
        role: {
            en: 'Teacher — English Dept.',
            dv: 'މުދައްރިސް — އިނގިރޭސި ދާއިރާ',
        },
        photo: avatar(45),
        title: {
            en: 'Why We Still Need to Read',
            dv: 'އަދިވެސް ފޮތް ކިޔަން ޖެހޭ ސަބަބު',
        },
        excerpt: {
            en: 'In a world of short videos, deep reading matters more than ever.',
            dv: 'ކުރު ވީޑިއޯތަކުގެ ދުނިޔޭގައި، ފުންކޮށް ފޮތް ކިޔުން މާ މުހިންމު.',
        },
        body: {
            en: 'Reading is not just decoding words; it is the slow, deliberate practice of holding another person’s mind inside your own. In an age of endless scrolling, teaching our children to sit with a long text may be the most radical thing we do…',
            dv: 'ފޮތް ކިޔުމަކީ ހަމައެކަނި ބަސްތައް ދެނެގަތުމެއް ނޫން؛ އެއީ އެހެން މީހެއްގެ ވިސްނުން ތިމާގެ ހިތުގައި ބެހެއްޓުމުގެ މަޑުމައިތިރި ފަރިތަކުރުމެއް…',
        },
    },
    {
        id: 3,
        slug: 'plastic-on-our-beaches',
        category: 'research',
        date: '2026-05-20',
        author: { en: 'Aishath Noora', dv: 'ޢާއިޝަތު ނޫރާ' },
        role: { en: 'Student — Grade 9', dv: 'ދަރިވަރު — ގްރޭޑް 9' },
        photo: avatar(48),
        title: {
            en: 'Counting Plastic on Our Beaches',
            dv: 'ގޮނޑުދޮށުގައި ޕްލާސްޓިކް ގުނުން',
        },
        excerpt: {
            en: 'A month-long survey of litter along Hithaadhoo’s shoreline.',
            dv: 'ހިތާދޫ ގޮންޑުދޮށުގައި މަހެއްހާ ދުވަހުގެ ސާރވޭއެއް.',
        },
        body: {
            en: 'Over four weeks, our team surveyed a 500-metre stretch of beach and catalogued more than 1,200 pieces of plastic waste. The most common item was single-use bottle caps. This report proposes three simple actions our community can take…',
            dv: 'ހަތަރު ހަފްތާގެ ތެރޭގައި، އަޅުގަނޑުމެންގެ ޓީމުން 500 މީޓަރުގެ ސަރަޙައްދެއް ބަލައި، 1,200 އަށްވުރެ ގިނަ ޕްލާސްޓިކް ކުނި ގުނިއެވެ…',
        },
    },
    {
        id: 4,
        slug: 'the-lighthouse-keeper',
        category: 'creative',
        date: '2026-05-02',
        author: { en: 'Ibrahim Zayan', dv: 'އިބްރާހީމް ޒަޔާން' },
        role: { en: 'Student — Grade 8', dv: 'ދަރިވަރު — ގްރޭޑް 8' },
        photo: avatar(60),
        title: { en: 'The Lighthouse Keeper', dv: 'ބައްތިގެ ބަލަދުވެރިޔާ' },
        excerpt: {
            en: 'A short story about a keeper who befriends the sea.',
            dv: 'ކަނޑާ އެކުވެރިވާ ބައްތި ބަލަދުވެރިއެއްގެ ކުރު ވާހަކައެއް.',
        },
        body: {
            en: 'Every night, old Kaleyfaanu climbed the winding stairs to light the lamp. And every night, the sea whispered back a story he had never heard before…',
            dv: 'ކޮންމެ ރެއަކު، ދޮށީ ކަލޭފާނު ސިޑިން އަރައި ބައްތި ދިއްލައެވެ. އަދި ކޮންމެ ރެއަކު، ކަނޑު އޭނާއަށް އައު ވާހަކައެއް ކިޔައިދެއެވެ…',
        },
    },
];
