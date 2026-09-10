<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { LocalizedText } from '@/i18n/messages';
import { useLocale } from '@/i18n/useLocale';
import {
    AtSign,
    Globe,
    Mail,
    MapPin,
    MessagesSquare,
    Phone,
    Send,
    Share2,
} from '@/lib/publicIcons';
import { school } from '@/lib/sampleData';

const { t, pick, messages } = useLocale();
const page = usePage();

interface CustomNavItem {
    id: number;
    label: LocalizedText;
    href: string;
    children?: { id: number; label: LocalizedText; href: string }[];
}

const defaultQuickLinks: { label: LocalizedText; href: string }[] = [
    { label: messages.nav.academicCalendar, href: '/academic-calendar' },
    { label: messages.nav.news, href: '/news' },
    { label: messages.nav.announcements, href: '/announcements' },
    { label: messages.nav.downloads, href: '/downloads' },
    { label: messages.nav.orgChart, href: '/team' },
    { label: messages.nav.contact, href: '/contact' },
];

// Footer quick links follow the CMS-managed site menu (flattened), falling
// back to the built-in list when no custom navigation has been published.
const quickLinks = computed<{ label: LocalizedText; href: string }[]>(() => {
    const custom = page.props.siteNavigation as CustomNavItem[] | null;

    if (!custom || !custom.length) {
return defaultQuickLinks;
}

    const links: { label: LocalizedText; href: string }[] = [];

    for (const item of custom) {
        if (item.href) {
links.push({ label: item.label, href: item.href });
}

        for (const child of item.children ?? []) {
            if (child.href) {
links.push({ label: child.label, href: child.href });
}
        }
    }

    return links.length ? links : defaultQuickLinks;
});

const social = [
    { icon: Globe, href: school.contact.social.facebook, label: 'Facebook' },
    { icon: AtSign, href: school.contact.social.instagram, label: 'Instagram' },
    { icon: Share2, href: school.contact.social.youtube, label: 'YouTube' },
    { icon: MessagesSquare, href: school.contact.social.x, label: 'X' },
];

const email = ref('');
const subscribed = ref(false);
function subscribe() {
    if (email.value.trim()) {
        subscribed.value = true;
        email.value = '';
    }
}
</script>

<template>
    <footer class="mt-20 border-t border-border bg-muted/40">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <!-- Brand -->
            <div class="space-y-4">
                <Link href="/" class="flex items-center gap-2.5">
                    <img src="/images/logo.png" :alt="t(messages.site.name)" class="size-10 shrink-0 object-contain" />
                    <span class="font-bold text-foreground">{{ t(messages.site.name) }}</span>
                </Link>
                <p class="max-w-xs text-sm text-muted-foreground">{{ t(messages.site.tagline) }}</p>
                <div class="flex gap-2">
                    <a
                        v-for="s in social"
                        :key="s.label"
                        :href="s.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="s.label"
                        class="grid size-9 place-items-center rounded-full border border-border text-foreground/70 transition hover:border-brand hover:bg-brand hover:text-brand-foreground"
                    >
                        <component :is="s.icon" class="size-4" />
                    </a>
                </div>
            </div>

            <!-- Quick links -->
            <div>
                <h3 class="mb-4 text-sm font-semibold text-foreground">{{ t(messages.footer.quickLinks) }}</h3>
                <ul class="space-y-2.5">
                    <li v-for="l in quickLinks" :key="l.href">
                        <Link :href="l.href" class="text-sm text-muted-foreground transition hover:text-brand hover:underline">
                            {{ t(l.label) }}
                        </Link>
                    </li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="mb-4 text-sm font-semibold text-foreground">{{ t(messages.footer.contactUs) }}</h3>
                <ul class="space-y-3 text-sm text-muted-foreground">
                    <li class="flex items-start gap-2.5">
                        <MapPin class="mt-0.5 size-4 shrink-0 text-brand" />
                        <span dir="auto">{{ pick(school.contact.address) }}</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <Phone class="size-4 shrink-0 text-brand" />
                        <a :href="`tel:${school.contact.phone}`" class="hover:text-brand" dir="ltr">{{ school.contact.phone }}</a>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <Mail class="size-4 shrink-0 text-brand" />
                        <a :href="`mailto:${school.contact.email}`" class="hover:text-brand" dir="ltr">{{ school.contact.email }}</a>
                    </li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h3 class="mb-4 text-sm font-semibold text-foreground">{{ t(messages.footer.newsletter) }}</h3>
                <p class="mb-3 text-sm text-muted-foreground">{{ t(messages.footer.newsletterText) }}</p>
                <form v-if="!subscribed" class="flex gap-2" @submit.prevent="subscribe">
                    <input
                        v-model="email"
                        type="email"
                        required
                        :placeholder="t(messages.contact.email)"
                        class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30"
                        dir="auto"
                    />
                    <button type="submit" class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand text-brand-foreground transition hover:opacity-90" :aria-label="t(messages.footer.subscribe)">
                        <Send class="size-4 rtl:-scale-x-100" />
                    </button>
                </form>
                <p v-else class="rounded-lg bg-brand-muted px-3 py-2.5 text-sm font-medium text-brand">
                    {{ t(messages.contact.sent) }}
                </p>
            </div>
        </div>

        <div class="border-t border-border">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-muted-foreground sm:flex-row sm:px-6 lg:px-8">
                <p>© {{ new Date().getFullYear() }} {{ t(messages.site.name) }}. {{ t(messages.footer.rights) }}</p>
                <p dir="auto">{{ pick(school.contact.officeHours) }}</p>
            </div>
        </div>
    </footer>
</template>
