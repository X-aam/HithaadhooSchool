/**
 * UI string translations for the bilingual school site.
 * `en` = English (LTR), `dv` = Dhivehi / Thaana (RTL).
 *
 * Content (news, announcements, events, etc.) lives separately in
 * `@/lib/sampleData` so staff-entered content is decoupled from UI labels.
 */
export type Locale = 'en' | 'dv';

export interface LocalizedText {
    en: string;
    dv: string;
}

export const messages = {
    site: {
        name: { en: 'Hithaadhoo School', dv: 'ބ. ހިތާދޫ ސްކޫލް' },
        tagline: {
            en: 'Building a generation for a successful life.',
            dv: 'ކާމިޔާބު ދިރިއުޅުމަކަށް ޖީލެއް ބިނާކުރުން.',
        },
    },
    nav: {
        home: { en: 'Home', dv: 'މައި ޞަފްޙާ' },
        academicCalendar: {
            en: 'Academic Calendar',
            dv: 'ކިޔެވުމުގެ ކަލަންޑަރ',
        },
        activityCalendar: { en: 'Activities', dv: 'ހަރަކާތްތައް' },
        classCalendar: { en: 'Class Timetable', dv: 'ކްލާސް ތާވަލު' },
        orgChart: { en: 'Management', dv: 'މެނޭޖްމަންޓް' },
        announcements: { en: 'Announcements', dv: 'އިޢުލާންތައް' },
        news: { en: 'News & Blog', dv: 'ޚަބަރާއި ލިޔުންތައް' },
        publishing: { en: "Writers' Corner", dv: 'ލިޔުންތެރިންގެ ކަންމަތި' },
        downloads: { en: 'Downloads', dv: 'ޑައުންލޯޑްތައް' },
        contact: { en: 'Contact', dv: 'ގުޅުއްވާ' },
        calendars: { en: 'Calendars', dv: 'ކަލަންޑަރތައް' },
    },
    common: {
        readMore: { en: 'Read more', dv: 'އިތުރަށް ބައްލަވާ' },
        viewAll: { en: 'View all', dv: 'ހުރިހާ ބައްލަވާ' },
        search: { en: 'Search', dv: 'ހޯއްދަވާ' },
        searchPlaceholder: {
            en: 'Search news, downloads, announcements…',
            dv: 'ޚަބަރު، ޑައުންލޯޑް، އިޢުލާން ހޯއްދަވާ…',
        },
        download: { en: 'Download', dv: 'ޑައުންލޯޑް' },
        filterByCategory: {
            en: 'Filter by category',
            dv: 'ބައި އިޚްތިޔާރުކުރައްވާ',
        },
        all: { en: 'All', dv: 'ހުރިހާ' },
        by: { en: 'By', dv: 'ލިޔުނީ' },
        loadMore: { en: 'Load more', dv: 'އިތުރަށް ދައްކާ' },
        noResults: {
            en: 'Nothing found.',
            dv: 'އެއްވެސް ނަތީޖާއެއް ނުފެނުނު.',
        },
        translationMissing: {
            en: 'Translation coming soon.',
            dv: 'ދިވެހި ތަރުޖަމާ ވަރަށް އަވަހަށް ލިބޭނެ.',
        },
        close: { en: 'Close', dv: 'ބަންދުކުރައްވާ' },
        details: { en: 'Details', dv: 'ތަފްޞީލު' },
        pinned: { en: 'Pinned', dv: 'ޕިން ކުރެވިފައި' },
        important: { en: 'Important', dv: 'މުހިންމު' },
        menu: { en: 'Menu', dv: 'މެނޫ' },
        switchLanguage: { en: 'ދިވެހި', dv: 'English' },
    },
    home: {
        welcomeKicker: { en: 'Welcome to', dv: 'މަރުޙަބާ' },
        principalTitle: {
            en: 'A message from our Principal',
            dv: 'ޕްރިންސިޕަލްގެ ބަސް',
        },
        motto: { en: 'Our Motto', dv: 'ޝިޢާރު' },
        missionTitle: { en: 'Our Mission', dv: 'އަޅުގަނޑުމެންގެ އަމާޒު' },
        visionTitle: { en: 'Our Vision', dv: 'އަޅުގަނޑުމެންގެ ތަޞައްވުރު' },
        quickLinks: { en: 'Quick links', dv: 'ފަސޭހަ ލިންކުތައް' },
        featuredNews: { en: 'Latest news', dv: 'އެންމެ ފަހުގެ ޚަބަރު' },
        latestAnnouncements: {
            en: 'Latest announcements',
            dv: 'އެންމެ ފަހުގެ އިޢުލާން',
        },
        applyNow: { en: 'Apply now', dv: 'ފޯމު ހުށަހަޅުއްވާ' },
    },
    calendar: {
        month: { en: 'Month', dv: 'މަސް' },
        year: { en: 'Year', dv: 'އަހަރު' },
        list: { en: 'List', dv: 'ލިސްޓު' },
        grid: { en: 'Grid', dv: 'ގްރިޑް' },
        today: { en: 'Today', dv: 'މިއަދު' },
        noEvents: {
            en: 'No events in this period.',
            dv: 'މި މުއްދަތުގައި އިވެންޓެއް ނެތް.',
        },
        allTerms: { en: 'All terms', dv: 'ހުރިހާ ޓާމް' },
        term1: { en: 'Term 1', dv: 'ފުރަތަމަ ޓާމް' },
        term2: { en: 'Term 2', dv: 'ދެވަނަ ޓާމް' },
        term3: { en: 'Term 3', dv: 'ތިންވަނަ ޓާމް' },
        exportPdf: { en: 'Download PDF', dv: 'ޕީޑީއެފް ޑައުންލޯޑް' },
        time: { en: 'Time', dv: 'ވަގުތު' },
        location: { en: 'Location', dv: 'ތަން' },
        selectGrade: { en: 'Grade', dv: 'ގްރޭޑް' },
        selectClass: { en: 'Class', dv: 'ކްލާސް' },
    },
    contact: {
        title: { en: 'Get in touch', dv: 'ގުޅުއްވުމަށް' },
        name: { en: 'Full name', dv: 'ފުރިހަމަ ނަން' },
        email: { en: 'Email address', dv: 'އީމެއިލް' },
        subject: { en: 'Subject', dv: 'މައުޟޫޢު' },
        message: { en: 'Message', dv: 'މެސެޖު' },
        send: { en: 'Send message', dv: 'މެސެޖު ފޮނުއްވާ' },
        sending: { en: 'Sending…', dv: 'ފޮނުވަނީ…' },
        sent: {
            en: 'Thank you! We will reply shortly.',
            dv: 'ޝުކުރިއްޔާ! އަޅުގަނޑުމެން ވަރަށް އަވަހަށް ޖަވާބުދޭނަން.',
        },
        address: { en: 'Address', dv: 'އެޑްރެސް' },
        phone: { en: 'Phone', dv: 'ފޯނު' },
        officeHours: { en: 'Office hours', dv: 'އޮފީސް ގަޑިތައް' },
        followUs: { en: 'Follow us', dv: 'ފޮލޯކުރައްވާ' },
        required: {
            en: 'This field is required.',
            dv: 'މި ގޮޅި ފުރިހަމަކުރައްވާ.',
        },
        invalidEmail: {
            en: 'Please enter a valid email.',
            dv: 'ސައްޙަ އީމެއިލެއް ލިޔުއްވާ.',
        },
    },
    publishing: {
        intro: {
            en: 'Creative writing, essays and ideas from our students and teachers.',
            dv: 'ދަރިވަރުންނާއި މުދައްރިސުންގެ އުފެއްދުންތެރި ލިޔުންތަކާއި ޚިޔާލުތައް.',
        },
        submit: { en: 'Submit your work', dv: 'ލިޔުން ހުށަހަޅުއްވާ' },
        student: { en: 'Student', dv: 'ދަރިވަރު' },
        teacher: { en: 'Teacher', dv: 'މުދައްރިސް' },
    },
    footer: {
        quickLinks: { en: 'Quick links', dv: 'ފަސޭހަ ލިންކުތައް' },
        contactUs: { en: 'Contact us', dv: 'ގުޅުއްވުމަށް' },
        newsletter: { en: 'Newsletter', dv: 'ނިއުސްލެޓަރ' },
        newsletterText: {
            en: 'Get school news in your inbox.',
            dv: 'ސްކޫލްގެ ޚަބަރު އީމެއިލުން ލިބިވަޑައިގަންނަވާ.',
        },
        subscribe: { en: 'Subscribe', dv: 'ސަބްސްކްރައިބް' },
        rights: {
            en: 'All rights reserved.',
            dv: 'ހުރިހާ ޙައްޤުތައް ރައްކާތެރިކުރެވިފައި.',
        },
    },
} as const;
