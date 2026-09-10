<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Save, Upload } from '@lucide/vue';
import { computed, ref } from 'vue';
import RichTextEditor from '@/components/admin/RichTextEditor.vue';
import { uploadImage } from '@/lib/uploadImage';

interface Bilingual { en: string; dv: string }
interface Article {
    id: number;
    slug: string;
    category: string;
    image: string | null;
    is_published: boolean;
    published_at: string | null;
    author: Bilingual;
    title: Bilingual;
    excerpt: Bilingual;
    body: Bilingual;
}

const props = defineProps<{ article: Article | null; authorName: Bilingual }>();

const isEdit = computed(() => props.article !== null);

const form = useForm({
    slug: props.article?.slug ?? '',
    category: props.article?.category ?? 'schoolNews',
    image: props.article?.image ?? '',
    is_published: props.article?.is_published ?? true,
    published_at: props.article?.published_at ?? new Date().toISOString().slice(0, 10),
    title: {
        en: props.article?.title?.en ?? '',
        dv: props.article?.title?.dv ?? '',
    },
    excerpt: {
        en: props.article?.excerpt?.en ?? '',
        dv: props.article?.excerpt?.dv ?? '',
    },
    body: {
        en: props.article?.body?.en ?? '',
        dv: props.article?.body?.dv ?? '',
    },
});

const tab = ref<'en' | 'dv'>('dv');

const enHasError = computed(() =>
    ['title.en', 'excerpt.en', 'body.en'].some((k) => (form.errors as Record<string, string>)[k]),
);
const dvHasError = computed(() =>
    ['title.dv', 'excerpt.dv', 'body.dv'].some((k) => (form.errors as Record<string, string>)[k]),
);

const imageInput = ref<HTMLInputElement | null>(null);
const imageUploading = ref(false);

async function uploadFeatured(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file) {
return;
}

    imageUploading.value = true;

    try {
        form.image = await uploadImage(file);
    } catch {
        window.alert('Image upload failed. Please try a smaller image (max 5 MB).');
    } finally {
        imageUploading.value = false;
    }
}

