import { messages } from '@/i18n/messages';

export interface NavMenuItem {
    id: number;
    label: { en: string; dv: string };
    href: string;
    children?: NavMenuItem[];
}

/**
 * The built-in site menu, used as the starting point in the CMS editor and as
 * the fallback when no custom navigation has been published yet.
 */
export function defaultNavigation(): NavMenuItem[] {
    const n = messages.nav;

    return [
        { id: 1, label: { ...n.home }, href: '/', children: [] },
        {
            id: 2,
            label: { ...n.calendars },
            href: '',
            children: [
                { id: 21, label: { ...n.academicCalendar }, href: '/academic-calendar' },
                { id: 22, label: { ...n.activityCalendar }, href: '/activities' },
                { id: 23, label: { ...n.classCalendar }, href: '/timetable' },
            ],
        },
        { id: 3, label: { ...n.news }, href: '/news', children: [] },
        { id: 4, label: { ...n.announcements }, href: '/announcements', children: [] },
        { id: 6, label: { ...n.downloads }, href: '/downloads', children: [] },
        { id: 7, label: { ...n.orgChart }, href: '/team', children: [] },
        { id: 8, label: { ...n.contact }, href: '/contact', children: [] },
    ];
}

/** Common internal destinations offered as suggestions in the link field. */
export const navLinkSuggestions = [
    '/',
    '/academic-calendar',
    '/activities',
    '/timetable',
    '/news',
    '/announcements',
    '/voices',
    '/downloads',
    '/team',
    '/contact',
];
