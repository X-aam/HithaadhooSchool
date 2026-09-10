<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useLocale } from '@/i18n/useLocale';
import { orgChart as defaultOrgChart } from '@/lib/sampleData';
import type { StaffNode } from '@/lib/sampleData';

const props = defineProps<{ orgChart?: StaffNode }>();

const { t, pick, messages } = useLocale();

const chart = computed<StaffNode>(() => props.orgChart ?? defaultOrgChart);

/** Flatten the staff tree in level order so leaders appear first. */
const members = computed<StaffNode[]>(() => {
    const out: StaffNode[] = [];
    let level: StaffNode[] = [chart.value];

    while (level.length) {
        out.push(...level);
        level = level.flatMap((n) => n.children ?? []);
    }

    return out;
});
</script>

<template>
    <Head :title="t(messages.nav.orgChart)" />

    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-center text-2xl font-semibold text-foreground">{{ t({ en: 'Meet the team', dv: 'ޓީމާ ބައްދަލުކުރައްވާ' }) }}</h2>

        <div class="mt-12 flex flex-wrap justify-center gap-x-10 gap-y-12">
            <div v-for="member in members" :key="member.id" class="group w-40 text-center sm:w-44">
                <img
                    :src="member.photo"
                    :alt="pick(member.name)"
                    class="mx-auto size-32 rounded-full bg-muted object-cover ring-1 ring-border transition group-hover:ring-brand/40 sm:size-36"
                    loading="lazy"
                />
                <h3 class="mt-5 text-sm font-semibold text-foreground" dir="auto">{{ pick(member.name) }}</h3>
                <p class="mt-1 text-xs text-muted-foreground" dir="auto">{{ pick(member.title) }}</p>
            </div>
        </div>
    </div>
</template>
