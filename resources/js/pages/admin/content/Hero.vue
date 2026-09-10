<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronUp,
    ImageIcon,
    ImagePlus,
    Plus,
    Trash2,
} from '@lucide/vue';
import { ref } from 'vue';
import ContentEditorShell from '@/components/admin/ContentEditorShell.vue';
import { clone } from '@/components/admin/schema';
import { heroSlides } from '@/lib/sampleData';
import { uploadImage } from '@/lib/uploadImage';

const props = defineProps<{ section: string; label: string; value: unknown }>();

interface Bilingual {
    en?: string;
    dv?: string;
}

interface HeroSlide {
    image: string;
    fallback: string;
    title: Bilingual;
    subtitle: Bilingual;
}

const form = useForm<{ value: HeroSlide[] }>({
    value:
        (props.value as HeroSlide[] | null) ??
        clone(heroSlides as unknown as HeroSlide[]),
});

const uploadingIndex = ref<number | null>(null);
const uploadError = ref('');
const expanded = ref<number[]>([]);
const pickers = ref<Record<number, HTMLInputElement | null>>({});

function addSlide() {
    form.value.push({
        image: '',
        fallback: '',
        title: { en: '', dv: '' },
        subtitle: { en: '', dv: '' },
    });
}

function removeSlide(i: number) {
    if (!confirm('Remove this slide from the homepage?')) {
        return;
    }

    form.value.splice(i, 1);
    expanded.value = expanded.value.filter((e) => e !== i);
}

/** Slides rotate in this order, so moving one is a routine edit. */
function move(i: number, delta: number) {
    const target = i + delta;

    if (target < 0 || target >= form.value.length) {
        return;
    }

    const [moved] = form.value.splice(i, 1);
    form.value.splice(target, 0, moved);
}

function toggle(i: number) {
    expanded.value = expanded.value.includes(i)
        ? expanded.value.filter((e) => e !== i)
        : [...expanded.value, i];
}

async function onImagePicked(slide: HeroSlide, index: number, event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file) {
        return;
    }

    uploadingIndex.value = index;
    uploadError.value = '';

    try {
        slide.image = await uploadImage(file);
    } catch {
        uploadError.value =
            'Upload failed. Use a JPG, PNG, WebP or GIF under 5 MB.';
    } finally {
        uploadingIndex.value = null;
    }
}

function save() {
    form.put(`/admin/content/${props.section}`, { preserveScroll: true });
}

const field =
    'min-w-0 rounded-md border border-border bg-background px-2 py-1.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20';
</script>

