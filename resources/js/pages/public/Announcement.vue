<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useLocale } from '@/i18n/useLocale';
import {
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    Download,
    Pin,
} from '@/lib/publicIcons';

interface Bilingual {
    en?: string;
    dv?: string;
}

interface Attachment {
    name: string;
    url: string;
    size: string;
    extension: string;
}

defineProps<{
    announcement: {
        id: number;
        slug: string;
        category: string;
        pinned: boolean;
        date: string | null;
        title: Bilingual;
        body: Bilingual;
        attachments: Attachment[];
    };
}>();

const { t, pick, date, messages, isRtl } = useLocale();

// Both locales are required here: t() needs a complete pair.
const categoryLabels: Record<string, { en: string; dv: string }> = {
    academic: { en: 'Academic', dv: 'ކިޔެވުން' },
    events: { en: 'Events', dv: 'ހަރަކާތް' },
    emergency: { en: 'Urgent', dv: 'ކުއްލި' },
    general: { en: 'General', dv: 'އާންމު' },
};
</script>

<template>
    <Head :title="pick(announcement.title)" />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <Link
            href="/announcements"
            class="inline-flex items-center gap-2 text-sm font-medium text-brand hover:underline"
        >
            <component :is="isRtl ? ArrowRight : ArrowLeft" class="size-4" />
            {{ t(messages.nav.announcements) }}
        </Link>

        <article class="mt-6">
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="rounded-full bg-brand-muted px-3 py-1 text-xs font-semibold text-brand"
                    dir="auto"
                >
                    {{
                        t(
                            categoryLabels[announcement.category] ??
                                categoryLabels.general,
                        )
                    }}
                </span>
                <span
                    v-if="announcement.pinned"
                    class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900"
                >
                    <Pin class="size-3" />
                    {{ t(messages.common.pinned) }}
                </span>
            </div>

            <h1
                class="mt-4 text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                dir="auto"
            >
                {{ pick(announcement.title) }}
            </h1>

            <p
                v-if="announcement.date"
                class="mt-3 inline-flex items-center gap-1.5 text-sm text-muted-foreground"
            >
                <CalendarDays class="size-4" />
                {{ date(announcement.date) }}
            </p>

            <!-- No hero image: announcements are text-first by design. -->
            <div
                class="announcement-body prose mt-8 max-w-none leading-relaxed prose-neutral dark:prose-invert"
                :dir="isRtl ? 'rtl' : 'ltr'"
                :class="isRtl ? 'font-thaana' : ''"
                v-html="pick(announcement.body)"
            />

            <section
                v-if="announcement.attachments.length"
                class="mt-10 border-t border-border pt-6"
            >
                <h2
                    class="mb-3 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                >
                    {{ t({ en: 'Attachments', dv: 'ގުޅުވާފައިވާ ފައިލް' }) }}
                </h2>
                <ul class="space-y-2">
                    <li v-for="(file, i) in announcement.attachments" :key="i">
                        <a
                            :href="file.url"
                            target="_blank"
                            rel="noopener"
                            class="flex items-center gap-3 rounded-xl border border-border bg-background px-4 py-3 transition hover:border-brand/40 hover:shadow-sm"
                        >
                            <span
                                class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-muted text-brand"
                            >
                                <Download class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span
                                    class="block truncate font-medium"
                                    dir="auto"
                                >
                                    {{ file.name }}
                                </span>
                                <span
                                    class="block text-xs text-muted-foreground"
                                >
                                    {{ file.extension.toUpperCase() }}
                                    <template v-if="file.size">
                                        · {{ file.size }}</template
                                    >
                                </span>
                            </span>
                        </a>
                    </li>
                </ul>
            </section>
        </article>
    </div>
</template>

<style scoped>
/* Matches the news article body: the editor previews images as centred
   blocks, so the published page has to agree. */
.announcement-body :deep(img) {
    display: block;
    height: auto;
    max-width: 100%;
    min-width: min(100%, 14rem);
    margin-block: 2rem;
    margin-inline: auto;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px rgb(0 0 0 / 0.06);
}

.announcement-body :deep(img:first-child) {
    margin-block-start: 0;
}

.announcement-body :deep(img:last-child) {
    margin-block-end: 0;
}
</style>
