<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, RotateCcw, Save, Send, Undo2 } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{ section: string; label: string; processing?: boolean }>();

const emit = defineEmits<{ save: [] }>();

const page = usePage();
const published = computed(() => Boolean((page.props as Record<string, unknown>).published));
const hasDraft = computed(() => Boolean((page.props as Record<string, unknown>).hasDraft));

function publish() {
    router.post(`/admin/content/${props.section}/publish`, {}, {
        preserveScroll: true,
        preserveState: false,
    });
}

function discardDraft() {
    if (confirm('Discard your unpublished draft changes and revert to the live version?')) {
        router.delete(`/admin/content/${props.section}/draft`, {
            preserveScroll: true,
            preserveState: false,
        });
    }
}

function reset() {
    if (confirm('Reset this section back to the built-in default content? Your customisations will be removed.')) {
        router.delete(`/admin/content/${props.section}`, { preserveState: false });
    }
}
</script>

<template>
    <Head :title="`${label} — CMS`" />

    <Link href="/admin/content" class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition hover:text-brand">
        <ArrowLeft class="size-4" /> Back to pages
    </Link>

    <div class="mt-4 mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight">{{ label }}</h1>
                <span
                    v-if="hasDraft"
                    class="rounded-full bg-amber-500/15 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300"
                >
                    Unpublished draft
                </span>
                <span
                    v-else-if="published"
                    class="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300"
                >
                    Published
                </span>
                <span
                    v-else
                    class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold text-muted-foreground"
                >
                    Not published
                </span>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
                Edits are saved privately as a draft; use <strong>Publish</strong> to push them to the public website.
            </p>
        </div>
        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-muted-foreground transition hover:bg-muted" @click="reset">
            <RotateCcw class="size-4" /> Reset to default
        </button>
    </div>

    <form class="space-y-6" @submit.prevent="emit('save')">
        <slot />

        <div class="sticky bottom-4 flex flex-wrap items-center gap-3 rounded-full border border-border bg-background/90 px-4 py-3 shadow-lg backdrop-blur">
            <button type="submit" :disabled="processing" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110 disabled:opacity-60">
                <Save class="size-4" /> Save draft
            </button>
            <button
                type="button"
                :disabled="processing || !hasDraft"
                class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-110 disabled:opacity-40"
                @click="publish"
            >
                <Send class="size-4" /> Publish
            </button>
            <button
                v-if="hasDraft"
                type="button"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-medium text-muted-foreground transition hover:bg-muted"
                @click="discardDraft"
            >
                <Undo2 class="size-4" /> Discard draft
            </button>
            <Link href="/admin/content" class="ms-auto text-sm font-medium text-muted-foreground hover:text-foreground">Cancel</Link>
        </div>
    </form>
</template>
