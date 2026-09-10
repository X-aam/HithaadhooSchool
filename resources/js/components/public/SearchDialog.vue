<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useLocale } from '@/i18n/useLocale';
import { Newspaper, Megaphone, Download, Search, X } from '@/lib/publicIcons';
import { announcements, downloads, news } from '@/lib/sampleData';

const props = defineProps<{ open: boolean }>();
const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t, pick, messages } = useLocale();
const query = ref('');
const inputRef = ref<HTMLInputElement | null>(null);

interface Result {
    type: 'news' | 'announcement' | 'download';
    href: string;
    title: string;
    icon: unknown;
}

const results = computed<Result[]>(() => {
    const q = query.value.trim().toLowerCase();

    if (q.length < 2) {
        return [];
    }

    const match = (v: string) => v.toLowerCase().includes(q);

    const out: Result[] = [];
    news.forEach((n) => {
        if (match(pick(n.title)) || match(pick(n.excerpt))) {
            out.push({ type: 'news', href: `/news/${n.slug}`, title: pick(n.title), icon: Newspaper });
        }
    });
    announcements.forEach((a) => {
        if (match(pick(a.title)) || match(pick(a.body))) {
            out.push({ type: 'announcement', href: '/announcements', title: pick(a.title), icon: Megaphone });
        }
    });
    downloads.forEach((d) => {
        if (match(pick(d.title))) {
            out.push({ type: 'download', href: '/downloads', title: pick(d.title), icon: Download });
        }
    });

    return out.slice(0, 8);
});

function close() {
    emit('update:open', false);
    query.value = '';
}

watch(
    () => props.open,
    (v) => {
        if (v) {
            nextTick(() => inputRef.value?.focus());
        }
    },
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 p-4 pt-[12vh] backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            @click.self="close"
            @keydown.esc="close"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-border bg-popover shadow-2xl">
                <div class="flex items-center gap-3 border-b border-border px-4">
                    <Search class="size-5 shrink-0 text-muted-foreground" />
                    <input
                        ref="inputRef"
                        v-model="query"
                        type="search"
                        class="h-14 w-full bg-transparent text-base text-foreground outline-none placeholder:text-muted-foreground"
                        :placeholder="t(messages.common.searchPlaceholder)"
                        dir="auto"
                    />
                    <button type="button" class="grid size-8 place-items-center rounded-full text-muted-foreground hover:bg-muted" :aria-label="t(messages.common.close)" @click="close">
                        <X class="size-4" />
                    </button>
                </div>

                <div class="max-h-[50vh] overflow-y-auto p-2">
                    <p v-if="query.trim().length >= 2 && results.length === 0" class="px-3 py-6 text-center text-sm text-muted-foreground">
                        {{ t(messages.common.noResults) }}
                    </p>
                    <Link
                        v-for="(r, i) in results"
                        :key="i"
                        :href="r.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition hover:bg-brand-muted"
                        @click="close"
                    >
                        <component :is="r.icon" class="size-4 shrink-0 text-brand" />
                        <span class="truncate text-foreground" dir="auto">{{ r.title }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </Transition>
</template>
