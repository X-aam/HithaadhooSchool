<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';
import { computed } from 'vue';

interface Bilingual { en: string; dv: string }
interface Announcement {
    id: number;
    category: string;
    pinned: boolean;
    is_published: boolean;
    published_at: string | null;
    title: Bilingual;
    body: Bilingual;
}

const props = defineProps<{ announcement: Announcement | null }>();

const isEdit = computed(() => props.announcement !== null);

const form = useForm({
    category: props.announcement?.category ?? 'general',
    pinned: props.announcement?.pinned ?? false,
    is_published: props.announcement?.is_published ?? true,
    published_at: props.announcement?.published_at ?? new Date().toISOString().slice(0, 10),
    title: {
        en: props.announcement?.title?.en ?? '',
        dv: props.announcement?.title?.dv ?? '',
    },
    body: {
        en: props.announcement?.body?.en ?? '',
        dv: props.announcement?.body?.dv ?? '',
    },
});

function submit() {
    if (isEdit.value) {
        form.put(`/admin/announcements/${props.announcement!.id}`);
    } else {
        form.post('/admin/announcements');
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Edit announcement — CMS' : 'New announcement — CMS'" />

    <Link href="/admin/announcements" class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition hover:text-brand">
        <ArrowLeft class="size-4" /> Back to announcements
    </Link>

    <h1 class="mt-4 mb-6 text-2xl font-bold tracking-tight">{{ isEdit ? 'Edit announcement' : 'New announcement' }}</h1>

    <form class="space-y-8" @submit.prevent="submit">
        <!-- Meta -->
        <section class="rounded-2xl border border-border bg-background p-6 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Category</label>
                    <select v-model="form.category" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">
                        <option value="academic">Academic</option>
                        <option value="events">Events</option>
                        <option value="emergency">Emergency</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Date</label>
                    <input v-model="form.published_at" type="date" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30" />
                    <p v-if="form.errors.published_at" class="mt-1 text-xs text-red-600">{{ form.errors.published_at }}</p>
                </div>
                <label class="flex items-center gap-2.5">
                    <input v-model="form.pinned" type="checkbox" class="size-4 rounded border-border text-brand focus:ring-brand/30" />
                    <span class="text-sm font-medium">Pin to top</span>
                </label>
                <label class="flex items-center gap-2.5">
                    <input v-model="form.is_published" type="checkbox" class="size-4 rounded border-border text-brand focus:ring-brand/30" />
                    <span class="text-sm font-medium">Published</span>
                </label>
            </div>
        </section>

        <!-- English -->
        <section class="rounded-2xl border border-border bg-background p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase">English</h2>
            <div class="space-y-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Title</label>
                    <input v-model="form.title.en" type="text" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30" />
                    <p v-if="form.errors['title.en']" class="mt-1 text-xs text-red-600">{{ form.errors['title.en'] }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Body</label>
                    <textarea v-model="form.body.en" rows="5" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30"></textarea>
                    <p v-if="form.errors['body.en']" class="mt-1 text-xs text-red-600">{{ form.errors['body.en'] }}</p>
                </div>
            </div>
        </section>

        <!-- Dhivehi -->
        <section class="rounded-2xl border border-border bg-background p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase">ދިވެހި (Dhivehi)</h2>
            <div class="space-y-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Title</label>
                    <input v-model="form.title.dv" type="text" dir="rtl" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 font-thaana" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Body</label>
                    <textarea v-model="form.body.dv" rows="5" dir="rtl" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 font-thaana"></textarea>
                </div>
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-full bg-brand px-6 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110 disabled:opacity-60">
                <Save class="size-4" /> {{ isEdit ? 'Save changes' : 'Create announcement' }}
            </button>
            <Link href="/admin/announcements" class="text-sm font-medium text-muted-foreground hover:text-foreground">Cancel</Link>
        </div>
    </form>
</template>
