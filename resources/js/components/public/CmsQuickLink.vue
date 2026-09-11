<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useLocale } from '@/i18n/useLocale';
import { LayoutDashboard } from '@/lib/publicIcons';

/**
 * A shortcut back to the CMS, shown on the public site only to staff who are
 * already signed in. Visitors never see it, and it reveals nothing when signed
 * out — the admin area answers 404 to guests.
 */
const page = usePage();
const { t, isRtl } = useLocale();

const user = computed(
    () => page.props.auth?.user as { name?: string } | null | undefined,
);
</script>

<template>
    <!--
        print:hidden so it never appears on a printed page. Positioned with the
        logical `end` so it moves to the left in Dhivehi.
    -->
    <Link
        v-if="user"
        href="/admin"
        class="fixed end-5 bottom-5 z-40 inline-flex items-center gap-2 rounded-full bg-brand px-4 py-3 text-sm font-semibold text-brand-foreground shadow-lg ring-1 ring-black/5 transition hover:brightness-110 focus-visible:ring-2 focus-visible:ring-brand/40 focus-visible:outline-none print:hidden"
        :title="t({ en: 'Open the CMS', dv: 'ސީއެމްއެސް ހުޅުއްވާ' })"
    >
        <LayoutDashboard class="size-5 shrink-0" />
        <!-- Label is hidden on small screens so the button stays out of the way. -->
        <span class="hidden sm:inline" :class="isRtl ? 'font-thaana' : ''">
            {{ t({ en: 'Edit site', dv: 'ސައިޓް އެޑިޓް' }) }}
        </span>
    </Link>
</template>
