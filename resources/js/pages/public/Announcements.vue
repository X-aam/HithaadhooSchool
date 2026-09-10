<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import { Pin, Search } from '@/lib/publicIcons';
import { announcements   } from '@/lib/sampleData';
import type {Announcement, AnnouncementCategory} from '@/lib/sampleData';

const props = defineProps<{ items?: Announcement[] }>();

const { t, pick, date, messages, isMissing } = useLocale();

const source = computed<Announcement[]>(() => props.items ?? announcements);

const categories: { key: AnnouncementCategory | 'all'; label: { en: string; dv: string } }[] = [
    { key: 'all', label: messages.common.all },
    { key: 'academic', label: { en: 'Academic', dv: 'ކިޔެވުން' } },
    { key: 'events', label: { en: 'Events', dv: 'ހަރަކާތްތައް' } },
    { key: 'emergency', label: { en: 'Emergency', dv: 'ކުއްލި' } },
    { key: 'general', label: { en: 'General', dv: 'އާންމު' } },
];

const active = ref<AnnouncementCategory | 'all'>('all');
const query = ref('');

function matchesQuery(a: Announcement): boolean {
    const q = query.value.trim().toLowerCase();

    if (!q) {
        return true;
    }

    const dv = query.value.trim();

    return (
        a.title.en.toLowerCase().includes(q) ||
        (a.title.dv ?? '').includes(dv) ||
        a.body.en.toLowerCase().includes(q) ||
        (a.body.dv ?? '').includes(dv)
    );
}

const filtered = computed(() =>
    [...source.value]
        .filter((a) => (active.value === 'all' || a.category === active.value) && matchesQuery(a))
        .sort((a, b) => {
            if (a.pinned !== b.pinned) {
return a.pinned ? -1 : 1;
}

            return b.date.localeCompare(a.date);
        }),
);
</script>

<template>
    <Head :title="t(messages.nav.announcements)" />


    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <!-- Search -->
        <div class="relative mb-4 w-full sm:max-w-xs">
            <Search class="pointer-events-none absolute inset-y-0 start-3 my-auto size-4 text-muted-foreground" />
            <input
                v-model="query"
                type="search"
                :placeholder="t({ en: 'Search announcements…', dv: 'އިޢުލާންތައް ހޯއްދަވާ…' })"
                class="w-full rounded-full border border-border bg-background py-1.5 pe-3 ps-9 text-sm text-foreground transition placeholder:text-muted-foreground focus:border-brand focus:outline-none"
            />
        </div>

        <!-- Filters -->
        <div class="mb-8 flex flex-wrap gap-2">
            <button
                v-for="c in categories"
                :key="c.key"
                type="button"
                class="rounded-full border px-4 py-1.5 text-sm font-medium transition"
                :class="active === c.key ? 'border-brand bg-brand text-brand-foreground' : 'border-border bg-background text-foreground/70 hover:border-brand/40 hover:text-brand'"
                @click="active = c.key"
            >
                {{ t(c.label) }}
            </button>
        </div>

        <p v-if="!filtered.length" class="py-16 text-center text-muted-foreground">{{ t(messages.common.noResults) }}</p>

        <div class="space-y-4">
            <Reveal v-for="(a, i) in filtered" :key="a.id" :delay="i * 60">
                <article
                    class="rounded-2xl border bg-background p-6 shadow-sm transition hover:shadow-md"
                    :class="a.pinned ? 'border-brand/40 ring-1 ring-brand/20' : 'border-border'"
                >
                    <div class="flex items-center gap-2">
                        <span v-if="a.pinned" class="inline-flex items-center gap-1 rounded-full bg-brand px-2.5 py-0.5 text-xs font-semibold text-brand-foreground">
                            <Pin class="size-3" /> {{ t(messages.common.pinned) }}
                        </span>
                        <span class="rounded-full bg-brand-muted px-2.5 py-0.5 text-xs font-medium text-brand">
                            {{ t(categories.find((c) => c.key === a.category)!.label) }}
                        </span>
                        <span class="ms-auto text-xs text-muted-foreground">{{ date(a.date) }}</span>
                    </div>
                    <h2 class="mt-3 text-xl font-semibold text-foreground" dir="auto">{{ pick(a.title) }}</h2>
                    <p class="mt-2 leading-relaxed text-foreground/80" dir="auto">{{ pick(a.body) }}</p>
                    <p v-if="isMissing(a.title)" class="mt-2 text-xs italic text-muted-foreground">{{ t(messages.common.translationMissing) }}</p>
                </article>
            </Reveal>
        </div>
    </div>
</template>
