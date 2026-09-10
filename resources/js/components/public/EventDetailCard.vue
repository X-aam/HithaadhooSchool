<script setup lang="ts">
import AddToCalendarButtons from '@/components/public/AddToCalendarButtons.vue';
import { useLocale } from '@/i18n/useLocale';
import type { Bilingual } from '@/lib/sampleData';

type CalendarEvent = { date: string; endDate?: string; title: Bilingual };

const props = defineProps<{ event: CalendarEvent; badgeClass: string }>();

const { pick, date } = useLocale();
</script>

<template>
    <li class="flex items-center gap-3 rounded-2xl border border-border bg-background p-3 shadow-sm">
        <div class="flex w-14 shrink-0 flex-col items-center justify-center rounded-xl py-1.5 text-center ring-1 ring-inset" :class="badgeClass">
            <span class="text-lg font-bold leading-none">{{ event.date.slice(8, 10) }}</span>
            <span class="text-xs font-medium uppercase">{{ date(event.date, { month: 'short' }) }}</span>
            <slot name="badge" />
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-sm font-medium text-foreground" dir="auto">{{ pick(event.title) }}</h3>
            <slot />
        </div>
        <AddToCalendarButtons :event="event" class="flex-col" />
    </li>
</template>
