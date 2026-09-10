<script setup lang="ts">
import type { LocalizedText } from '@/i18n/messages';
import { useLocale } from '@/i18n/useLocale';

defineProps<{
    title: LocalizedText;
    subtitle?: LocalizedText;
    icon?: unknown;
}>();

const { t } = useLocale();
</script>

<template>
    <section class="relative overflow-hidden border-b border-border bg-gradient-to-b from-brand-muted/70 to-background">
        <div class="bg-dots pointer-events-none absolute inset-0 opacity-40 [mask-image:linear-gradient(to_bottom,black,transparent)]" />
        <div class="animate-float-soft pointer-events-none absolute -top-24 end-0 size-72 rounded-full bg-brand/10 blur-3xl" />
        <div class="animate-float-soft-delayed pointer-events-none absolute -bottom-24 start-0 size-72 rounded-full bg-brand-accent/10 blur-3xl" />
        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <div class="flex items-center gap-4">
                <span v-if="icon" class="grid size-12 place-items-center rounded-2xl bg-brand text-brand-foreground shadow-md shadow-brand/25">
                    <component :is="icon" class="size-6" />
                </span>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl" dir="auto">
                        {{ t(title) }}
                    </h1>
                    <p v-if="subtitle" class="mt-2 max-w-2xl text-muted-foreground" dir="auto">
                        {{ t(subtitle) }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
