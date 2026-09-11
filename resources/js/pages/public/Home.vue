<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import {
    ArrowRight,
    CalendarDays,
    Download,
    Eye,
    GraduationCap,
    Megaphone,
    Phone,
    Pin,
    Quote,
    Target,
    UsersRound,
} from '@/lib/publicIcons';
import {
    announcements,
    heroSlides as defaultHero,
    news,
    school as defaultSchool,
} from '@/lib/sampleData';
import type { Announcement, NewsArticle } from '@/lib/sampleData';

const { t, pick, num, date, messages, isRtl } = useLocale();

const props = defineProps<{
    featuredNews?: NewsArticle[];
    latestAnnouncements?: Announcement[];
    hero?: typeof defaultHero;
    school?: typeof defaultSchool;
}>();

const heroSlides = computed(() => props.hero ?? defaultHero);
const school = computed(() => props.school ?? defaultSchool);

/* Swap to a stable placeholder if a school photo hasn't been added yet. */
function onImgError(e: Event, fallback: string) {
    const el = e.target as HTMLImageElement;

    if (el.src !== fallback) {
        el.src = fallback;
    }
}

/* ---- Hero slideshow (auto-advances, pauses while hovered) ---- */
const current = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

function go(i: number) {
    current.value = (i + heroSlides.value.length) % heroSlides.value.length;
}
function start() {
    stop();
    timer = setInterval(() => go(current.value + 1), 6500);
}
function stop() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}
onMounted(start);
onBeforeUnmount(stop);

/* ---- Data slices ---- */
const featured = computed(() => props.featuredNews ?? news.slice(0, 3));
const latestAnnouncements = computed(
    () =>
        props.latestAnnouncements ??
        [...announcements]
            .sort((a, b) => (b.pinned ? 1 : 0) - (a.pinned ? 1 : 0))
            .slice(0, 3),
);

const stats = computed(() => [
    {
        icon: CalendarDays,
        value: 2026 - school.value.established,
        label: { en: 'Years of learning', dv: 'ކިޔެވުމުގެ އަހަރު' },
    },
    {
        icon: UsersRound,
        value: school.value.students,
        label: { en: 'Students', dv: 'ދަރިވަރުން' },
    },
    {
        icon: GraduationCap,
        value: school.value.teachers,
        label: { en: 'Teachers', dv: 'މުދައްރިސުން' },
    },
]);

/* ---- Animated stat counters (count up once scrolled into view) ---- */
const statsSection = ref<HTMLElement | null>(null);
const statValues = ref(stats.value.map(() => 0));
let statsObserver: IntersectionObserver | null = null;
let counted = false;

function countUp() {
    if (counted) {
        return;
    }

    counted = true;
    const targets = stats.value.map((s) => s.value);

    if (
        typeof window === 'undefined' ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
        statValues.value = targets;

        return;
    }

    const duration = 1400;
    const t0 = performance.now();
    const tick = (now: number) => {
        const p = Math.min(1, (now - t0) / duration);
        const eased = 1 - Math.pow(1 - p, 3);
        statValues.value = targets.map((v) => Math.round(v * eased));

        if (p < 1) {
            requestAnimationFrame(tick);
        }
    };
    requestAnimationFrame(tick);
}

onMounted(() => {
    if (typeof IntersectionObserver === 'undefined') {
        countUp();

        return;
    }

    statsObserver = new IntersectionObserver(
        (entries) => {
            if (entries.some((e) => e.isIntersecting)) {
                countUp();
                statsObserver?.disconnect();
            }
        },
        { threshold: 0.3 },
    );

    if (statsSection.value) {
        statsObserver.observe(statsSection.value);
    }
});
onBeforeUnmount(() => statsObserver?.disconnect());

const quickLinks = [
    {
        label: messages.nav.announcements,
        href: '/announcements',
        icon: Megaphone,
    },
    {
        label: messages.nav.academicCalendar,
        href: '/academic-calendar',
        icon: CalendarDays,
    },
    { label: messages.nav.downloads, href: '/downloads', icon: Download },
    { label: messages.nav.contact, href: '/contact', icon: Phone },
];
</script>