function submit(publish: boolean) {
    form.is_published = publish;

    if (isEdit.value) {
        form.put(`/admin/news/${props.article!.id}`);
    } else {
        form.post('/admin/news');
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Edit article — CMS' : 'New article — CMS'" />

    <Link href="/admin/news" class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition hover:text-brand">
        <ArrowLeft class="size-4" /> Back to news
    </Link>

    <h1 class="mt-4 mb-6 text-2xl font-bold tracking-tight">{{ isEdit ? 'Edit article' : 'New article' }}</h1>

    <form @submit.prevent="submit(true)">
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main content with language tabs -->
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-border bg-background p-6 shadow-sm">
                    <div class="mb-5 flex gap-1 border-b border-border">
                        <button
                            type="button"
                            class="relative -mb-px border-b-2 px-4 py-2 text-sm font-semibold transition"
                            :class="tab === 'en' ? 'border-brand text-brand' : 'border-transparent text-muted-foreground hover:text-foreground'"
                            @click="tab = 'en'"
                        >
                            English
                            <span v-if="enHasError" class="absolute -end-0.5 top-1 size-2 rounded-full bg-red-500" />
                        </button>
                        <button
                            type="button"
                            class="relative -mb-px border-b-2 px-4 py-2 text-sm font-semibold transition"
                            :class="tab === 'dv' ? 'border-brand text-brand' : 'border-transparent text-muted-foreground hover:text-foreground'"
                            @click="tab = 'dv'"
                        >
                            ދިވެހި (Dhivehi)
                            <span v-if="dvHasError" class="absolute -end-0.5 top-1 size-2 rounded-full bg-red-500" />
                        </button>
                    </div>

                    <!-- English -->
                    <div v-show="tab === 'en'" class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium">Title</label>
                            <input v-model="form.title.en" type="text" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30" />
                            <p v-if="form.errors['title.en']" class="mt-1 text-xs text-red-600">{{ form.errors['title.en'] }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium">Excerpt</label>
                            <textarea v-model="form.excerpt.en" rows="2" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30"></textarea>
                            <p v-if="form.errors['excerpt.en']" class="mt-1 text-xs text-red-600">{{ form.errors['excerpt.en'] }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium">Body</label>
                            <RichTextEditor v-model="form.body.en" dir="ltr" placeholder="Write the article…" />
                            <p v-if="form.errors['body.en']" class="mt-1 text-xs text-red-600">{{ form.errors['body.en'] }}</p>
                        </div>
                    </div>

                    <!-- Dhivehi -->
                    <div v-show="tab === 'dv'" class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium">Title</label>
                            <input v-model="form.title.dv" type="text" dir="rtl" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 font-thaana" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium">Excerpt</label>
                            <textarea v-model="form.excerpt.dv" rows="2" dir="rtl" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 font-thaana"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium">Body</label>
                            <RichTextEditor v-model="form.body.dv" dir="rtl" placeholder="ފެން ލިޔުއެއް" />
                        </div>
                    </div>
                </section>
            </div>

            <!-- Meta sidebar -->
            <aside class="space-y-6">
                <section class="space-y-5 rounded-2xl border border-border bg-background p-6 shadow-sm lg:sticky lg:top-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Author</label>
                        <div class="rounded-lg border border-border bg-muted/30 px-3 py-2 text-sm">
                            <span>{{ authorName.en || '—' }}</span>
                            <span v-if="authorName.dv" class="ms-2 font-thaana text-muted-foreground" dir="rtl">{{ authorName.dv }}</span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">Set from your user profile.</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Category</label>
                        <select v-model="form.category" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">
                            <option value="schoolNews">School News</option>
                            <option value="achievements">Achievements</option>
                            <option value="events">Events</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Publish date</label>
                        <input v-model="form.published_at" type="date" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30" />
                        <p v-if="form.errors.published_at" class="mt-1 text-xs text-red-600">{{ form.errors.published_at }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Image URL <span class="font-normal text-muted-foreground">(or upload)</span></label>
                        <input v-model="form.image" type="text" placeholder="https://…" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30" />
                        <button type="button" :disabled="imageUploading" class="mt-2 inline-flex items-center gap-2 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-foreground/80 transition hover:bg-muted disabled:opacity-60" @click="imageInput?.click()">
                            <Upload class="size-3.5" :class="imageUploading ? 'animate-pulse' : ''" /> {{ imageUploading ? 'Uploading…' : 'Upload image' }}
                        </button>
                        <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="uploadFeatured" />
                        <p v-if="form.errors.image" class="mt-1 text-xs text-red-600">{{ form.errors.image }}</p>
                        <img v-if="form.image" :src="form.image" alt="" class="mt-2 aspect-[16/10] w-full rounded-lg border border-border object-cover" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Slug <span class="font-normal text-muted-foreground">(leave blank to auto-generate)</span></label>
                        <input v-model="form.slug" type="text" placeholder="auto-from-title" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30" />
                        <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
                    </div>
                    <div class="space-y-2 border-t border-border pt-4">
                        <button type="button" :disabled="form.processing" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-brand-foreground shadow-sm transition hover:brightness-110 disabled:opacity-60" @click="submit(true)">
                            <Save class="size-4" /> {{ isEdit ? 'Update &amp; publish' : 'Publish' }}
                        </button>
                        <button type="button" :disabled="form.processing" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border px-5 py-2.5 text-sm font-semibold text-foreground/80 transition hover:bg-muted disabled:opacity-60" @click="submit(false)">
                            <FileText class="size-4" /> Save as draft
                        </button>
                        <Link href="/admin/news" class="block pt-1 text-center text-sm font-medium text-muted-foreground hover:text-foreground">Cancel</Link>
                    </div>
                </section>
            </aside>
        </div>
    </form>
</template>
