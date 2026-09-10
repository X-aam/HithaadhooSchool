<script setup lang="ts">
import { ChevronDown, ChevronUp, Plus, Trash2 } from '@lucide/vue';
import type { FieldDef } from './schema';
import SchemaField from './SchemaField.vue';

const props = defineProps<{
    items: Record<string, any>[];
    fields: FieldDef[];
    newItem: () => Record<string, any>;
    titleKey?: string;
    addLabel?: string;
}>();

function itemTitle(item: Record<string, any>, index: number): string {
    if (props.titleKey) {
        const v = item[props.titleKey];
        const text = v && typeof v === 'object' ? v.en : v;

        if (text) {
return String(text);
}
    }

    return `Item ${index + 1}`;
}

function add() {
    props.items.push(props.newItem());
}

function remove(index: number) {
    props.items.splice(index, 1);
}

function move(index: number, delta: number) {
    const target = index + delta;

    if (target < 0 || target >= props.items.length) {
return;
}

    const [moved] = props.items.splice(index, 1);
    props.items.splice(target, 0, moved);
}
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="(item, i) in items"
            :key="i"
            class="rounded-2xl border border-border bg-background p-5 shadow-sm"
        >
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="truncate text-sm font-semibold">{{ itemTitle(item, i) }}</h3>
                <div class="flex items-center gap-1">
                    <button type="button" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted disabled:opacity-30" :disabled="i === 0" title="Move up" @click="move(i, -1)">
                        <ChevronUp class="size-4" />
                    </button>
                    <button type="button" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted disabled:opacity-30" :disabled="i === items.length - 1" title="Move down" @click="move(i, 1)">
                        <ChevronDown class="size-4" />
                    </button>
                    <button type="button" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Remove" @click="remove(i)">
                        <Trash2 class="size-4" />
                    </button>
                </div>
            </div>
            <div class="grid gap-4">
                <SchemaField v-for="f in fields" :key="f.key" :obj="item" :field="f" />
            </div>
        </div>

        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-dashed border-brand/50 px-5 py-2.5 text-sm font-semibold text-brand transition hover:bg-brand-muted"
            @click="add"
        >
            <Plus class="size-4" /> {{ addLabel ?? 'Add item' }}
        </button>
    </div>
</template>
