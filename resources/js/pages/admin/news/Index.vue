<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, ExternalLink } from '@lucide/vue';

interface Bilingual { en: string; dv?: string }
interface Article {
    id: number;
    slug: string;
    category: string;
    image: string | null;
    is_published: boolean;
    published_at: string | null;
    title: Bilingual;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginated {
    data: Article[];
    links: PaginationLink[];
    total: number;
    from: number | null;
    to: number | null;
}

defineProps<{ articles: Paginated }>();

const categoryLabels: Record<string, string> = {
    schoolNews: 'School News',
    achievements: 'Achievements',
    events: 'Events',
};

function destroy(article: Article) {
    if (confirm(`Delete “${article.title.en}”? This cannot be undone.`)) {
        router.delete(`/admin/news/${article.id}`);
    }
}
</script>

<template>
    <Head title="News — CMS" />

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">News &amp; Blog</h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ articles.total }} article(s)</p>
        </div>
        <Link href="/admin/news/create" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110">
            <Plus class="size-4" /> New article
        </Link>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm">
        <table class="w-full text-sm">
            <thead class="border-b border-border bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                <tr>
                    <th class="px-4 py-3 font-medium">Title</th>
                    <th class="px-4 py-3 font-medium">Category</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <tr v-for="a in articles.data" :key="a.id" class="hover:bg-muted/30">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img v-if="a.image" :src="a.image" alt="" class="size-10 shrink-0 rounded-lg object-cover" />
                            <div class="min-w-0">
                                <div class="truncate font-medium">{{ a.title.en }}</div>
                                <div v-if="a.title.dv" class="truncate text-xs text-muted-foreground" dir="rtl">{{ a.title.dv }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="rounded-full bg-brand-muted px-2.5 py-0.5 text-xs font-medium text-brand">{{ categoryLabels[a.category] ?? a.category }}</span>
                    </td>
                    <td class="px-4 py-3 text-muted-foreground">{{ a.published_at }}</td>
                    <td class="px-4 py-3">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="a.is_published ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-muted text-muted-foreground'"
                        >
                            {{ a.is_published ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <a :href="`/news/${a.slug}`" target="_blank" rel="noopener" class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-brand" title="View">
                                <ExternalLink class="size-4" />
                            </a>
                            <Link :href="`/admin/news/${a.id}/edit`" class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-brand" title="Edit">
                                <Pencil class="size-4" />
                            </Link>
                            <button type="button" class="rounded-lg p-2 text-muted-foreground transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete" @click="destroy(a)">
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="!articles.data.length">
                    <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">
                        No articles yet. <Link href="/admin/news/create" class="font-semibold text-brand hover:underline">Create the first one.</Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="articles.links.length > 3" class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs text-muted-foreground">Showing {{ articles.from }}–{{ articles.to }} of {{ articles.total }}</p>
        <div class="flex flex-wrap items-center gap-1">
            <template v-for="(link, i) in articles.links" :key="i">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="min-w-9 rounded-lg border px-3 py-1.5 text-center text-sm font-medium transition"
                    :class="link.active ? 'border-brand bg-brand text-brand-foreground' : 'border-border text-foreground/70 hover:bg-muted'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="min-w-9 rounded-lg border border-border px-3 py-1.5 text-center text-sm text-muted-foreground/50"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