<template>
    <ContentEditorShell
        :section="section"
        :label="label"
        :processing="form.processing"
        @save="save"
    >
        <div
            class="flex flex-wrap items-center gap-3 rounded-xl border border-border bg-muted/30 px-4 py-3"
        >
            <p class="text-sm text-muted-foreground">
                Slides rotate on the homepage in this order. Use the arrows to
                reorder them.
            </p>
            <span class="text-xs text-muted-foreground">
                {{ form.value.length }}
                {{ form.value.length === 1 ? 'slide' : 'slides' }}
            </span>
            <button
                type="button"
                class="ms-auto inline-flex items-center gap-1.5 rounded-full bg-brand px-4 py-2 text-sm font-semibold text-brand-foreground transition hover:brightness-110"
                @click="addSlide"
            >
                <Plus class="size-4" /> Add slide
            </button>
        </div>

        <p
            v-if="uploadError"
            class="rounded-xl bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive"
        >
            {{ uploadError }}
        </p>

        <div class="space-y-2">
            <div
                v-for="(slide, i) in form.value"
                :key="i"
                class="rounded-xl border border-border bg-background p-2.5"
            >
                <div class="flex gap-3">
                    <!-- Preview: the photo is the thing being judged here -->
                    <div class="shrink-0">
                        <span
                            class="grid aspect-video w-32 place-items-center overflow-hidden rounded-lg border border-border bg-muted text-muted-foreground sm:w-40"
                        >
                            <img
                                v-if="slide.image"
                                :src="slide.image"
                                alt=""
                                class="size-full object-cover"
                            />
                            <ImageIcon v-else class="size-6" />
                        </span>
                        <input
                            :ref="
                                (el) => {
                                    pickers[i] = el as HTMLInputElement | null;
                                }
                            "
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onImagePicked(slide, i, $event)"
                        />
                        <button
                            type="button"
                            class="mt-1.5 inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-border px-2 py-1 text-xs font-medium transition hover:bg-muted disabled:opacity-60"
                            :disabled="uploadingIndex === i"
                            @click="pickers[i]?.click()"
                        >
                            <ImagePlus class="size-3.5" />
                            {{
                                uploadingIndex === i
                                    ? 'Uploading…'
                                    : slide.image
                                      ? 'Replace'
                                      : 'Upload'
                            }}
                        </button>
                    </div>

                    <!-- Text -->
                    <div class="grid min-w-0 flex-1 gap-2 sm:grid-cols-2">
                        <input
                            v-model="slide.title.en"
                            type="text"
                            placeholder="Slide title"
                            aria-label="Title (English)"
                            :class="[field, 'font-medium']"
                        />
                        <input
                            v-model="slide.title.dv"
                            type="text"
                            dir="rtl"
                            placeholder="ސުރުޚީ"
                            aria-label="Title (Dhivehi)"
                            :class="field"
                        />
                        <input
                            v-model="slide.subtitle.en"
                            type="text"
                            placeholder="Subtitle"
                            aria-label="Subtitle (English)"
                            :class="field"
                        />
                        <input
                            v-model="slide.subtitle.dv"
                            type="text"
                            dir="rtl"
                            placeholder="ދެވަނަ ސުރުޚީ"
                            aria-label="Subtitle (Dhivehi)"
                            :class="field"
                        />
                        <input
                            v-model="slide.image"
                            type="text"
                            placeholder="/storage/uploads/hero.jpg"
                            aria-label="Image path"
                            :class="[field, 'font-mono text-xs sm:col-span-2']"
                        />
                    </div>

                    <!-- Order and removal -->
                    <div class="flex shrink-0 flex-col items-center gap-0.5">
                        <span
                            class="mb-0.5 rounded bg-muted px-1.5 text-xs font-semibold text-muted-foreground"
                        >
                            {{ i + 1 }}
                        </span>
                        <button
                            type="button"
                            class="rounded-md p-1.5 text-muted-foreground transition hover:bg-muted disabled:opacity-30"
                            :disabled="i === 0"
                            title="Move up"
                            @click="move(i, -1)"
                        >
                            <ChevronUp class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="rounded-md p-1.5 text-muted-foreground transition hover:bg-muted disabled:opacity-30"
                            :disabled="i === form.value.length - 1"
                            title="Move down"
                            @click="move(i, 1)"
                        >
                            <ChevronDown class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="rounded-md p-1.5 text-muted-foreground transition hover:text-red-600"
                            title="Remove slide"
                            @click="removeSlide(i)"
                        >
                            <Trash2 class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="rounded-md px-1.5 py-1 text-xs font-medium transition"
                            :class="
                                expanded.includes(i)
                                    ? 'bg-brand-muted text-brand'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                            "
                            title="Fallback image"
                            @click="toggle(i)"
                        >
                            More
                        </button>
                    </div>
                </div>

                <div
                    v-if="expanded.includes(i)"
                    class="mt-2.5 border-t border-border pt-2.5"
                >
                    <label
                        :for="`fallback-${i}`"
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                    >
                        Fallback image — shown if the image above fails to load
                    </label>
                    <input
                        :id="`fallback-${i}`"
                        v-model="slide.fallback"
                        type="text"
                        placeholder="/images/hero-1.jpg"
                        :class="[field, 'w-full font-mono text-xs']"
                    />
                </div>
            </div>

            <p
                v-if="!form.value.length"
                class="rounded-xl border border-dashed border-border py-12 text-center text-sm text-muted-foreground"
            >
                No slides yet. Use “Add slide” to create one.
            </p>
        </div>
    </ContentEditorShell>
</template>
