<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Reveal from '@/components/public/Reveal.vue';
import { useLocale } from '@/i18n/useLocale';
import { Download, File, FileSpreadsheet, FileText } from '@/lib/publicIcons';
import { downloads   } from '@/lib/sampleData';
import type {DownloadCategory, DownloadItem} from '@/lib/sampleData';

const props = defineProps<{ items?: DownloadItem[] }>();

const { t, pick, date, messages } = useLocale();

const source = computed<DownloadItem[]>(() => props.items ?? downloads);

const categories: { key: DownloadCategory | 'all'; label: { en: string; dv: string } }[] = [
    { key: 'all', label: messages.common.all },
    { key: 'forms', label: { en: 'Forms', dv: 'ފޯމުތައް' } },
    { key: 'policies', label: { en: 'Policies', dv: 'ސިޔާސަތުތައް' } },
    { key: 'syllabi', label: { en: 'Syllabi', dv: 'މަންހަޖު' } },
    { key: 'newsletters', label: { en: 'Newsletters', dv: 'ނިއުސްލެޓަރ' } },
];

const active = ref<DownloadCategory | 'all'>('all');
const query = ref('');

const filtered = computed(() =>
    source.value.filter((d) => {
        const catOk = active.value === 'all' || d.category === active.value;
        const q = query.value.trim().toLowerCase();
        const searchOk = !q || `${d.title.en ?? ''} ${d.title.dv ?? ''}`.toLowerCase().includes(q);

        return catOk && searchOk;
    }),
);

function iconFor(type: string) {
    if (type === 'pdf') {
return FileText;
}

    if (type === 'xlsx') {
return FileSpreadsheet;
}

    return File;
}
</script>

<template>
    <Head :title="t(messages.nav.downloads)" />


    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="c in categories"
                    :key="c.key"
                    type="button"
                    class="rounded-full border px-4 py-1.5 text-sm font-medium transition"
                    :class="active === c.key ? 'border-brand bg-brand text-brand-foreground' : 'border-border bg-background text-foreground/70 hover:border-brand/40 hover:text-brand'"
                    @click="active = c.key"
                >
                    {{ t(c.label) }}
                </button>
            </div>
            <input
                v-model="query"
                type="search"
                :placeholder="t(messages.common.search)"
                dir="auto"
                class="w-full rounded-full border border-border bg-background px-4 py-2 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20 sm:w-64"
            />
        </div>

        <p v-if="!filtered.length" class="py-16 text-center text-muted-foreground">{{ t(messages.common.noResults) }}</p>

        <div class="grid gap-3">
            <Reveal v-for="(d, i) in filtered" :key="d.id" :delay="(i % 6) * 50">
                <div class="flex items-center gap-4 rounded-2xl border border-border bg-background p-4 shadow-sm transition hover:border-brand/40 hover:shadow-md">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-muted text-brand">
                        <component :is="iconFor(d.fileType)" class="size-6" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate font-semibold text-foreground" dir="auto">{{ pick(d.title) }}</h3>
                        <p class="text-xs text-muted-foreground">{{ d.fileType.toUpperCase() }} · {{ d.size }} · {{ date(d.date) }}</p>
                    </div>
                    <a
                        :href="d.url || `#download-${d.id}`"
                        :target="d.url ? '_blank' : undefined"
                        :rel="d.url ? 'noopener' : undefined"
                        class="inline-flex shrink-0 items-center gap-2 rounded-full bg-brand px-4 py-2 text-sm font-semibold text-brand-foreground transition hover:brightness-110"
                        :aria-label="`${t(messages.common.download)} ${pick(d.title)}`"
                    >
                        <Download class="size-4" />
                        <span class="hidden sm:inline">{{ t(messages.common.download) }}</span>
                    </a>
                </div>
            </Reveal>
        </div>
    </div>
</template>
