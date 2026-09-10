<script setup lang="ts">
import {
    ChevronDown,
    ChevronRight,
    ImagePlus,
    Plus,
    Trash2,
    UserRound,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { uploadImage } from '@/lib/uploadImage';

defineOptions({ name: 'StaffNodeEditor' });

const props = defineProps<{
    node: Record<string, any>;
    depth?: number;
    removable?: boolean;
}>();

const emit = defineEmits<{ remove: [] }>();

/*
 * This editor mutates the node it is given, in place. The whole org chart is a
 * single reactive value on the parent's form, and each level of the tree edits
 * its own slice of it — same contract as SchemaField. `model` is that shared
 * object under a local name, which is also what keeps vue/no-mutating-props
 * from flagging every field binding below.
 */
const model = props.node;

if (!Array.isArray(model.children)) {
    model.children = [];
}

/** Photo and bio stay folded away — most edits are just a name or a title. */
const expanded = ref(false);
const showReports = ref(true);
const uploading = ref(false);
const photoInput = ref<HTMLInputElement | null>(null);

const reportCount = computed(() => countReports(model));

/** Everyone beneath a person, at any depth. */
function countReports(person: Record<string, any>): number {
    const children: Record<string, any>[] = person.children ?? [];

    return children.reduce(
        (total, child) => total + 1 + countReports(child),
        0,
    );
}

function addChild() {
    model.children.push({
        id: Date.now() + Math.floor(Math.random() * 1000),
        name: { en: '', dv: '' },
        title: { en: '', dv: '' },
        photo: '',
        bio: { en: '', dv: '' },
        children: [],
    });
    showReports.value = true;
}

function removeChild(i: number) {
    if (!confirm('Remove this person and everyone reporting to them?')) {
        return;
    }

    model.children.splice(i, 1);
}

async function onPhotoSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file) {
        return;
    }

    uploading.value = true;

    try {
        model.photo = await uploadImage(file);
    } catch {
        window.alert(
            'Photo upload failed. Please try a smaller image (max 5 MB).',
        );
    } finally {
        uploading.value = false;
    }
}

const input =
    'min-w-0 rounded-md border border-border bg-background px-2 py-1.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20';
</script>

<template>
    <div class="rounded-xl border border-border bg-background">
        <!-- Compact row: everything needed to identify and rename a person -->
        <div class="flex items-center gap-2 p-2">
            <button
                type="button"
                class="shrink-0 rounded p-0.5 text-muted-foreground transition hover:text-foreground disabled:opacity-30"
                :disabled="!model.children.length"
                :title="showReports ? 'Hide reports' : 'Show reports'"
                @click="showReports = !showReports"
            >
                <component
                    :is="showReports ? ChevronDown : ChevronRight"
                    class="size-4"
                />
            </button>

            <span
                class="grid size-8 shrink-0 place-items-center overflow-hidden rounded-md bg-muted text-muted-foreground"
            >
                <img
                    v-if="model.photo"
                    :src="model.photo"
                    alt=""
                    class="size-full object-cover"
                />
                <UserRound v-else class="size-4" />
            </span>

            <input
                v-model="model.name.en"
                type="text"
                placeholder="Name"
                aria-label="Name (English)"
                :class="[input, 'w-40 flex-1 font-medium']"
            />
            <input
                v-model="model.name.dv"
                type="text"
                dir="rtl"
                placeholder="ނަން"
                aria-label="Name (Dhivehi)"
                :class="[input, 'w-32 flex-1']"
            />
            <input
                v-model="model.title.en"
                type="text"
                placeholder="Title / role"
                aria-label="Title (English)"
                :class="[input, 'w-40 flex-1']"
            />
            <input
                v-model="model.title.dv"
                type="text"
                dir="rtl"
                placeholder="މަޤާމު"
                aria-label="Title (Dhivehi)"
                :class="[input, 'w-32 flex-1']"
            />

            <span
                v-if="reportCount"
                class="shrink-0 rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                :title="`${reportCount} people report to this person`"
            >
                {{ reportCount }}
            </span>

            <button
                type="button"
                class="shrink-0 rounded-md px-2 py-1 text-xs font-medium transition"
                :class="
                    expanded
                        ? 'bg-brand-muted text-brand'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                "
                title="Photo and bio"
                @click="expanded = !expanded"
            >
                More
            </button>
            <button
                type="button"
                class="shrink-0 rounded-md p-1.5 text-muted-foreground transition hover:bg-brand-muted hover:text-brand"
                title="Add report"
                @click="addChild"
            >
                <Plus class="size-4" />
            </button>
            <button
                v-if="removable"
                type="button"
                class="shrink-0 rounded-md p-1.5 text-muted-foreground transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30"
                title="Remove person"
                @click="emit('remove')"
            >
                <Trash2 class="size-4" />
            </button>
        </div>

        <!-- Photo and bio, only when asked for -->
        <div
            v-if="expanded"
            class="grid gap-3 border-t border-border bg-muted/20 p-3 sm:grid-cols-[auto_1fr]"
        >
            <div class="flex items-start gap-2">
                <span
                    class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-lg border border-border bg-background text-muted-foreground"
                >
                    <img
                        v-if="model.photo"
                        :src="model.photo"
                        alt=""
                        class="size-full object-cover"
                    />
                    <UserRound v-else class="size-6" />
                </span>
                <div class="flex flex-col gap-1">
                    <input
                        ref="photoInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="onPhotoSelected"
                    />
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md border border-border px-2 py-1 text-xs font-medium transition hover:bg-muted disabled:opacity-60"
                        :disabled="uploading"
                        @click="photoInput?.click()"
                    >
                        <ImagePlus class="size-3.5" />
                        {{ uploading ? 'Uploading…' : 'Upload' }}
                    </button>
                    <button
                        v-if="model.photo"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs text-muted-foreground transition hover:text-red-600"
                        @click="model.photo = ''"
                    >
                        <X class="size-3.5" /> Remove
                    </button>
                </div>
            </div>

            <div class="grid gap-2 sm:grid-cols-2">
                <textarea
                    v-model="model.bio.en"
                    rows="2"
                    placeholder="Short bio (English)"
                    aria-label="Bio (English)"
                    :class="[input, 'resize-y']"
                />
                <textarea
                    v-model="model.bio.dv"
                    rows="2"
                    dir="rtl"
                    placeholder="ތަޢާރަފު"
                    aria-label="Bio (Dhivehi)"
                    :class="[input, 'resize-y']"
                />
                <input
                    v-model="model.photo"
                    type="text"
                    placeholder="/storage/uploads/photo.jpg"
                    aria-label="Photo path"
                    :class="[input, 'font-mono sm:col-span-2']"
                />
            </div>
        </div>

        <!-- Reports, indented just enough to read as a tree -->
        <div
            v-if="model.children.length && showReports"
            class="space-y-1.5 border-t border-border p-2 ps-4"
        >
            <StaffNodeEditor
                v-for="(child, i) in model.children"
                :key="child.id ?? i"
                :node="child"
                :depth="(depth ?? 0) + 1"
                removable
                @remove="removeChild(i)"
            />
        </div>
    </div>
</template>
