<script setup lang="ts">
import {
    ChevronDown,
    ChevronUp,
    CornerDownRight,
    Plus,
    Trash2,
} from '@lucide/vue';
import { navLinkSuggestions } from '@/lib/siteNavigation';

const props = defineProps<{
    items: Record<string, any>[];
}>();

/*
 * This editor mutates the array it is handed, in place — the menu is one
 * reactive value on the parent's form. `model` is that same array under a local
 * name, which is also what keeps vue/no-mutating-props from flagging the edits.
 */
const model = props.items;

function allItems(): Record<string, any>[] {
    const flat: Record<string, any>[] = [];

    for (const item of model) {
        flat.push(item);

        for (const child of item.children ?? []) {
            flat.push(child);
        }
    }

    return flat;
}

function nextId(): number {
    return (
        allItems().reduce((max, e) => Math.max(max, Number(e.id) || 0), 0) + 1
    );
}

function addItem() {
    model.push({
        id: nextId(),
        label: { en: '', dv: '' },
        href: '',
        children: [],
    });
}

function removeItem(index: number) {
    const item = model[index];
    const children = item?.children?.length ?? 0;

    if (
        children &&
        !confirm(
            `Remove "${item.label?.en || 'this item'}" and its ${children} sub-items?`,
        )
    ) {
        return;
    }

    model.splice(index, 1);
}

function move(list: Record<string, any>[], index: number, delta: number) {
    const target = index + delta;

    if (target < 0 || target >= list.length) {
        return;
    }

    const [moved] = list.splice(index, 1);
    list.splice(target, 0, moved);
}

function addChild(item: Record<string, any>) {
    if (!item.children) {
        item.children = [];
    }

    item.children.push({ id: nextId(), label: { en: '', dv: '' }, href: '' });
}

function removeChild(item: Record<string, any>, index: number) {
    item.children.splice(index, 1);
}

const field =
    'min-w-0 rounded-md border border-border bg-background px-2 py-1.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20';
const iconBtn =
    'rounded-md p-1.5 text-muted-foreground transition hover:bg-muted disabled:opacity-30';
</script>

<template>
    <div class="space-y-3">
        <div
            class="flex flex-wrap items-center gap-3 rounded-xl border border-border bg-muted/30 px-4 py-3"
        >
            <p class="text-sm text-muted-foreground">
                The header menu, in order. Leave a link blank when the item only
                opens a dropdown.
            </p>
            <button
                type="button"
                class="ms-auto inline-flex items-center gap-1.5 rounded-full bg-brand px-4 py-2 text-sm font-semibold text-brand-foreground transition hover:brightness-110"
                @click="addItem"
            >
                <Plus class="size-4" /> Add menu item
            </button>
        </div>

        <datalist id="nav-link-suggestions">
            <option v-for="s in navLinkSuggestions" :key="s" :value="s" />
        </datalist>

        <div class="space-y-1.5">
            <div
                v-for="(item, i) in model"
                :key="item.id"
                class="rounded-xl border border-border bg-background p-2"
            >
                <!-- Top-level item on one row -->
                <div class="flex items-center gap-2">
                    <span
                        class="w-5 shrink-0 text-center text-xs font-semibold text-muted-foreground"
                    >
                        {{ i + 1 }}
                    </span>
                    <input
                        v-model="item.label.en"
                        type="text"
                        placeholder="Label"
                        aria-label="Label (English)"
                        :class="[field, 'w-36 flex-1 font-medium']"
                    />
                    <input
                        v-model="item.label.dv"
                        dir="rtl"
                        type="text"
                        placeholder="ލޭބަލް"
                        aria-label="Label (Dhivehi)"
                        :class="[field, 'w-32 flex-1 font-thaana']"
                    />
                    <input
                        v-model="item.href"
                        type="text"
                        list="nav-link-suggestions"
                        placeholder="/news — blank for dropdown only"
                        aria-label="Link"
                        :class="[field, 'w-56 flex-1 font-mono text-xs']"
                    />
                    <button
                        type="button"
                        class="shrink-0 rounded-md px-2 py-1.5 text-xs font-medium text-muted-foreground transition hover:bg-brand-muted hover:text-brand"
                        title="Add sub-item"
                        @click="addChild(item)"
                    >
                        + Sub
                    </button>
                    <button
                        type="button"
                        :class="[iconBtn, 'shrink-0']"
                        :disabled="i === 0"
                        title="Move up"
                        @click="move(model, i, -1)"
                    >
                        <ChevronUp class="size-4" />
                    </button>
                    <button
                        type="button"
                        :class="[iconBtn, 'shrink-0']"
                        :disabled="i === model.length - 1"
                        title="Move down"
                        @click="move(model, i, 1)"
                    >
                        <ChevronDown class="size-4" />
                    </button>
                    <button
                        type="button"
                        class="shrink-0 rounded-md p-1.5 text-muted-foreground transition hover:text-red-600"
                        title="Remove"
                        @click="removeItem(i)"
                    >
                        <Trash2 class="size-4" />
                    </button>
                </div>

                <!-- Sub-items, one row each -->
                <div
                    v-if="item.children && item.children.length"
                    class="mt-1.5 space-y-1 border-s-2 border-brand/20 ps-3"
                >
                    <div
                        v-for="(child, j) in item.children"
                        :key="child.id"
                        class="flex items-center gap-2"
                    >
                        <CornerDownRight
                            class="size-3.5 shrink-0 text-muted-foreground"
                        />
                        <input
                            v-model="child.label.en"
                            type="text"
                            placeholder="Label"
                            aria-label="Sub-item label (English)"
                            :class="[field, 'w-32 flex-1']"
                        />
                        <input
                            v-model="child.label.dv"
                            dir="rtl"
                            type="text"
                            placeholder="ލޭބަލް"
                            aria-label="Sub-item label (Dhivehi)"
                            :class="[field, 'w-28 flex-1 font-thaana']"
                        />
                        <input
                            v-model="child.href"
                            type="text"
                            list="nav-link-suggestions"
                            placeholder="/timetable"
                            aria-label="Sub-item link"
                            :class="[field, 'w-52 flex-1 font-mono text-xs']"
                        />
                        <button
                            type="button"
                            :class="[iconBtn, 'shrink-0']"
                            :disabled="j === 0"
                            title="Move up"
                            @click="move(item.children, j, -1)"
                        >
                            <ChevronUp class="size-3.5" />
                        </button>
                        <button
                            type="button"
                            :class="[iconBtn, 'shrink-0']"
                            :disabled="j === item.children.length - 1"
                            title="Move down"
                            @click="move(item.children, j, 1)"
                        >
                            <ChevronDown class="size-3.5" />
                        </button>
                        <button
                            type="button"
                            class="shrink-0 rounded-md p-1.5 text-muted-foreground transition hover:text-red-600"
                            title="Remove"
                            @click="removeChild(item, j)"
                        >
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                </div>
            </div>

            <p
                v-if="!model.length"
                class="rounded-xl border border-dashed border-border py-12 text-center text-sm text-muted-foreground"
            >
                No menu items yet. Use “Add menu item” to create one.
            </p>
        </div>
    </div>
</template>
