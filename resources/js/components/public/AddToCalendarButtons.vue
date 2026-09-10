<script setup lang="ts">
import { useLocale } from '@/i18n/useLocale';
import type { Bilingual } from '@/lib/sampleData';

type CalendarEvent = { date: string; endDate?: string; title: Bilingual };

const props = defineProps<{ event: CalendarEvent }>();

const { t, pick } = useLocale();

function pad(n: number): string {
    return String(n).padStart(2, '0');
}

function gcalUrl(e: CalendarEvent): string {
    const start = e.date.replaceAll('-', '');
    const endExclusive = new Date(e.endDate || e.date);
    endExclusive.setDate(endExclusive.getDate() + 1);
    const end = `${endExclusive.getFullYear()}${pad(endExclusive.getMonth() + 1)}${pad(endExclusive.getDate())}`;

    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(pick(e.title))}&dates=${start}/${end}`;
}

function outlookUrl(e: CalendarEvent): string {
    return `https://outlook.live.com/calendar/0/deeplink/compose?subject=${encodeURIComponent(pick(e.title))}&startdt=${e.date}&enddt=${e.endDate || e.date}&allday=true`;
}
</script>

<template>
    <div class="flex shrink-0 items-center gap-1.5">
        <a :href="outlookUrl(event)" target="_blank" rel="noopener" class="inline-flex size-7 items-center justify-center rounded-md ring-1 ring-inset ring-border transition hover:bg-muted" :aria-label="`Add ${pick(event.title)} to Outlook`" :title="t({ en: 'Add to Outlook', dv: 'އައުޓްލުކްއަށް އިތުރުކުރައްވާ' })">
            <svg viewBox="0 0 24 24" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path fill="#0A2767" d="M13 4.5v15l7.4-1.5a1 1 0 0 0 .8-1V7a1 1 0 0 0-.8-1z" />
                <path fill="#0364B8" d="M13 6.2 20.6 11l.6-.4V7a1 1 0 0 0-.8-1z" />
                <rect x="2.5" y="5.5" width="11.5" height="13" rx="1.2" fill="#0F6CBD" />
                <path fill="#fff" d="M8.2 8.6c-1.9 0-3.1 1.4-3.1 3.4s1.2 3.4 3.1 3.4 3.1-1.4 3.1-3.4-1.2-3.4-3.1-3.4zm0 1.6c1 0 1.5.9 1.5 1.8s-.5 1.8-1.5 1.8-1.5-.9-1.5-1.8.5-1.8 1.5-1.8z" />
                <path fill="#0A2767" d="M14 11.2v3.6l4.4 2.7a.8.8 0 0 0 .9 0L21 16.3z" opacity=".9" />
                <path fill="#28A8EA" d="M14 8.5h7v7.8a1 1 0 0 1-.5.9l-3 1.8a1 1 0 0 1-1 0L14 17z" opacity=".5" />
            </svg>
        </a>
        <a :href="gcalUrl(event)" target="_blank" rel="noopener" class="inline-flex size-7 items-center justify-center rounded-md ring-1 ring-inset ring-border transition hover:bg-muted" :aria-label="`Add ${pick(event.title)} to Google Calendar`" :title="t({ en: 'Add to Google Calendar', dv: 'ގޫގަލް ކަލަންޑަރަށް އިތުރުކުރައްވާ' })">
            <svg viewBox="0 0 24 24" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path fill="#4285F4" d="M17 3h2a2 2 0 0 1 2 2v2h-4z" />
                <path fill="#EA4335" d="M7 3H5a2 2 0 0 0-2 2v2h4z" />
                <path fill="#34A853" d="M7 21H5a2 2 0 0 1-2-2v-2h4z" />
                <path fill="#FBBC04" d="M17 21h2a2 2 0 0 0 2-2v-2h-4z" />
                <path fill="#4285F4" d="M3 7h4v10H3z" />
                <path fill="#1967D2" d="M17 7h4v10h-4z" />
                <path fill="#EA4335" d="M7 3h10v4H7z" />
                <path fill="#34A853" d="M7 17h10v4H7z" />
                <rect x="7" y="7" width="10" height="10" fill="#fff" />
                <text x="12" y="15" font-size="7" font-weight="700" text-anchor="middle" fill="#4285F4" font-family="Arial, sans-serif">31</text>
            </svg>
        </a>
    </div>
</template>
