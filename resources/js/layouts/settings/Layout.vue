<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editMail } from '@/routes/mail';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { index as teams } from '@/routes/teams';
import type { NavItem } from '@/types';

const page = usePage();

const isAdmin = computed(
    () =>
        (page.props.auth?.user as { role?: string } | undefined)?.role ===
        'admin',
);

/*
 * These sections used to sit in a left sidebar. They render inside the CMS
 * chrome now, which already has one, so they read as tabs instead — a second
 * vertical nav beside the first is hard to scan.
 */
const sections = computed<NavItem[]>(() => [
    { title: 'Profile', href: editProfile() },
    { title: 'Security', href: editSecurity() },
    { title: 'Teams', href: teams() },
    { title: 'Appearance', href: editAppearance() },
    // Mail is server-wide configuration, not a personal preference.
    ...(isAdmin.value ? [{ title: 'Email', href: editMail() }] : []),
]);

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight">Settings</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Manage your profile and account settings.
            </p>
        </div>

        <nav
            class="mb-8 flex flex-wrap gap-1 border-b border-border"
            aria-label="Settings"
        >
            <Link
                v-for="section in sections"
                :key="toUrl(section.href)"
                :href="section.href"
                class="-mb-px border-b-2 px-4 py-2.5 text-sm font-medium transition"
                :class="
                    isCurrentOrParentUrl(section.href)
                        ? 'border-brand text-brand'
                        : 'border-transparent text-foreground/70 hover:border-border hover:text-foreground'
                "
            >
                {{ section.title }}
            </Link>
        </nav>

        <section class="max-w-2xl space-y-12">
            <slot />
        </section>
    </div>
</template>
