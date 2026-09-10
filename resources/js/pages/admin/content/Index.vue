<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, FileText } from '@lucide/vue';

interface Section {
    key: string;
    label: string;
    published: boolean;
    hasDraft: boolean;
}

defineProps<{ sections: Section[] }>();
</script>

<template>
    <Head title="Pages — CMS" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight">Pages &amp; content</h1>
        <p class="mt-1 text-sm text-muted-foreground">Edit the text and data shown across the public website.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <Link
            v-for="s in sections"
            :key="s.key"
            :href="`/admin/content/${s.key}/edit`"
            class="group flex items-center gap-4 rounded-2xl border border-border bg-background p-5 shadow-sm transition hover:border-brand/40 hover:shadow-md"
        >
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-muted text-brand">
                <FileText class="size-5" />
            </span>
            <span class="min-w-0 flex-1">
                <span class="block font-semibold">{{ s.label }}</span>
                <span class="mt-0.5 flex flex-wrap items-center gap-1.5">
                    <span
                        v-if="s.hasDraft"
                        class="rounded-full bg-amber-500/15 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:text-amber-300"
                    >
                        Draft pending
                    </span>
                    <span
                        v-if="s.published"
                        class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300"
                    >
                        Published
                    </span>
                    <span
                        v-else-if="!s.hasDraft"
                        class="text-xs text-muted-foreground"
                    >
                        Using default content
                    </span>
                </span>
            </span>
            <ChevronRight class="size-5 shrink-0 text-muted-foreground transition group-hover:translate-x-0.5 group-hover:text-brand" />
        </Link>
    </div>
</template>
