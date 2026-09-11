<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import { ArrowRight } from '@/lib/publicIcons';
import { news } from '@/lib/sampleData';
import type { NewsArticle, NewsCategory } from '@/lib/sampleData';

const props = defineProps<{ articles?: NewsArticle[] }>();

const { t, pick, date, messages, isRtl } = useLocale();

const source = computed<NewsArticle[]>(() => props.articles ?? news);

const categories: {
    key: NewsCategory | 'all';
    label: { en: string; dv: string };
}[] = [
    // Reads as a dropdown default now, so it names what it clears.
    { key: 'all', label: { en: 'All categories', dv: 'ހުރިހާ ބައި' } },
    { key: 'schoolNews', label: { en: 'School News', dv: 'ސްކޫލް ޚަބަރު' } },
    { key: 'achievements', label: { en: 'Achievements', dv: 'ކާމިޔާބީ' } },
    { key: 'events', label: { en: 'Events', dv: 'ހަރަކާތްތައް' } },
];

const active = ref<NewsCategory | 'all'>('all');
const filtered = computed(() =>
    [...source.value]
        .filter((n) => active.value === 'all' || n.category === active.value)
        .sort((a, b) => b.date.localeCompare(a.date)),
);

const perPage = 9;
const page = ref(1);
const totalPages = computed(() =>
    Math.max(1, Math.ceil(filtered.value.length / perPage)),
);
const paged = computed(() =>
    filtered.value.slice((page.value - 1) * perPage, page.value * perPage),
);

watch(active, () => {
    page.value = 1;
});
watch(totalPages, (tp) => {
    if (page.value > tp) {
        page.value = tp;
    }
});

function goTo(p: number) {
    page.value = Math.min(Math.max(1, p), totalPages.value);

    if (typeof window !== 'undefined') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
</script>

<template>
    <Head :title="t(messages.nav.news)" />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-wrap items-center gap-2">
            <select
                v-model="active"
                :aria-label="t({ en: 'Category', dv: 'ބައި' })"
                class="rounded-full border bg-background px-3 py-1.5 text-sm font-medium transition focus:border-brand focus:outline-none"
                :class="
                    active === 'all'
                        ? 'border-border text-foreground'
                        : 'border-brand text-brand'
                "
            >
                <option v-for="c in categories" :key="c.key" :value="c.key">
                    {{ t(c.label) }}
                </option>
            </select>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Reveal
                v-for="(article, i) in paged"
                :key="article.id"
                :delay="(i % 3) * 90"
            >
                <Link
                    :href="`/news/${article.slug}`"
                    class="group flex h-full flex-col overflow-hidden rounded-2xl border border-border bg-background shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                >
                    <div class="aspect-[16/10] overflow-hidden">
                        <img
                            :src="article.image"
                            :alt="pick(article.title)"
                            class="size-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-center gap-2 text-xs">
                            <span
                                class="rounded-full bg-brand-muted px-2.5 py-0.5 font-medium text-brand"
                                >{{
                                    t(
                                        categories.find(
                                            (c) => c.key === article.category,
                                        )!.label,
                                    )
                                }}</span
                            >
                            <span class="text-muted-foreground">{{
                                date(article.date)
                            }}</span>
                        </div>
                        <h2
                            class="mt-3 text-lg font-semibold text-foreground group-hover:text-brand"
                            dir="auto"
                        >
                            {{ pick(article.title) }}
                        </h2>
                        <p
                            class="mt-2 line-clamp-3 text-sm text-muted-foreground"
                            dir="auto"
                        >
                            {{ pick(article.excerpt) }}
                        </p>
                        <span
                            class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand"
                        >
                            {{ t(messages.common.readMore) }}
                            <ArrowRight
                                class="size-4"
                                :class="isRtl ? 'rotate-180' : ''"
                            />
                        </span>
                    </div>
                </Link>
            </Reveal>
        </div>

        <p
            v-if="!filtered.length"
            class="py-16 text-center text-muted-foreground"
        >
            {{ t(messages.common.noResults) }}
        </p>

        <nav
            v-if="totalPages > 1"
            class="mt-10 flex items-center justify-center gap-1.5"
            :aria-label="t(messages.common.menu)"
        >
            <button
                type="button"
                class="rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-foreground/70 transition hover:border-brand/40 hover:text-brand disabled:opacity-40"
                :disabled="page === 1"
                @click="goTo(page - 1)"
            >
                <ArrowRight
                    class="size-4 rotate-180"
                    :class="isRtl ? 'rotate-0' : ''"
                />
            </button>
            <button
                v-for="p in totalPages"
                :key="p"
                type="button"
                class="min-w-9 rounded-lg border px-3 py-1.5 text-sm font-medium transition"
                :class="
                    p === page
                        ? 'border-brand bg-brand text-brand-foreground'
                        : 'border-border text-foreground/70 hover:border-brand/40 hover:text-brand'
                "
                @click="goTo(p)"
            >
                {{ p }}
            </button>
            <button
                type="button"
                class="rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-foreground/70 transition hover:border-brand/40 hover:text-brand disabled:opacity-40"
                :disabled="page === totalPages"
                @click="goTo(page + 1)"
            >
                <ArrowRight class="size-4" :class="isRtl ? 'rotate-180' : ''" />
            </button>
        </nav>
    </div>
</template>
