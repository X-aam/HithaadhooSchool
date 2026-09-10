<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Newspaper, Megaphone, Plus, Pin, CheckCircle2, CircleDashed } from '@lucide/vue';

interface Bilingual { en: string; dv?: string }
interface RecentNews { id: number; title: Bilingual; published_at: string | null; is_published: boolean }
interface RecentAnn extends RecentNews { pinned: boolean }

defineProps<{
    stats: { news: number; newsPublished: number; announcements: number; announcementsPublished: number };
    recentNews: RecentNews[];
    recentAnnouncements: RecentAnn[];
}>();
</script>

<template>
    <Head title="CMS Dashboard" />

    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
        <p class="mt-1 text-sm text-muted-foreground">Manage your school website content.</p>
    </div>

    <!-- Stat cards -->
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-border bg-background p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="grid size-11 place-items-center rounded-xl bg-brand-muted text-brand">
                    <Newspaper class="size-6" />
                </span>
                <Link href="/admin/news/create" class="inline-flex items-center gap-1 rounded-full bg-brand px-3 py-1.5 text-xs font-semibold text-brand-foreground transition hover:brightness-110">
                    <Plus class="size-3.5" /> New
                </Link>
            </div>
            <div class="mt-4 text-3xl font-bold">{{ stats.news }}</div>
            <div class="text-sm text-muted-foreground">News articles · {{ stats.newsPublished }} published</div>
            <Link href="/admin/news" class="mt-3 inline-block text-sm font-semibold text-brand hover:underline">Manage news →</Link>
        </div>

        <div class="rounded-2xl border border-border bg-background p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="grid size-11 place-items-center rounded-xl bg-brand-muted text-brand">
                    <Megaphone class="size-6" />
                </span>
                <Link href="/admin/announcements/create" class="inline-flex items-center gap-1 rounded-full bg-brand px-3 py-1.5 text-xs font-semibold text-brand-foreground transition hover:brightness-110">
                    <Plus class="size-3.5" /> New
                </Link>
            </div>
            <div class="mt-4 text-3xl font-bold">{{ stats.announcements }}</div>
            <div class="text-sm text-muted-foreground">Announcements · {{ stats.announcementsPublished }} published</div>
            <Link href="/admin/announcements" class="mt-3 inline-block text-sm font-semibold text-brand hover:underline">Manage announcements →</Link>
        </div>
    </div>

    <!-- Recent lists -->
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-border bg-background p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase">Recent news</h2>
            <ul class="divide-y divide-border">
                <li v-for="a in recentNews" :key="a.id" class="flex items-center gap-3 py-3">
                    <component :is="a.is_published ? CheckCircle2 : CircleDashed" class="size-4 shrink-0" :class="a.is_published ? 'text-brand' : 'text-muted-foreground'" />
                    <Link :href="`/admin/news/${a.id}/edit`" class="min-w-0 flex-1 truncate text-sm font-medium hover:text-brand">{{ a.title.en }}</Link>
                    <span class="shrink-0 text-xs text-muted-foreground">{{ a.published_at }}</span>
                </li>
                <li v-if="!recentNews.length" class="py-3 text-sm text-muted-foreground">No articles yet.</li>
            </ul>
        </section>

        <section class="rounded-2xl border border-border bg-background p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase">Recent announcements</h2>
            <ul class="divide-y divide-border">
                <li v-for="a in recentAnnouncements" :key="a.id" class="flex items-center gap-3 py-3">
                    <Pin v-if="a.pinned" class="size-4 shrink-0 text-brand" />
                    <component v-else :is="a.is_published ? CheckCircle2 : CircleDashed" class="size-4 shrink-0" :class="a.is_published ? 'text-brand' : 'text-muted-foreground'" />
                    <Link :href="`/admin/announcements/${a.id}/edit`" class="min-w-0 flex-1 truncate text-sm font-medium hover:text-brand">{{ a.title.en }}</Link>
                    <span class="shrink-0 text-xs text-muted-foreground">{{ a.published_at }}</span>
                </li>
                <li v-if="!recentAnnouncements.length" class="py-3 text-sm text-muted-foreground">No announcements yet.</li>
            </ul>
        </section>
    </div>
</template>
