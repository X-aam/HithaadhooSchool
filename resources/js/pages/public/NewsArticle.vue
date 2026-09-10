<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import {
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    Share2,
    UsersRound,
} from '@/lib/publicIcons';
import { news } from '@/lib/sampleData';
import type { NewsArticle } from '@/lib/sampleData';

const props = defineProps<{ slug: string; articles?: NewsArticle[] }>();

const { t, pick, date, messages, isRtl } = useLocale();

const source = computed<NewsArticle[]>(() => props.articles ?? news);

const article = computed(() => source.value.find((n) => n.slug === props.slug));
const related = computed(() =>
    source.value
        .filter(
            (n) =>
                n.slug !== props.slug && n.category === article.value?.category,
        )
        .slice(0, 3),
);

function share() {
    if (navigator.share && article.value) {
        navigator
            .share({
                title: pick(article.value.title),
                url: window.location.href,
            })
            .catch(() => {});
    } else {
        navigator.clipboard?.writeText(window.location.href);
    }
}
</script>

<template>
    <Head :title="article ? pick(article.title) : t(messages.nav.news)" />

    <div v-if="article" class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <Link
            href="/news"
            class="inline-flex items-center gap-2 text-sm font-medium text-brand hover:underline"
        >
            <component :is="isRtl ? ArrowRight : ArrowLeft" class="size-4" />
            {{ t(messages.nav.news) }}
        </Link>

        <article class="mt-6">
            <h1
                class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                dir="auto"
            >
                {{ pick(article.title) }}
            </h1>
            <div
                class="mt-4 flex flex-wrap items-center gap-4 text-sm text-muted-foreground"
            >
                <span class="inline-flex items-center gap-1.5"
                    ><UsersRound class="size-4" /><span dir="auto"
                        >{{ t(messages.common.by) }}
                        {{ pick(article.author) }}</span
                    ></span
                >
                <span class="inline-flex items-center gap-1.5"
                    ><CalendarDays class="size-4" />{{
                        date(article.date)
                    }}</span
                >
                <button
                    type="button"
                    class="ms-auto inline-flex items-center gap-1.5 rounded-full border border-border px-3 py-1 transition hover:border-brand/40 hover:text-brand"
                    @click="share"
                >
                    <Share2 class="size-4" />
                    {{ t({ en: 'Share', dv: 'ޙިއްޞާކުރައްވާ' }) }}
                </button>
            </div>

            <img
                :src="article.image"
                :alt="pick(article.title)"
                class="mt-6 aspect-[16/9] w-full rounded-2xl object-cover"
                loading="lazy"
            />

            <div
                class="article-body prose mt-8 max-w-none leading-relaxed prose-neutral dark:prose-invert"
                :dir="isRtl ? 'rtl' : 'ltr'"
                :class="isRtl ? 'font-thaana' : ''"
                v-html="pick(article.body)"
            />
        </article>

        <section
            v-if="related.length"
            class="mt-16 border-t border-border pt-10"
        >
            <h2 class="mb-6 text-xl font-bold text-foreground">
                {{ t({ en: 'Related articles', dv: 'ގުޅުންހުރި ލިޔުންތައް' }) }}
            </h2>
            <div class="grid gap-6 sm:grid-cols-3">
                <Reveal v-for="(r, i) in related" :key="r.id" :delay="i * 80">
                    <Link
                        :href="`/news/${r.slug}`"
                        class="group block overflow-hidden rounded-2xl border border-border bg-background shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                    >
                        <img
                            :src="r.image"
                            :alt="pick(r.title)"
                            class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                        <div class="p-4">
                            <h3
                                class="text-sm font-semibold text-foreground group-hover:text-brand"
                                dir="auto"
                            >
                                {{ pick(r.title) }}
                            </h3>
                        </div>
                    </Link>
                </Reveal>
            </div>
        </section>
    </div>

    <div v-else class="mx-auto max-w-3xl px-4 py-24 text-center">
        <p class="text-muted-foreground">{{ t(messages.common.noResults) }}</p>
        <Link
            href="/news"
            class="mt-4 inline-block text-sm font-semibold text-brand hover:underline"
            >{{ t(messages.nav.news) }}</Link
        >
    </div>
</template>

<style scoped>
/*
 * Styling for author-written body HTML. The rich text editor previews images
 * as centred blocks, so the published article has to match or photos land
 * somewhere different from where they were placed.
 *
 * Widths set from the editor's toolbar arrive as an inline `style="width: 50%"`,
 * so nothing here may set `width` — that would either be ignored or fight the
 * author's choice.
 */
.article-body :deep(img) {
    display: block;
    height: auto;
    max-width: 100%;
    /* Keeps a 25%-width photo readable on a phone: min-width beats an inline
       width in the cascade, so the author's setting still applies on desktop. */
    min-width: min(100%, 14rem);
    margin-block: 2rem;
    margin-inline: auto;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px rgb(0 0 0 / 0.06);
}

/* An image opening or closing the article shouldn't add a double gap. */
.article-body :deep(img:first-child) {
    margin-block-start: 0;
}

.article-body :deep(img:last-child) {
    margin-block-end: 0;
}

/* Captions, when the author writes a figure rather than a bare image. */
.article-body :deep(figure) {
    margin-block: 2rem;
}

.article-body :deep(figure img) {
    margin-block: 0;
}

.article-body :deep(figcaption) {
    margin-block-start: 0.625rem;
    color: var(--muted-foreground);
    font-size: 0.8125rem;
    text-align: center;
}
</style>