<template>
    <Head :title="t(messages.nav.home)" />

    <!-- Hero slideshow -->
    <section
        class="relative isolate overflow-hidden"
        @mouseenter="stop"
        @mouseleave="start"
    >
        <div class="relative h-[76vh] min-h-[520px] w-full">
            <Transition
                v-for="(slide, i) in heroSlides"
                :key="i"
                enter-active-class="transition-opacity duration-[1200ms] ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-[1200ms] ease-out"
                leave-to-class="opacity-0"
            >
                <img
                    v-show="i === current"
                    :src="slide.image"
                    :alt="t(slide.title)"
                    class="animate-slow-zoom absolute inset-0 size-full object-cover"
                    loading="eager"
                    @error="onImgError($event, slide.fallback)"
                />
            </Transition>
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"
            />
            <div
                class="absolute inset-0 bg-gradient-to-r from-black/45 via-transparent to-transparent"
            />

            <div
                class="relative mx-auto flex h-full max-w-7xl flex-col justify-center px-4 sm:px-6 lg:px-8"
            >
                <p
                    class="mb-3 inline-flex w-fit items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium text-white backdrop-blur"
                >
                    <img
                        src="/images/logo.png"
                        alt=""
                        class="size-5 shrink-0 object-contain"
                    />
                    {{ t(messages.home.welcomeKicker) }}
                    {{ t(messages.site.name) }}
                </p>
                <Transition
                    mode="out-in"
                    enter-active-class="transition duration-700 ease-out"
                    enter-from-class="opacity-0 translate-y-4"
                    leave-active-class="transition duration-300 ease-in"
                    leave-to-class="opacity-0 -translate-y-3"
                >
                    <div :key="current" class="max-w-3xl">
                        <h1
                            class="text-4xl font-bold tracking-tight text-white drop-shadow sm:text-5xl lg:text-6xl"
                            dir="auto"
                        >
                            {{ t(heroSlides[current].title) }}
                        </h1>
                        <p
                            class="mt-4 text-lg text-white/90 sm:text-xl"
                            dir="auto"
                        >
                            {{ t(heroSlides[current].subtitle) }}
                        </p>
                        <p
                            class="mt-3 text-sm font-semibold tracking-wide text-brand-accent uppercase"
                            dir="auto"
                        >
                            {{ t(messages.home.motto) }}:
                            {{ t(school.motto) }} · Est.
                            {{ school.established }}
                        </p>
                    </div>
                </Transition>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        href="/contact"
                        class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-semibold text-brand-foreground shadow-lg transition hover:scale-[1.02] hover:brightness-110"
                    >
                        {{ t(messages.home.applyNow) }}
                        <ArrowRight
                            class="size-4"
                            :class="isRtl ? 'rotate-180' : ''"
                        />
                    </Link>
                    <Link
                        href="/announcements"
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-6 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/25"
                    >
                        {{ t(messages.home.latestAnnouncements) }}
                    </Link>
                </div>
            </div>

            <!-- Slide dots -->
            <div
                class="absolute inset-x-0 bottom-16 flex justify-center gap-2 sm:bottom-20"
            >
                <button
                    v-for="(slide, i) in heroSlides"
                    :key="i"
                    type="button"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="
                        i === current
                            ? 'w-9 bg-brand-accent shadow-[0_0_12px] shadow-brand-accent/60'
                            : 'w-2 bg-white/50 hover:w-4 hover:bg-white/80'
                    "
                    :aria-label="t(slide.title)"
                    :aria-current="i === current"
                    @click="go(i)"
                />
            </div>

            <!-- Ocean-wave divider into the stats band -->
            <div
                class="pointer-events-none absolute inset-x-0 bottom-0 text-brand"
            >
                <svg
                    viewBox="0 0 1440 64"
                    preserveAspectRatio="none"
                    class="block h-10 w-full sm:h-14"
                    aria-hidden="true"
                >
                    <path
                        fill="currentColor"
                        fill-opacity="0.35"
                        d="M0,40 C240,8 480,56 720,40 C960,24 1200,0 1440,24 L1440,64 L0,64 Z"
                    />
                    <path
                        fill="currentColor"
                        d="M0,48 C240,20 480,64 720,48 C960,32 1200,16 1440,36 L1440,64 L0,64 Z"
                    />
                </svg>
            </div>
        </div>
    </section>

    <!-- Stats band -->
    <section
        ref="statsSection"
        class="relative overflow-hidden bg-brand text-brand-foreground"
    >
        <div
            class="animate-float-soft pointer-events-none absolute start-1/4 -top-20 size-56 rounded-full bg-white/10 blur-3xl"
        />
        <div
            class="animate-float-soft-delayed pointer-events-none absolute end-10 -bottom-24 size-64 rounded-full bg-brand-accent/25 blur-3xl"
        />
        <div
            class="relative mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8"
        >
            <Reveal
                v-for="(s, i) in stats"
                :key="i"
                :delay="i * 90"
                class="flex items-center gap-4"
            >
                <span
                    class="grid size-13 shrink-0 place-items-center rounded-2xl bg-white/15 backdrop-blur-sm"
                >
                    <component :is="s.icon" class="size-6" />
                </span>
                <div>
                    <div
                        class="text-3xl font-extrabold tracking-tight tabular-nums sm:text-4xl"
                    >
                        {{ num(statValues[i]) }}
                    </div>
                    <div class="text-sm opacity-90" dir="auto">
                        {{ t(s.label as any) }}
                    </div>
                </div>
            </Reveal>
        </div>
    </section>

    <!-- Welcome / Principal + Mission & Vision -->
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="grid gap-12 lg:grid-cols-5">
            <Reveal class="lg:col-span-3">
                <span
                    class="inline-flex items-center gap-2 text-xs font-bold tracking-[0.18em] text-brand uppercase"
                >
                    <span class="h-px w-8 bg-brand" />
                    {{ t(messages.home.principalTitle) }}
                </span>
                <figure class="mt-6">
                    <Quote class="size-10 text-brand/30" />
                    <blockquote
                        class="mt-2 text-lg leading-relaxed text-foreground/90"
                        dir="auto"
                    >
                        {{ pick(school.welcome) }}
                    </blockquote>
                    <figcaption class="mt-6 flex items-center gap-4">
                        <img
                            :src="school.principal.photo"
                            :alt="pick(school.principal.name)"
                            class="size-14 rounded-full object-cover ring-2 ring-brand/20"
                            loading="lazy"
                        />
                        <div>
                            <div
                                class="font-semibold text-foreground"
                                dir="auto"
                            >
                                {{ pick(school.principal.name) }}
                            </div>
                            <div
                                class="text-sm text-muted-foreground"
                                dir="auto"
                            >
                                {{ pick(school.principal.title) }}
                            </div>
                        </div>
                    </figcaption>
                </figure>
            </Reveal>

            <Reveal :delay="120" class="space-y-6 lg:col-span-2">
                <div
                    class="group rounded-2xl border border-border bg-brand-muted/50 p-6 transition duration-300 hover:-translate-y-1 hover:border-brand/40 hover:shadow-lg hover:shadow-brand/10"
                >
                    <span
                        class="grid size-10 place-items-center rounded-xl bg-brand text-brand-foreground shadow-sm"
                    >
                        <Target class="size-5" />
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-brand">
                        {{ t(messages.home.missionTitle) }}
                    </h3>
                    <p
                        class="mt-2 text-sm leading-relaxed text-foreground/80"
                        dir="auto"
                    >
                        {{ pick(school.mission) }}
                    </p>
                </div>
                <div
                    class="group rounded-2xl border border-border bg-background p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand/40 hover:shadow-lg hover:shadow-brand/10"
                >
                    <span
                        class="grid size-10 place-items-center rounded-xl bg-brand-muted text-brand"
                    >
                        <Eye class="size-5" />
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-brand">
                        {{ t(messages.home.visionTitle) }}
                    </h3>
                    <p
                        class="mt-2 text-sm leading-relaxed text-foreground/80"
                        dir="auto"
                    >
                        {{ pick(school.vision) }}
                    </p>
                </div>
            </Reveal>
        </div>
    </section>

    <!-- Quick links -->
    <section class="relative border-y border-border bg-muted/40">
        <div class="bg-dots pointer-events-none absolute inset-0 opacity-30" />
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h2
                class="mb-6 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                {{ t(messages.home.quickLinks) }}
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Reveal
                    v-for="(q, i) in quickLinks"
                    :key="q.href"
                    :delay="i * 70"
                >
                    <Link
                        :href="q.href"
                        class="group flex items-center gap-4 rounded-2xl border border-border bg-background p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand/40 hover:shadow-lg hover:shadow-brand/10"
                    >
                        <span
                            class="grid size-12 place-items-center rounded-xl bg-brand-muted text-brand transition duration-300 group-hover:scale-105 group-hover:bg-brand group-hover:text-brand-foreground"
                        >
                            <component :is="q.icon" class="size-6" />
                        </span>
                        <span
                            class="font-semibold text-foreground"
                            dir="auto"
                            >{{ t(q.label) }}</span
                        >
                        <ArrowRight
                            class="ms-auto size-4 text-muted-foreground transition group-hover:text-brand"
                            :class="isRtl ? 'rotate-180' : ''"
                        />
                    </Link>
                </Reveal>
            </div>
        </div>
    </section>

    <!-- Featured news -->
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <span
                    class="inline-flex items-center gap-2 text-xs font-bold tracking-[0.18em] text-brand uppercase"
                >
                    <span class="h-px w-8 bg-brand" />
                    {{ t(messages.nav.news) }}
                </span>
                <h2
                    class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                >
                    {{ t(messages.home.featuredNews) }}
                </h2>
            </div>
            <Link
                href="/news"
                class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-brand hover:underline"
            >
                {{ t(messages.common.viewAll) }}
                <ArrowRight class="size-4" :class="isRtl ? 'rotate-180' : ''" />
            </Link>
        </div>
        <div class="grid gap-6 md:grid-cols-3">
            <Reveal
                v-for="(article, i) in featured"
                :key="article.id"
                :delay="i * 90"
            >
                <Link
                    :href="`/news/${article.slug}`"
                    class="group flex h-full flex-col overflow-hidden rounded-2xl border border-border bg-background shadow-sm transition duration-300 hover:-translate-y-1.5 hover:border-brand/40 hover:shadow-xl hover:shadow-brand/10"
                >
                    <div class="relative aspect-[16/10] overflow-hidden">
                        <img
                            :src="article.image"
                            :alt="pick(article.title)"
                            class="size-full object-cover transition duration-700 ease-out group-hover:scale-110"
                            loading="lazy"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent opacity-70 transition duration-300 group-hover:opacity-100"
                        />
                        <span
                            class="absolute start-3 bottom-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-brand shadow backdrop-blur"
                            >{{ date(article.date) }}</span
                        >
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <h3
                            class="text-lg font-semibold text-foreground transition-colors group-hover:text-brand"
                            dir="auto"
                        >
                            {{ pick(article.title) }}
                        </h3>
                        <p
                            class="mt-2 line-clamp-2 text-sm text-muted-foreground"
                            dir="auto"
                        >
                            {{ pick(article.excerpt) }}
                        </p>
                        <span
                            class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand"
                        >
                            {{ t(messages.common.readMore) }}
                            <ArrowRight
                                class="size-4 transition-transform duration-300 group-hover:translate-x-1"
                                :class="
                                    isRtl
                                        ? 'rotate-180 group-hover:-translate-x-1'
                                        : ''
                                "
                            />
                        </span>
                    </div>
                </Link>
            </Reveal>
        </div>
    </section>

    <!-- Latest announcements -->
    <section class="border-t border-border bg-muted/40">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <span
                        class="inline-flex items-center gap-2 text-xs font-bold tracking-[0.18em] text-brand uppercase"
                    >
                        <span class="h-px w-8 bg-brand" />
                        {{ t(messages.nav.announcements) }}
                    </span>
                    <h2
                        class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        {{ t(messages.home.latestAnnouncements) }}
                    </h2>
                </div>
                <Link
                    href="/announcements"
                    class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-brand hover:underline"
                >
                    {{ t(messages.common.viewAll) }}
                    <ArrowRight
                        class="size-4"
                        :class="isRtl ? 'rotate-180' : ''"
                    />
                </Link>
            </div>
            <div class="space-y-4">
                <Reveal
                    v-for="(a, i) in latestAnnouncements"
                    :key="a.id"
                    :delay="i * 80"
                >
                    <Link
                        href="/announcements"
                        class="group flex items-start gap-4 rounded-2xl border border-border bg-background p-5 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-brand/40 hover:shadow-lg hover:shadow-brand/10"
                    >
                        <span
                            class="grid size-11 shrink-0 place-items-center rounded-xl transition duration-300 group-hover:scale-105"
                            :class="
                                a.pinned
                                    ? 'bg-brand text-brand-foreground'
                                    : 'bg-brand-muted text-brand'
                            "
                        >
                            <Pin v-if="a.pinned" class="size-5" />
                            <Megaphone v-else class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="a.pinned"
                                    class="rounded-full bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand"
                                    >{{ t(messages.common.pinned) }}</span
                                >
                                <span class="text-xs text-muted-foreground">{{
                                    date(a.date)
                                }}</span>
                            </div>
                            <h3
                                class="mt-1 font-semibold text-foreground group-hover:text-brand"
                                dir="auto"
                            >
                                {{ pick(a.title) }}
                            </h3>
                            <p
                                class="mt-1 line-clamp-2 text-sm text-muted-foreground"
                                dir="auto"
                            >
                                {{ pick(a.body) }}
                            </p>
                        </div>
                    </Link>
                </Reveal>
            </div>
        </div>
    </section>
</template>
