<script setup lang="ts">
import { ref } from 'vue';
import { useLocale } from '@/i18n/useLocale';
import { ChevronDown } from '@/lib/publicIcons';
import type { StaffNode } from '@/lib/sampleData';
import OrgNode from './OrgNode.vue';

defineProps<{ node: StaffNode; root?: boolean }>();

const { pick } = useLocale();
const open = ref(true);
</script>

<template>
    <div class="flex flex-col items-center">
        <div
            class="group relative w-56 rounded-2xl border border-border bg-background p-4 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-brand/40 hover:shadow-md"
            :class="root ? 'ring-2 ring-brand/30' : ''"
        >
            <img :src="node.photo" :alt="pick(node.name)" class="mx-auto size-16 rounded-full object-cover ring-2 ring-brand/15" loading="lazy" />
            <h3 class="mt-3 text-sm font-semibold text-foreground" dir="auto">{{ pick(node.name) }}</h3>
            <p class="text-xs text-brand" dir="auto">{{ pick(node.title) }}</p>
            <p class="mt-2 max-h-0 overflow-hidden text-xs leading-relaxed text-muted-foreground opacity-0 transition-all duration-300 group-hover:max-h-32 group-hover:opacity-100" dir="auto">
                {{ pick(node.bio) }}
            </p>
            <button
                v-if="node.children?.length"
                type="button"
                class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-muted-foreground transition hover:text-brand lg:hidden"
                @click="open = !open"
            >
                <ChevronDown class="size-3.5 transition" :class="open ? 'rotate-180' : ''" />
            </button>
        </div>

        <template v-if="node.children?.length && (open || true)">
            <div v-show="open" class="mt-6 flex flex-col items-center lg:mt-8">
                <span class="hidden h-6 w-px bg-border lg:block" />
                <div class="flex flex-col items-center gap-6 lg:flex-row lg:items-start lg:gap-8">
                    <OrgNode v-for="child in node.children" :key="child.id" :node="child" />
                </div>
            </div>
        </template>
    </div>
</template>
