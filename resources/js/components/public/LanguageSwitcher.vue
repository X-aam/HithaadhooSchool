<script setup lang="ts">
import { useLocale } from '@/i18n/useLocale';
import { Languages } from '@/lib/publicIcons';

const { locale, toggleLocale, t, messages } = useLocale();
</script>

<template>
    <button
        type="button"
        class="group inline-flex items-center gap-2 rounded-full border border-brand/30 bg-white/70 px-3 py-1.5 text-sm font-semibold text-brand shadow-sm backdrop-blur transition hover:bg-brand hover:text-brand-foreground focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none dark:bg-white/10"
        :aria-label="t(messages.common.switchLanguage)"
        @click="toggleLocale"
    >
        <Languages class="size-4" />
        <span class="tabular-nums">{{ locale === 'en' ? 'ދިވެހި' : 'EN' }}</span>
    </button>
</template>
