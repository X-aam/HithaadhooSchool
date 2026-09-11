import { computed, ref } from 'vue';
import { messages } from '@/i18n/messages';
import type { Locale, LocalizedText } from '@/i18n/messages';

const STORAGE_KEY = 'gvs_locale';
const COOKIE_KEY = 'gvs_locale';

function readInitialLocale(): Locale {
    if (typeof window === 'undefined') {
        return 'en';
    }

    const fromStorage = window.localStorage.getItem(STORAGE_KEY);

    if (fromStorage === 'en' || fromStorage === 'dv') {
        return fromStorage;
    }

    const cookieMatch = document.cookie.match(
        new RegExp(`${COOKIE_KEY}=(en|dv)`),
    );

    if (cookieMatch) {
        return cookieMatch[1] as Locale;
    }

    return 'en';
}

// Module-level shared state so every component reads the same locale.
const locale = ref<Locale>(readInitialLocale());

// Explicit Dhivehi names — the `dv-MV` Intl locale is unavailable in most
// browsers and silently falls back to English month/weekday labels.
const DV_MONTHS_LONG = [
    'ޖެނުއަރީ',
    'ފެބްރުއަރީ',
    'މާރިޗު',
    'އޭޕްރީލް',
    'މޭ',
    'ޖޫން',
    'ޖުލައި',
    'އޯގަސްޓު',
    'ސެޕްޓެމްބަރު',
    'އޮކްޓޫބަރު',
    'ނޮވެމްބަރު',
    'ޑިސެންބަރު',
];
const DV_MONTHS_SHORT = [
    'ޖެނު',
    'ފެބް',
    'މާރި',
    'އޭޕް',
    'މޭ',
    'ޖޫން',
    'ޖުލަ',
    'އޯގަ',
    'ސެޕް',
    'އޮކް',
    'ނޮވެ',
    'ޑިސެ',
];
const DV_WEEKDAYS_LONG = [
    'އާދިއްތަ',
    'ހޯމަ',
    'އަންގާރަ',
    'ބުދަ',
    'ބުރާސްފަތި',
    'ހުކުރު',
    'ހޮނިހިރު',
];
const DV_WEEKDAYS_SHORT = [
    'އާދި',
    'ހޯމަ',
    'އަން',
    'ބުދަ',
    'ބުރާ',
    'ހުކުރު',
    'ހޮނި',
];

function applyDocumentLocale(value: Locale) {
    if (typeof document === 'undefined') {
        return;
    }

    const el = document.documentElement;
    el.setAttribute('lang', value);
    el.setAttribute('dir', value === 'dv' ? 'rtl' : 'ltr');
}

function persist(value: Locale) {
    if (typeof window === 'undefined') {
        return;
    }

    window.localStorage.setItem(STORAGE_KEY, value);
    // 1-year cookie so the server could pick it up for SSR/hreflang later.
    document.cookie = `${COOKIE_KEY}=${value}; path=/; max-age=31536000; SameSite=Lax`;
}

// Apply on first load.
applyDocumentLocale(locale.value);

export function useLocale() {
    const isRtl = computed(() => locale.value === 'dv');

    function setLocale(value: Locale) {
        locale.value = value;
        applyDocumentLocale(value);
        persist(value);
    }

    function toggleLocale() {
        setLocale(locale.value === 'en' ? 'dv' : 'en');
    }

    /** Translate a UI message object `{ en, dv }`. */
    function t(entry: LocalizedText | undefined): string {
        if (!entry) {
            return '';
        }

        return entry[locale.value] ?? entry.en ?? '';
    }

    /**
     * Pick the localized field from a content record, falling back to the
     * other language (with a "translation missing" hint when needed).
     */
    function pick(
        entry: Partial<Record<Locale, string>> | undefined,
        opts: { fallback?: boolean } = { fallback: true },
    ): string {
        if (!entry) {
            return '';
        }

        const primary = entry[locale.value];

        if (primary && primary.trim().length > 0) {
            return primary;
        }

        if (opts.fallback) {
            const other = locale.value === 'en' ? entry.dv : entry.en;

            if (other && other.trim().length > 0) {
                return other;
            }
        }

        return '';
    }

    /** True when the requested language is missing for a content record. */
    function isMissing(
        entry: Partial<Record<Locale, string>> | undefined,
    ): boolean {
        if (!entry) {
            return true;
        }

        const primary = entry[locale.value];

        return !primary || primary.trim().length === 0;
    }

    /** Format a number using locale-aware numerals. */
    function num(value: number): string {
        return new Intl.NumberFormat(
            locale.value === 'dv' ? 'dv-MV' : 'en-GB',
        ).format(value);
    }

    /**
     * Format an ISO date string for the active locale.
     *
     * `opts` replaces the default parts rather than merging with them, so
     * asking for `{ month: 'long' }` gives "January" and not "1 January 2026".
     * Both locales have to agree on this: merging is what made every month
     * picker on the site read as the first day of the month.
     */
    function date(iso: string, opts?: Intl.DateTimeFormatOptions): string {
        const d = new Date(iso);
        const o: Intl.DateTimeFormatOptions = opts ?? {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        };

        if (locale.value !== 'dv') {
            return new Intl.DateTimeFormat('en-GB', o).format(d);
        }

        // Build the Dhivehi string from explicit names (day month year).
        const dmy: string[] = [];

        if (o.day) {
            dmy.push(String(d.getDate()));
        }

        if (o.month) {
            dmy.push(
                (o.month === 'short' || o.month === 'narrow'
                    ? DV_MONTHS_SHORT
                    : DV_MONTHS_LONG)[d.getMonth()],
            );
        }

        if (o.year) {
            dmy.push(String(d.getFullYear()));
        }

        const parts: string[] = [];

        if (o.weekday) {
            parts.push(
                (o.weekday === 'short' || o.weekday === 'narrow'
                    ? DV_WEEKDAYS_SHORT
                    : DV_WEEKDAYS_LONG)[d.getDay()],
            );
        }

        if (dmy.length) {
            parts.push(dmy.join(' '));
        }

        return parts.join('، ');
    }

    return {
        locale,
        isRtl,
        messages,
        setLocale,
        toggleLocale,
        t,
        pick,
        isMissing,
        num,
        date,
    };
}
