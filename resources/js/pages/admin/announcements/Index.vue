<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Pin } from '@lucide/vue';

interface Bilingual { en: string; dv?: string }
interface Announcement {
    id: number;
    category: string;
    pinned: boolean;
    is_published: boolean;
    published_at: string | null;
    title: Bilingual;
}

defineProps<{ announcements: Announcement[] }>();

const categoryLabels: Record<string, string> = {
    academic: 'Academic',
    events: 'Events',
    emergency: 'Emergency',
    general: 'General',
};

function destroy(a: Announcement) {
    if (confirm(`Delete “${a.title.en}”? This cannot be undone.`)) {
        router.delete(`/admin/announcements/${a.id}`);
    }
}
</script>

<template>
    <Head title="Announcements — CMS" />

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Announcements</h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ announcements.length }} announcement(s)</p>
        </div>
        <Link href="/admin/announcements/create" class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110">
            <Plus class="size-4" /> New announcement
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
                <tr v-for="a in announcements" :key="a.id" class="hover:bg-muted/30">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <Pin v-if="a.pinned" class="size-4 shrink-0 text-brand" />
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
                            <Link :href="`/admin/announcements/${a.id}/edit`" class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-brand" title="Edit">
                                <Pencil class="size-4" />
                            </Link>
                            <button type="button" class="rounded-lg p-2 text-muted-foreground transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete" @click="destroy(a)">
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="!announcements.length">
                    <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">
                        No announcements yet. <Link href="/admin/announcements/create" class="font-semibold text-brand hover:underline">Create the first one.</Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
